/*
 * Copyright (c) Enalean, 2025-present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
 *
 *  Tuleap is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Tuleap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import type { VueWrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import { getGlobalTestOptions } from "../../../helpers/global-options-for-tests";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import QuerySuggested from "../QuerySuggested.vue";
import TitleInput from "../TitleInput.vue";
import { EMITTER, QUERY_UPDATER, WIDGET_ID } from "../../../injection-symbols";
import { Fault } from "@tuleap/fault";
import type {
    EditedQueryEvent,
    Events,
    NotifyFaultEvent,
    NotifySuccessEvent,
    SwitchQueryEvent,
} from "../../../helpers/widget-events";
import {
    NOTIFY_FAULT_EVENT,
    NOTIFY_SUCCESS_EVENT,
    QUERY_EDITED_EVENT,
    SEARCH_ARTIFACTS_EVENT,
} from "../../../helpers/widget-events";
import type { Emitter } from "mitt";
import mitt from "mitt";
import QueryDisplayedByDefaultSwitch from "../QueryDisplayedByDefaultSwitch.vue";
import EditQuery from "./EditQuery.vue";
import type { Query } from "../../../type";
import type { UpdateQuery } from "../../../domain/UpdateQuery";
import { UpdateQueryStub } from "../../../../tests/stubs/UpdateQueryStub";
import DescriptionTextArea from "../DescriptionTextArea.vue";
import SelectableTable from "../../selectable-table/SelectableTable.vue";

vi.useFakeTimers();

describe("EditQuery", () => {
    let dispatched_fault_events: NotifyFaultEvent[];
    let dispatched_success_events: NotifySuccessEvent[];
    let dispatched_search_events: true[];
    let dispatched_new_query_created_events: SwitchQueryEvent[];
    let emitter: Emitter<Events>;
    let query: Query;

    const QueryEditor = {
        name: "QueryEditor",
        template: "<div>custom query editor</div>",
        methods: {
            updateEditor: (tql_query: string): string => tql_query,
        },
    };

    const registerFaultEvent = (event: NotifyFaultEvent): void => {
        dispatched_fault_events.push(event);
    };

    const registerSuccessEvent = (event: NotifySuccessEvent): void => {
        dispatched_success_events.push(event);
    };

    const registerSearchEvent = (): void => {
        dispatched_search_events.push(true);
    };

    const registerQueryEditedEvent = (event: EditedQueryEvent): void => {
        dispatched_new_query_created_events.push(event);
    };

    beforeEach(() => {
        emitter = mitt<Events>();
        dispatched_fault_events = [];
        dispatched_success_events = [];
        dispatched_search_events = [];
        dispatched_new_query_created_events = [];
        emitter.on(NOTIFY_FAULT_EVENT, registerFaultEvent);
        emitter.on(NOTIFY_SUCCESS_EVENT, registerSuccessEvent);
        emitter.on(SEARCH_ARTIFACTS_EVENT, registerSearchEvent);
        emitter.on(QUERY_EDITED_EVENT, registerQueryEditedEvent);
        query = {
            id: "00000000-03e8-70c0-9e41-6ea7a4e2b78d",
            tql_query: "SELECT @pretty_title FROM @project = 'self' WHERE @id > 15",
            title: "Some artifacts",
            description: "a query",
            is_default: false,
        };
    });

    afterEach(() => {
        emitter.off(NOTIFY_FAULT_EVENT, registerFaultEvent);
        emitter.off(NOTIFY_SUCCESS_EVENT, registerSuccessEvent);
        emitter.off(SEARCH_ARTIFACTS_EVENT, registerSearchEvent);
        emitter.off(QUERY_EDITED_EVENT, registerQueryEditedEvent);
    });

    function getWrapper(
        query_updater: UpdateQuery = UpdateQueryStub.withDefaultContent(),
    ): VueWrapper<InstanceType<typeof EditQuery>> {
        return shallowMount(EditQuery, {
            global: {
                ...getGlobalTestOptions(),
                stubs: { QueryEditor },
                provide: {
                    [WIDGET_ID.valueOf()]: 96,
                    [EMITTER.valueOf()]: emitter,
                    [QUERY_UPDATER.valueOf()]: query_updater,
                },
            },
            props: {
                query,
            },
        });
    }

    it("cancels the query edition by emitting an event and clearing the feedback", async () => {
        const wrapper = getWrapper();
        await wrapper.find("[data-test=query-edition-cancel-button]").trigger("click");
        expect(wrapper.emitted()).toHaveProperty("return-to-active-query-pane");
    });

    describe("'Save' and 'Search' buttons", () => {
        it("Displays an enabled 'Save' button when the user search for query result, the 'Search' button becomes disabled", async () => {
            const wrapper = getWrapper();
            wrapper.findComponent(TitleInput).vm.$emit("update:title", "Some title");
            wrapper
                .findComponent(QueryEditor)
                .vm.$emit("update:tql_query", "SELECT @id FROM @project = 'self' WHERE @id > 1 ");

            await vi.runOnlyPendingTimersAsync();

            const search_button = wrapper.find("[data-test=query-edition-search-button]");
            await search_button.trigger("click");
            expect(search_button.element.attributes.getNamedItem("disabled")).not.toBeNull();

            const saved_button = wrapper.find("[data-test=query-edition-save-button]");
            expect(saved_button.exists()).toBe(true);
            expect(saved_button.element.attributes.getNamedItem("disabled")).toBeNull();
        });
    });
    it("update the fields and the editor when the query has been chosen", () => {
        const tql_query = "SELECT @id FROM @project='self' WHERE @id > 1";
        const wrapper = getWrapper();
        const editor_component = wrapper.getComponent(QueryEditor);
        const editor_spy = vi.spyOn(editor_component.vm, "updateEditor");
        wrapper.findComponent(QuerySuggested).vm.$emit("query-chosen", {
            title: "Original title",
            description: "",
            tql_query,
        });
        expect(editor_spy).toHaveBeenCalledWith(tql_query);
    });
    describe("Searching artifact result", () => {
        it("does not display the result table when no search has been performed", () => {
            const wrapper = getWrapper();

            expect(wrapper.findComponent(SelectableTable).exists()).toBe(false);
        });
        it("Search a tql query result by emitting an event when the Search button is clicked", async () => {
            const wrapper = getWrapper();

            wrapper
                .findComponent(QueryEditor)
                .vm.$emit("update:tql_query", "SELECT @id FROM @project = 'self' WHERE @id > 1 ");

            await vi.runOnlyPendingTimersAsync();

            await wrapper.find("[data-test=query-edition-search-button]").trigger("click");

            expect(dispatched_search_events).toHaveLength(1);
        });
        it("Search a tql query result by emitting an event when the shortcut (ctrl+enter) is pressed", () => {
            const wrapper = getWrapper();

            wrapper
                .findComponent(QueryEditor)
                .vm.$emit("trigger-search", "SELECT @id FROM @project = 'self' WHERE @id > 1 ");

            expect(dispatched_search_events).toHaveLength(1);
        });

        it("displays the right icon according to the loading state", async () => {
            const wrapper = getWrapper();

            wrapper
                .findComponent(QueryEditor)
                .vm.$emit("update:tql_query", "SELECT @id FROM @project = 'self' WHERE @id > 1 ");
            wrapper.findComponent(TitleInput).vm.$emit("update:title", "Some title");

            await vi.runOnlyPendingTimersAsync();

            const search_button = wrapper.find("[data-test=query-edition-search-button]");
            await search_button.trigger("click");

            const query_selectable_component = wrapper.findComponent(SelectableTable);
            query_selectable_component.vm.$emit("search-started");

            await vi.runOnlyPendingTimersAsync();

            expect(wrapper.find("[data-test=query-edition-search-button-spin-icon]").exists()).toBe(
                true,
            );
            expect(
                wrapper.find("[data-test=query-edition-search-button-search-icon]").exists(),
            ).toBe(false);

            query_selectable_component.vm.$emit("search-finished");

            await vi.runOnlyPendingTimersAsync();

            expect(wrapper.find("[data-test=query-edition-search-button-spin-icon]").exists()).toBe(
                false,
            );
            expect(
                wrapper.find("[data-test=query-edition-search-button-search-icon]").exists(),
            ).toBe(true);
        });
    });
    describe("Saving a new query", () => {
        it.each([
            ["TQL query", "Some title", ""],
            ["Title", "", "SELECT @id FROM @project = 'self' WHERE @id > 1"],
        ])(
            "Displays the disabled 'Save' button when the %s field is empty",
            async (_field_name: string, title: string, tql_query: string) => {
                const wrapper = getWrapper();
                wrapper.findComponent(TitleInput).vm.$emit("update:title", title);
                wrapper.findComponent(QueryEditor).vm.$emit("update:tql_query", tql_query);

                await vi.runOnlyPendingTimersAsync();

                const saved_button = wrapper.find("[data-test=query-edition-save-button]");
                expect(saved_button.exists()).toBe(true);
                expect(saved_button.element.attributes.getNamedItem("disabled")).not.toBeNull();
            },
        );
        it("saves a new query result when the save button is clicked", async () => {
            const title = "Some title";
            const description = "";
            const is_default = true;
            const tql_query = "SELECT @id FROM @project = 'self' WHERE @id > 1 ";
            const wrapper = getWrapper(
                UpdateQueryStub.withCallback((current_query, query_to_update) => {
                    expect(query_to_update.title).toStrictEqual(title);
                    expect(query_to_update.description).toStrictEqual(description);
                    expect(query_to_update.is_default).toStrictEqual(is_default);
                    expect(query_to_update.tql_query).toStrictEqual(tql_query);
                    expect(current_query.id).toStrictEqual(query.id);
                }),
            );
            wrapper.findComponent(QueryEditor).vm.$emit("update:tql_query", tql_query);
            wrapper.findComponent(DescriptionTextArea).vm.$emit("update:description", description);
            wrapper.findComponent(TitleInput).vm.$emit("update:title", title);
            wrapper
                .findComponent(QueryDisplayedByDefaultSwitch)
                .vm.$emit("update:is_default_query", is_default);

            await vi.runOnlyPendingTimersAsync();

            await wrapper.find("[data-test=query-edition-save-button]").trigger("click");

            expect(dispatched_fault_events).toHaveLength(0);
            expect(dispatched_success_events).toHaveLength(1);
            expect(dispatched_new_query_created_events).toHaveLength(1);
            expect(dispatched_new_query_created_events[0].query.title).toBe(title);
            expect(wrapper.emitted()).toHaveProperty("return-to-active-query-pane");
        });
        it("show an error if the save failed", async () => {
            const wrapper = getWrapper(UpdateQueryStub.withFault(Fault.fromMessage("Error")));

            wrapper
                .findComponent(QueryEditor)
                .vm.$emit("update:tql_query", "SELECT @id FROM @project = 'self' WHERE @id > 1 ");
            wrapper.findComponent(TitleInput).vm.$emit("update:title", "Some title");

            await vi.runOnlyPendingTimersAsync();

            await wrapper.find("[data-test=query-edition-save-button]").trigger("click");

            expect(dispatched_fault_events).toHaveLength(1);
            expect(dispatched_success_events).toHaveLength(0);
            expect(dispatched_new_query_created_events).toHaveLength(0);
            expect(wrapper.emitted()).not.toHaveProperty("return-to-active-query-pane");
        });
    });
    describe("is_modal_should_be_displayed", () => {
        it("the modal should be displayed when a field is not empty", () => {
            const wrapper = getWrapper();

            expect(
                wrapper.findComponent(QuerySuggested).vm.$props.is_modal_should_be_displayed,
            ).toBe(true);
        });
        it("the modal should not display the modal when all field are empty", async () => {
            const wrapper = getWrapper();

            wrapper.findComponent(QueryEditor).vm.$emit("update:tql_query", "");
            wrapper.findComponent(TitleInput).vm.$emit("update:title", "");
            wrapper.findComponent(DescriptionTextArea).vm.$emit("update:description", "");

            await vi.runOnlyPendingTimersAsync();
            expect(
                wrapper.findComponent(QuerySuggested).vm.$props.is_modal_should_be_displayed,
            ).toBe(false);
        });
    });
});
