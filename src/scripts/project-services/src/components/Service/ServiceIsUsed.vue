<!--
  - Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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
    <div class="tlp-form-element">
        <label class="tlp-label tlp-checkbox" v-bind:for="id">
            <input
                v-bind:id="id"
                type="checkbox"
                name="is_used"
                value="1"
                v-bind:checked="value"
                v-bind:disabled="value === false && disabledReason !== ''"
                data-test="service-is-used"
            />
            {{ $gettext("Enabled") }}
        </label>
        <div
            v-if="value === false && disabledReason !== ''"
            class="tlp-text-info"
            data-test="service-disabled-message"
        >
            {{ disabledReason }}
        </div>
    </div>
</template>
<script>
export default {
    name: "ServiceIsUsed",
    props: ["id", "value", "disabledReason"],
};
</script>
