<!--
  - Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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
    <select
        class="tlp-input"
        id="document-item-owner"
        name="owner"
        required
        ref="owner_input"
        data-test="people-picker-select"
    >
        <option selected v-bind:value="currently_selected_user.id">
            {{ currently_selected_user.display_name }}
        </option>
        <slot />
    </select>
</template>

<script lang="ts">
import { autocomplete_users_for_select2 } from "@tuleap/autocomplete-for-select2";
import emitter from "../../../../helpers/emitter";

export default {
    name: "PeoplePicker",
    props: {
        currently_selected_user: Object,
    },
    data() {
        return {
            select2_people_picker: null,
        };
    },
    mounted() {
        const current_user = {
            id: this.currently_selected_user.id,
        };

        const configuration = {
            data: { ...current_user },
            codendiUserOnly: true,
            use_tuleap_id: true,
        };

        this.select2_people_picker = autocomplete_users_for_select2(this.$el, {
            ...configuration,
            multiple: false,
        })
            .trigger("change")
            .on("change", (event) => {
                const updated_value = parseInt(event.target.value, 10);
                if (isNaN(updated_value)) {
                    return;
                }
                emitter.emit("update-owner-property", updated_value);
            });
    },
    destroyed() {
        this.select2_people_picker.off().select2("destroy");
    },
};
</script>
