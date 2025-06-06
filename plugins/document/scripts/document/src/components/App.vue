<!--
  - Copyright (c) Enalean, 2018-Present. All Rights Reserved.
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
    <div class="document-app">
        <document-breadcrumb v-if="!has_folder_permission_error" />

        <permission-error v-if="has_folder_permission_error" />
        <item-permission-error
            v-else-if="has_document_permission_error"
            v-bind:csrf_token="csrf_token"
            v-bind:csrf_token_name="csrf_token_name"
        />
        <loading-error
            v-else-if="
                has_folder_loading_error || has_document_loading_error || has_document_lock_error
            "
        />
        <router-view v-else />
        <global-error-modal v-if="has_global_modal_error" />
        <switch-to-old-u-i v-if="can_user_switch_to_old_ui" />
        <post-item-deletion-notification />
    </div>
</template>
<script setup lang="ts">
import DocumentBreadcrumb from "./Breadcrumb/DocumentBreadcrumb.vue";
import PermissionError from "./Folder/Error/PermissionError.vue";
import ItemPermissionError from "./Folder/Error/ItemPermissionError.vue";
import LoadingError from "./Folder/Error/LoadingError.vue";
import GlobalErrorModal from "./Folder/Error/GlobalErrorModal.vue";
import SwitchToOldUI from "./Folder/SwitchToOldUI.vue";
import PostItemDeletionNotification from "./Folder/DropDown/Delete/PostItemDeletionNotification.vue";
import { onMounted } from "vue";
import { useGetters, useNamespacedState, useStore } from "vuex-composition-helpers";
import { useGettext } from "vue3-gettext";
import type { ErrorState } from "../store/error/module";
import type { ConfigurationState } from "../store/configuration";
import type { RootGetter } from "../store/getters";

const { $gettext } = useGettext();
const store = useStore();

const {
    has_folder_permission_error,
    has_folder_loading_error,
    has_document_permission_error,
    has_document_loading_error,
    has_document_lock_error,
    has_global_modal_error,
} = useNamespacedState<
    Pick<
        ErrorState,
        | "has_folder_permission_error"
        | "has_folder_loading_error"
        | "has_document_permission_error"
        | "has_document_loading_error"
        | "has_document_lock_error"
        | "has_global_modal_error"
    >
>("error", [
    "has_folder_permission_error",
    "has_folder_loading_error",
    "has_document_permission_error",
    "has_document_loading_error",
    "has_document_lock_error",
    "has_global_modal_error",
]);
const { can_user_switch_to_old_ui } = useNamespacedState<
    Pick<ConfigurationState, "can_user_switch_to_old_ui">
>("configuration", ["can_user_switch_to_old_ui"]);
const { is_uploading } = useGetters<Pick<RootGetter, "is_uploading">>(["is_uploading"]);

defineProps<{
    csrf_token: string;
    csrf_token_name: string;
}>();

onMounted(() => {
    const base_title = document.title;
    store.watch(
        (state, getters) => getters.current_folder_title,
        (title, old_title) => {
            if (title) {
                document.title = title + " - " + base_title;
            } else if (old_title) {
                document.title = base_title;
            }
        },
    );

    store.commit("setRootTitle", $gettext("Documents"));

    window.addEventListener("beforeunload", (event) => {
        if (is_uploading.value) {
            event.returnValue = true;
            event.preventDefault();
        }
    });
});
</script>
