/**
 * Copyright (c) Enalean, 2026-Present. All Rights Reserved.
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

import { FetchWrapperError } from "@tuleap/tlp-fetch";
import { describe, expect, it } from "vitest";
import { transformRestErrorToFault } from "./rest-error-helper";

describe("rest-error-helper", () => {
    describe("transformRestErrorToFault", () => {
        it("Should transform FetchWrapperError into a fault with its message", async () => {
            const error = new FetchWrapperError(
                "Some message",
                new Response(
                    JSON.stringify({
                        error: {
                            code: 400,
                            message: "Bad request",
                        },
                    }),
                ),
            );

            const fault = await transformRestErrorToFault(error);
            expect(fault.toString()).toStrictEqual("400 Bad request");
        });

        it("Should transform classic error into a fault with its message", async () => {
            const fault = await transformRestErrorToFault(new Error("Some message"));
            expect(fault.toString()).toStrictEqual("Some message");
        });
    });
});
