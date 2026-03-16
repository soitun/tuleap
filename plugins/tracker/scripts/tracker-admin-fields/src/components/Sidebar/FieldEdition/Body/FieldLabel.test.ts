/*
 * Copyright (c) Enalean, 2026 - present. All Rights Reserved.
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

import { describe, expect, it } from "vitest";
import { CONTAINER_FIELDSET } from "@tuleap/plugin-tracker-constants";
import type { StructureFields } from "@tuleap/plugin-tracker-rest-api-types";
import type { VueWrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import FieldLabel from "./FieldLabel.vue";
import type { Payload } from "../type";
import { PAYLOAD } from "../type";
import { getGlobalTestOptions } from "../../../../helpers/global-options-for-tests";
import { ref } from "vue";

describe("FieldLabel", () => {
    const field: StructureFields = {
        field_id: 123,
        name: "details",
        label: "Details",
        description: "",
        type: CONTAINER_FIELDSET,
        required: false,
        has_notifications: false,
        label_decorators: [],
    };

    function getWrapper(payload: Payload): VueWrapper {
        return shallowMount(FieldLabel, {
            props: {
                field,
            },
            global: {
                ...getGlobalTestOptions(),
                provide: {
                    [PAYLOAD.valueOf()]: ref(payload),
                },
            },
        });
    }

    it("should update the payload when the label is changed", async () => {
        const payload: Payload = {
            description: "Lorem",
        };

        const wrapper = getWrapper(payload);

        expect(payload).toStrictEqual({
            description: "Lorem",
        });

        await wrapper.find("input").setValue("New label");

        expect(payload).toStrictEqual({
            label: "New label",
            description: "Lorem",
        });
        expect(field.label).toStrictEqual("Details");
    });
});
