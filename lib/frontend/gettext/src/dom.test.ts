/*
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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
import { getLocaleWithDefault } from "./dom";
import { en_US_LOCALE } from "@tuleap/core-constants";

describe(`dom`, () => {
    describe(`getLocaleWithDefault()`, () => {
        it(`reads the data-user-locale attribute from the given document's body and returns it`, () => {
            const doc = document.implementation.createHTMLDocument();
            const locale_string = "fr_FR";
            doc.body.setAttribute("data-user-locale", locale_string);
            expect(getLocaleWithDefault(doc)).toBe(locale_string);
        });

        it(`defaults to "en_US" when the document body has no data-user-locale attribute`, () => {
            const doc = document.implementation.createHTMLDocument();
            expect(getLocaleWithDefault(doc)).toBe(en_US_LOCALE);
        });
    });
});
