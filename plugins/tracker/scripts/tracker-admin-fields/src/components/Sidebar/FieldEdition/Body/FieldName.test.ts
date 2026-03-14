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
import FieldName from "./FieldName.vue";
import type { Payload } from "../type";
import { PAYLOAD } from "../type";
import { getGlobalTestOptions } from "../../../../helpers/global-options-for-tests";
import { ref } from "vue";

describe("FieldName", () => {
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
        return shallowMount(FieldName, {
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

    it("should update the payload when the name is changed", async () => {
        const payload: Payload = {
            description: "Lorem",
        };

        const wrapper = getWrapper(payload);

        expect(payload).toStrictEqual({
            description: "Lorem",
        });

        await wrapper.find("input").setValue("newname");

        expect(payload).toStrictEqual({
            name: "newname",
            description: "Lorem",
        });
        expect(field.name).toStrictEqual("details");
    });
});
