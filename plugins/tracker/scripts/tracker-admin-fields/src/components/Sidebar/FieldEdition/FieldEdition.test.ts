/*
 * Copyright (c) Enalean, 2026 - Present. All Rights Reserved.
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

import { describe, expect, it, vi } from "vitest";
import FieldEdition from "./FieldEdition.vue";
import { FIELDS, HANDLE_REMOVE_FIELD, TRACKER_ROOT } from "../../../injection-symbols";
import type { VueWrapper } from "@vue/test-utils";
import { mount } from "@vue/test-utils";
import { CONTAINER_FIELDSET } from "@tuleap/plugin-tracker-constants";
import FieldEditionBody from "./FieldEditionBody.vue";
import NotFound from "../../NotFound.vue";
import { createGettext } from "vue3-gettext";
import { getRouter } from "../../../router/fields-usage-router";
import * as rest from "@tuleap/fetch-result";
import { uri } from "@tuleap/fetch-result";
import { okAsync } from "neverthrow";

describe("FieldEdition", () => {
    const FIELDS_USAGE_BASE_URL = "/trackers/101/fields/";

    function getWrapper(field_id: number, location: Location = window.location): VueWrapper {
        return mount(FieldEdition, {
            props: {
                field_id,
                location,
            },
            global: {
                plugins: [getRouter(FIELDS_USAGE_BASE_URL), createGettext({ silent: true })],
                provide: {
                    [FIELDS.valueOf()]: [
                        {
                            field_id: 123,
                            name: "details",
                            label: "Details",
                            type: CONTAINER_FIELDSET,
                            required: false,
                            has_notifications: false,
                            label_decorators: [],
                        },
                    ],
                    [HANDLE_REMOVE_FIELD.valueOf()]: () => {},
                    [TRACKER_ROOT.valueOf()]: { children: [] },
                },
            },
        });
    }
    it("should display error message if field is unknown", () => {
        const wrapper = getWrapper(12345);

        expect(wrapper.findComponent(NotFound).exists()).toBe(true);
        expect(wrapper.findComponent(FieldEditionBody).exists()).toBe(false);
    });

    it("should display edition body if field is known", () => {
        const wrapper = getWrapper(123);

        expect(wrapper.findComponent(NotFound).exists()).toBe(false);
        expect(wrapper.findComponent(FieldEditionBody).exists()).toBe(true);
    });

    it("should not submit anything if nothing is changed", async () => {
        const wrapper = getWrapper(123);

        const patch = vi.spyOn(rest, "patchJSON");

        await wrapper.find("form").trigger("submit");

        expect(patch).not.toHaveBeenCalled();
    });

    it("should submit form if something is changed", async () => {
        const wrapper = getWrapper(123);

        const patch = vi.spyOn(rest, "patchJSON");

        await wrapper.find("[data-test=label]").setValue("New label");
        await wrapper.find("form").trigger("submit");

        expect(patch).toHaveBeenCalledWith(uri`/api/v1/tracker_fields/123`, {
            label: "New label",
        });
    });

    it(`should reload the field usage page after the PATCH
        so that warning messages that depends on the current
        field configuration are accurate (eg Burnup/Burndown)`, async () => {
        const location: Location = { ...window.location, assign: vi.fn() };
        const wrapper = getWrapper(123, location);

        vi.spyOn(rest, "patchJSON").mockReturnValue(okAsync(true));

        await wrapper.find("[data-test=label]").setValue("New label");
        await wrapper.find("form").trigger("submit");

        expect(location.assign).toHaveBeenCalledWith(FIELDS_USAGE_BASE_URL);
    });
});
