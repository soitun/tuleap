<!--
  - Copyright (c) Enalean, 2024-Present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <section class="tlp-pane-section artifact-table" ref="selectable-table">
        <empty-state v-if="is_table_empty" v-bind:tql_query="tql_query" />
        <div class="cross-tracker-loader" v-if="is_loading" data-test="loading"></div>
        <div class="overflow-wrapper" v-if="total > 0">
            <div class="selectable-table" v-if="!is_loading">
                <span
                    class="headers-cell"
                    v-for="(column_name, column_index) of columns"
                    v-bind:key="column_name"
                    v-bind:class="{
                        'is-last-cell-of-row': isLastCellOfRow(column_index, columns.size),
                        'is-pretty-title-column': column_name === PRETTY_TITLE_COLUMN_NAME,
                    }"
                    data-test="column-header"
                    >{{ getColumnName(column_name) }}</span
                >
                <artifact-rows
                    v-bind:rows="rows"
                    v-bind:columns="columns"
                    v-bind:level="0"
                    v-bind:tql_query="tql_query"
                    v-bind:ancestors="[]"
                />
            </div>
        </div>
        <selectable-pagination
            v-if="!is_table_empty"
            v-bind:limit="limit"
            v-bind:offset="offset"
            v-bind:total_number="total"
            v-on:new-page="handleNewPage"
        />
    </section>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, useTemplateRef } from "vue";
import { strictInject } from "@tuleap/vue-strict-inject";
import {
    ARROW_REDRAW_TRIGGERER,
    EMITTER,
    GET_COLUMN_NAME,
    RETRIEVE_ARTIFACTS_TABLE,
} from "../../injection-symbols";

import type { ArtifactsTable } from "../../domain/ArtifactsTable";
import SelectablePagination from "./SelectablePagination.vue";
import EmptyState from "../EmptyState.vue";
import { ArtifactsRetrievalFault } from "../../domain/ArtifactsRetrievalFault";
import type { ColumnName } from "../../domain/ColumnName";
import { PRETTY_TITLE_COLUMN_NAME } from "../../domain/ColumnName";
import type { RefreshArtifactsEvent } from "../../helpers/widget-events";
import {
    SEARCH_ARTIFACTS_EVENT,
    NOTIFY_FAULT_EVENT,
    REFRESH_ARTIFACTS_EVENT,
    SEARCH_ARTIFACTS_SUCCESS_EVENT,
} from "../../helpers/widget-events";
import ArtifactRows from "./ArtifactRows.vue";

const column_name_getter = strictInject(GET_COLUMN_NAME);

const artifacts_retriever = strictInject(RETRIEVE_ARTIFACTS_TABLE);
const arrow_redraw_triggerer = strictInject(ARROW_REDRAW_TRIGGERER);

const props = defineProps<{
    tql_query: string;
}>();

const selectable_table_element = useTemplateRef<HTMLElement>("selectable-table");
const is_loading = ref(false);
const columns = ref<ArtifactsTable["columns"]>(new Set());
const rows = ref<ArtifactsTable["rows"]>([]);
const total = ref(0);
let offset = 0;
const limit = 30;

const is_table_empty = computed<boolean>(() => !is_loading.value && total.value === 0);
const number_of_selected_columns = ref(0);

const emitter = strictInject(EMITTER);

const emit = defineEmits<{
    (e: "search-finished"): void;
    (e: "search-started"): void;
}>();

function handleNewPage(new_offset: number): void {
    offset = new_offset;
    refreshArtifactList();
}

function refreshArtifactList(): void {
    resetArtifactList();
    getSelectableQueryContent(props.tql_query);
}

function resetArtifactList(): void {
    rows.value = [];
    columns.value = new Set<string>();
    is_loading.value = true;
}

onMounted(() => {
    refreshArtifactList();
    emitter.on(REFRESH_ARTIFACTS_EVENT, handleRefreshArtifactsEvent);
    emitter.on(SEARCH_ARTIFACTS_EVENT, handleSearchArtifactsEvent);

    if (!selectable_table_element.value) {
        return;
    }
    arrow_redraw_triggerer.listenToSelectableTableResize(selectable_table_element.value);
});

onBeforeUnmount(() => {
    emitter.off(REFRESH_ARTIFACTS_EVENT, handleRefreshArtifactsEvent);
    emitter.off(SEARCH_ARTIFACTS_EVENT, handleSearchArtifactsEvent);

    if (!selectable_table_element.value) {
        return;
    }
    arrow_redraw_triggerer.removeListener(selectable_table_element.value);
});

function handleRefreshArtifactsEvent(event: RefreshArtifactsEvent): void {
    resetArtifactList();
    getSelectableQueryContent(event.query.tql_query);
}

function handleSearchArtifactsEvent(): void {
    resetArtifactList();
    getSelectableQueryContent(props.tql_query);
}

function getSelectableQueryContent(tql_query: string): void {
    if (tql_query === "") {
        emit("search-finished");
        is_loading.value = false;
        return;
    }

    artifacts_retriever
        .getSelectableQueryResult(tql_query, limit, offset)
        .match(
            (content_with_total) => {
                columns.value = content_with_total.table.columns;
                number_of_selected_columns.value = columns.value.size - 1;
                rows.value = content_with_total.table.rows;
                total.value = content_with_total.total;
                emitter.emit(SEARCH_ARTIFACTS_SUCCESS_EVENT);
            },
            (fault) => {
                emitter.emit(NOTIFY_FAULT_EVENT, {
                    fault: ArtifactsRetrievalFault(fault),
                    tql_query,
                });
            },
        )
        .then(() => {
            emit("search-finished");
            is_loading.value = false;
        });
}

const getColumnName = (name: ColumnName): string => {
    return column_name_getter.getTranslatedColumnName(name);
};

function isLastCellOfRow(index: number, size: number): boolean {
    return index + 1 === size;
}
</script>

<style scoped lang="scss">
@use "../../../themes/cell";
@use "../../../themes/pretty-title";

.overflow-wrapper {
    margin: 0 calc(-1 * var(--tlp-medium-spacing));
    overflow-y: auto;
}

.selectable-table {
    display: grid;
    grid-template-columns:
        [edit] min-content
        repeat(v-bind(number_of_selected_columns), auto);
    grid-template-rows:
        [headers] var(--tlp-x-large-spacing)
        auto;
    font-size: 0.875rem;
}

.headers-cell {
    @include cell.cell-template;

    grid-row: headers;
    border-bottom: 2px solid var(--tlp-main-color);
    color: var(--tlp-main-color);
    white-space: nowrap;
}

.is-pretty-title-column {
    @include pretty-title.is-pretty-title-column;
}

.artifact-table {
    position: relative;
}
</style>
