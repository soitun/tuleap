/*
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

import type { VueWrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import PeoplePicker from "./PeoplePicker.vue";
import { getGlobalTestOptions } from "../../../../../../../helpers/global-options-for-test";
import type { UserForPeoplePicker } from "../../../../../../../store/swimlane/card/UserForPeoplePicker";

const mocked_jquery = {
    select2: jest.fn(),
    on: jest.fn(),
    off: jest.fn(),
    val: jest.fn(),
};

jest.mock("jquery", () => {
    return (): Record<string, jest.SpyInstance> => mocked_jquery;
});

function getWrapper(is_multiple = true): VueWrapper<InstanceType<typeof PeoplePicker>> {
    return shallowMount(PeoplePicker, {
        global: { ...getGlobalTestOptions({}) },
        props: {
            is_multiple,
            users: [
                {
                    id: 101,
                    avatar_url: "steeve.png",
                    display_name: "Steeve",
                } as UserForPeoplePicker,
            ],
            value: [],
        },
    });
}

describe("PeoplePicker", () => {
    beforeEach(() => {
        mocked_jquery.on.mockImplementation(() => mocked_jquery);
        mocked_jquery.off.mockImplementation(() => mocked_jquery);
        mocked_jquery.select2.mockImplementation(() => mocked_jquery);
    });

    it("Display a select2 element", () => {
        const wrapper = getWrapper();

        expect(mocked_jquery.select2).toHaveBeenCalledWith("open");

        expect(wrapper.element).toMatchSnapshot();
    });

    it("Display a select2 element with an empty option if it is not multiple", () => {
        const wrapper = getWrapper(false);

        expect(mocked_jquery.select2).toHaveBeenCalledWith("open");

        const options = wrapper.findAll("option");
        expect(options).toHaveLength(1);
        expect(options.at(0)).toMatchInlineSnapshot("<option></option>");
    });

    it("Destroys the select2", () => {
        const wrapper = getWrapper();
        wrapper.unmount();

        expect(mocked_jquery.select2).toHaveBeenCalledWith("destroy");
    });

    it("Listens to changes", () => {
        getWrapper();

        expect(mocked_jquery.on).toHaveBeenCalledWith("change", expect.any(Function));
    });

    describe("onchange", () => {
        let onchange_callback = (): number[] => [];
        beforeEach(() => {
            mocked_jquery.on.mockImplementation((event, callback) => {
                onchange_callback = callback;
                return mocked_jquery;
            });
        });

        it("Emits input event on change with selected ids as number", () => {
            const wrapper = getWrapper();

            mocked_jquery.val.mockImplementation(() => ["123", "234"]);
            onchange_callback();

            const input = wrapper.emitted().input;
            if (!input) {
                throw new Error("Should have received input");
            }
            expect(input[0]).toStrictEqual([[123, 234]]);
        });

        it("Emits input event on change with empty array if val() returns null", () => {
            const wrapper = getWrapper();

            mocked_jquery.val.mockImplementation(() => null);
            onchange_callback();

            const input = wrapper.emitted().input;
            if (!input) {
                throw new Error("Should have received input");
            }
            expect(input[0]).toStrictEqual([[]]);
        });

        it("Emits input event on change with empty array if val() returns undefined", () => {
            const wrapper = getWrapper();

            mocked_jquery.val.mockImplementation(() => undefined);
            onchange_callback();

            const input = wrapper.emitted().input;
            if (!input) {
                throw new Error("Should have received input");
            }
            expect(input[0]).toStrictEqual([[]]);
        });

        it("Emits input event on change with array of one element if val() returns a string", () => {
            const wrapper = getWrapper();

            mocked_jquery.val.mockImplementation(() => "123");
            onchange_callback();

            const input = wrapper.emitted().input;
            if (!input) {
                throw new Error("Should have received input");
            }
            expect(input[0]).toStrictEqual([[123]]);
        });

        it("Emits input event on change with array of one element if val() returns a number", () => {
            const wrapper = getWrapper();

            mocked_jquery.val.mockImplementation(() => 123);
            onchange_callback();

            const input = wrapper.emitted().input;
            if (!input) {
                throw new Error("Should have received input");
            }
            expect(input[0]).toStrictEqual([[123]]);
        });
    });
});
