<!--
  - Copyright (c) Enalean, 2026 - Present. All Rights Reserved.
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
    <sidebar-container>
        <form class="tlp-pane-container" v-on:submit.prevent="submit">
            <div class="tlp-pane-header">
                <h1 class="tlp-pane-title">{{ title }}</h1>
            </div>
            <div class="tlp-pane-section">
                <not-found v-if="field === undefined" />
                <field-edition-body v-else v-bind:field="field" />
            </div>
            <field-edition-footer v-if="field" v-bind:field="field" />
        </form>
    </sidebar-container>
    <field-edition-error-modal v-if="update_error !== ''" v-bind:error="update_error" />
</template>

<script setup lang="ts">
import { computed, provide, ref } from "vue";
import SidebarContainer from "../SidebarContainer.vue";
import { FIELDS } from "../../../injection-symbols";
import { strictInject } from "@tuleap/vue-strict-inject";
import { useGettext } from "vue3-gettext";
import FieldEditionBody from "./FieldEditionBody.vue";
import { CONTAINER_FIELDSET } from "@tuleap/plugin-tracker-constants";
import NotFound from "../../NotFound.vue";
import FieldEditionFooter from "./FieldEditionFooter.vue";
import type { Payload } from "./type";
import { IS_UPDATING, PAYLOAD } from "./type";
import { patchJSON, uri } from "@tuleap/fetch-result";
import { useRouter } from "vue-router";
import FieldEditionErrorModal from "./FieldEditionErrorModal.vue";

const { $gettext } = useGettext();

const props = defineProps<{ field_id: number; location: Location }>();

const fields = strictInject(FIELDS);

const field = computed(() =>
    fields.find((field) => {
        return field.field_id === props.field_id;
    }),
);

const title = computed(() =>
    field.value?.type === CONTAINER_FIELDSET
        ? $gettext("Fieldset configuration")
        : $gettext("Field configuration"),
);

const payload = ref<Payload>({});
const is_updating = ref(false);
const update_error = ref("");
provide(PAYLOAD, payload);
provide(IS_UPDATING, is_updating);

const router = useRouter();

function submit(): void {
    if (Object.keys(payload.value).length === 0) {
        return;
    }

    is_updating.value = true;
    update_error.value = "";
    patchJSON(uri`/api/v1/tracker_fields/${props.field_id}`, payload.value).match(
        () => {
            props.location.assign(router.resolve({ name: "fields-usage" }).href);
        },
        (fault) => {
            update_error.value = String(fault);
            is_updating.value = false;
        },
    );
}
</script>
