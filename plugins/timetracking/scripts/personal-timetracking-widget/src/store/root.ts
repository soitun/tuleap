/*
 * Copyright (c) Enalean, 2018 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import { defineStore } from "pinia";
import { formatMinutes } from "@tuleap/plugin-timetracking-time-formatters";
import { postTime, delTime, getTrackedTimes, putTime } from "../api/rest-querier";
import {
    ERROR_OCCURRED,
    REST_FEEDBACK_ADD,
    REST_FEEDBACK_DELETE,
    REST_FEEDBACK_EDIT,
    SUCCESS_TYPE,
} from "@tuleap/plugin-timetracking-constants";
import { updateEvent } from "../TimetrackingEvents";
import type { PersonalTime } from "@tuleap/plugin-timetracking-rest-api-types";
import { FetchWrapperError } from "@tuleap/tlp-fetch";

const a_week_ago: Date = new Date();
a_week_ago.setDate(a_week_ago.getDate() - 7);

interface State {
    user_id: number;
    start_date: string;
    end_date: string;
    reading_mode: boolean;
    total_times: number;
    user_locale: string;
    pagination_offset: number;
    pagination_limit: number;
    is_loaded: boolean;
    times: PersonalTime[];
    error_message: string;
    current_times: PersonalTime[];
    is_add_mode: boolean;
    rest_feedback: {
        message: string;
        type: string;
    };
    is_loading: boolean;
}

export const usePersonalTimetrackingWidgetStore = defineStore("root", {
    state: (): State => {
        return {
            user_id: 0,
            start_date: a_week_ago.toISOString().split("T")[0],
            end_date: new Date().toISOString().split("T")[0],
            reading_mode: true,
            total_times: 0,
            user_locale: "",
            pagination_offset: 0,
            pagination_limit: 50,
            is_loaded: false,
            times: [],
            error_message: "",
            current_times: [],
            is_add_mode: false,
            rest_feedback: {
                message: "",
                type: "",
            },
            is_loading: false,
        };
    },
    getters: {
        get_formatted_total_sum(state: State): string {
            const sum = state.times.flat().reduce((sum, { minutes }) => minutes + sum, 0);
            return formatMinutes(sum);
        },
        get_formatted_aggregated_time:
            () =>
            (times: PersonalTime[]): string => {
                const minutes = times.reduce((sum, { minutes }) => minutes + sum, 0);
                return formatMinutes(minutes);
            },
        has_rest_error: (state): boolean => state.error_message !== "",
        can_results_be_displayed: (state) => state.is_loaded && state.error_message === "",
        can_load_more: (state): boolean => state.pagination_offset < state.total_times,
    },
    actions: {
        setDatesAndReload(start_date: string, end_date: string): Promise<void> {
            this.setParametersForNewQuery(start_date, end_date);
            return this.loadFirstBatchOfTimes();
        },
        async getTimes() {
            try {
                this.resetErrorMessage();
                const totalTimes = await getTrackedTimes(
                    this.user_id,
                    this.start_date,
                    this.end_date,
                    this.pagination_limit,
                    this.pagination_offset,
                );

                return this.loadAChunkOfTimes(totalTimes.times, totalTimes.total);
            } catch (error) {
                return this.showErrorMessage(error);
            }
        },
        async addTime(
            date: string,
            artifact: number,
            time_value: string,
            step: string,
        ): Promise<void> {
            try {
                const response = await postTime(date, artifact, time_value, step);
                this.pushCurrentTimes([response], REST_FEEDBACK_ADD);
                updateEvent();
                return this.loadFirstBatchOfTimes();
            } catch (rest_error) {
                return this.showRestError(rest_error);
            }
        },
        async updateTime(
            date: string,
            time_id: number,
            time_value: string,
            step: string,
        ): Promise<void> {
            try {
                const response = await putTime(date, time_id, time_value, step);
                this.replaceInCurrentTimes(response, REST_FEEDBACK_EDIT);
                updateEvent();
                return this.loadFirstBatchOfTimes();
            } catch (rest_error) {
                return this.showRestError(rest_error);
            }
        },
        async deleteTime(time_id: number): Promise<void> {
            try {
                await delTime(time_id);
                this.deleteInCurrentTimes(time_id, REST_FEEDBACK_DELETE);
                updateEvent();
                return this.loadFirstBatchOfTimes();
            } catch (rest_error) {
                return this.showRestError(rest_error);
            }
        },
        async loadFirstBatchOfTimes(): Promise<void> {
            this.setIsLoading(true);
            await this.getTimes();
            this.setIsLoading(false);
        },
        async reloadTimes(): Promise<void> {
            this.resetTimes();
            await this.getTimes();
            this.setIsLoading(false);
        },
        async showErrorMessage(rest_error: unknown): Promise<void> {
            if (!(rest_error instanceof FetchWrapperError) || rest_error.response === undefined) {
                this.setErrorMessage("");
                throw rest_error;
            }
            try {
                const { error } = await rest_error.response.json();
                this.setErrorMessage(error.code + " " + error.message);
            } catch (error) {
                this.setErrorMessage(ERROR_OCCURRED);
            }
        },
        async showRestError(rest_error: unknown): Promise<void> {
            if (!(rest_error instanceof FetchWrapperError) || rest_error.response === undefined) {
                this.setRestFeedback("", "");
                throw rest_error;
            }
            try {
                const { error } = await rest_error.response.json();
                return this.setRestFeedback(error.code + " " + error.message, "danger");
            } catch (error) {
                return this.setRestFeedback(ERROR_OCCURRED, "danger");
            }
        },
        toggleReadingMode(): void {
            this.reading_mode = !this.reading_mode;
        },
        setParametersForNewQuery(start_date: string, end_date: string): void {
            this.start_date = start_date;
            this.end_date = end_date;
            this.reading_mode = !this.reading_mode;
            this.times = [];
            this.pagination_offset = 0;
        },
        setCurrentTimes(times: PersonalTime[]): void {
            this.sortTimes(times);
            this.current_times = times;
        },
        loadAChunkOfTimes(times: PersonalTime[], total: number): void {
            this.times = this.times.concat(Object.values(times));
            this.pagination_offset += this.pagination_limit;
            this.total_times = total;
            this.is_loaded = true;
        },
        resetTimes(): void {
            this.is_loading = true;
            this.pagination_offset = 0;
            this.times = [];
            this.is_add_mode = false;
        },
        initUserId(user_id: number): void {
            this.user_id = user_id;
        },
        initUserLocale(user_locale: string): void {
            this.user_locale = user_locale.replace(/_/g, "-");
        },
        setAddMode(is_add_mode: boolean): void {
            this.is_add_mode = is_add_mode;
            if (
                !this.is_add_mode ||
                (this.is_add_mode && this.rest_feedback.type === SUCCESS_TYPE)
            ) {
                this.rest_feedback.message = "";
                this.rest_feedback.type = "";
            }
        },
        replaceInCurrentTimes(time: PersonalTime, feedback_message: string): void {
            const time_to_update_index = this.current_times.findIndex(
                (current_time) => current_time.id === time.id,
            );
            this.current_times[time_to_update_index] = time;
            this.sortTimes(this.current_times);
            this.rest_feedback.message = feedback_message;
            this.rest_feedback.type = SUCCESS_TYPE;
        },
        deleteInCurrentTimes(time_id: number, feedback_message: string): void {
            const time_to_delete_index = this.current_times.findIndex(
                (current_time) => current_time.id === time_id,
            );
            this.current_times.splice(time_to_delete_index, 1);
            this.rest_feedback.message = feedback_message;
            this.rest_feedback.type = SUCCESS_TYPE;
        },
        resetErrorMessage(): void {
            this.error_message = "";
        },
        setErrorMessage(error_message: string): void {
            this.error_message = error_message;
        },
        pushCurrentTimes(times: PersonalTime[], feedback_message: string): void {
            this.current_times = this.current_times.concat(Object.values(times));
            this.sortTimes(this.current_times);
            this.is_add_mode = false;
            this.rest_feedback.message = feedback_message;
            this.rest_feedback.type = SUCCESS_TYPE;
        },
        setIsLoading(isLoading: boolean): void {
            this.is_loading = isLoading;
        },
        setRestFeedback(message: string, type: string): void {
            this.rest_feedback.message = message;
            this.rest_feedback.type = type;
        },
        sortTimes(times: PersonalTime[]): void {
            times.sort((a, b) => {
                return new Date(String(b.date)).getTime() - new Date(String(a.date)).getTime();
            });
        },
    },
});