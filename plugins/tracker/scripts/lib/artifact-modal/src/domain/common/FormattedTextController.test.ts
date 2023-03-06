/*
 * Copyright (c) Enalean, 2023-Present. All Rights Reserved.
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

import { EventDispatcher } from "../EventDispatcher";
import type { FormattedTextControllerType } from "./FormattedTextController";
import { FormattedTextController } from "./FormattedTextController";
import type { FileUploadSetup } from "../fields/file-field/FileUploadSetup";

describe(`FormattedTextController`, () => {
    let dispatcher: EventDispatcher;

    beforeEach(() => {
        dispatcher = EventDispatcher();
    });

    const getController = (): FormattedTextControllerType => FormattedTextController(dispatcher);

    describe(`getFileUploadSetup()`, () => {
        it(`dispatches an event and returns the contents of that event`, () => {
            const upload_setup: FileUploadSetup = {
                file_field_id: 749,
                max_size_upload: 7000,
                file_creation_uri: "https://example.com/upload",
            };
            dispatcher.addObserver("WillGetFileUploadSetup", (event) => {
                event.setup = upload_setup;
            });

            expect(getController().getFileUploadSetup()).toBe(upload_setup);
        });

        it(`when nobody responds, it returns null`, () => {
            expect(getController().getFileUploadSetup()).toBeNull();
        });
    });
});