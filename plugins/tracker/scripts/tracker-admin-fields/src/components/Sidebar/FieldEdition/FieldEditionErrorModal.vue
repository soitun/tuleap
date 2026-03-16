<!--
  - Copyright (c) Enalean, 2026 - present. All Rights Reserved.
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
    <Teleport to="body">
        <div
            role="dialog"
            aria-labelledby="field-edition-error-modal-title"
            class="tlp-modal tlp-modal-danger"
            ref="modal_element"
        >
            <div class="tlp-modal-header">
                <h1 class="tlp-modal-title" id="field-edition-error-modal-title">
                    {{ $gettext("Oops, an error occurred!") }}
                </h1>
                <button
                    class="tlp-modal-close"
                    type="button"
                    data-dismiss="modal"
                    v-bind:aria-label="$gettext('Close')"
                >
                    <i class="fa-solid fa-xmark tlp-modal-close-icon" aria-hidden="true"></i>
                </button>
            </div>
            <div class="tlp-modal-body">
                <p>
                    {{ $gettext("An error occurred during the save.") }}
                </p>
                <div class="tlp-alert-danger">{{ error }}</div>
            </div>
            <div class="tlp-modal-footer">
                <button
                    type="button"
                    class="tlp-button-danger tlp-modal-action"
                    data-dismiss="modal"
                >
                    {{ $gettext("Close") }}
                </button>
            </div>
        </div>
    </Teleport>
</template>
<script setup lang="ts">
import { onMounted, onBeforeUnmount, useTemplateRef } from "vue";
import { useGettext } from "vue3-gettext";
import type { Modal } from "@tuleap/tlp-modal";
import { createModal } from "@tuleap/tlp-modal";

const { $gettext } = useGettext();

const modal_element = useTemplateRef<HTMLElement>("modal_element");
let modal: Modal | null = null;

defineProps<{
    error: string;
}>();

onMounted(() => {
    if (modal_element.value) {
        modal = createModal(modal_element.value);
        modal.show();
    }
});

onBeforeUnmount(() => {
    modal?.destroy();
});
</script>
