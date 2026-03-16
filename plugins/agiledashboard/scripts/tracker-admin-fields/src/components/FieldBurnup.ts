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

import burnup_url from "../assets/fake-burnup-admin.png";
import { gettext_provider } from "../gettext-provider";

export const TAG = "tuleap-field-burnup";

class FieldBurnupElement extends HTMLElement {
    public connectedCallback(): void {
        const img = document.createElement("img");
        img.src = burnup_url;
        img.alt = gettext_provider.gettext("Fake image of a burnup");
        img.setAttribute("data-not-drag-handle", "true");

        this.appendChild(img);
    }
}

if (!customElements.get("tuleap-field-burnup")) {
    customElements.define("tuleap-field-burnup", FieldBurnupElement);
}
