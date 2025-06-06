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

import type { CurrentTrackerIdentifier, Identifier } from "@tuleap/plugin-tracker-artifact-common";

export type TrackerIdentifier = Identifier<"TrackerIdentifier">;

export const TrackerIdentifier = {
    fromId: (id: number): TrackerIdentifier => ({ id, _type: "TrackerIdentifier" }),

    fromCurrentTracker: (
        current_tracker_identifier: CurrentTrackerIdentifier,
    ): TrackerIdentifier => ({
        id: current_tracker_identifier.id,
        _type: "TrackerIdentifier",
    }),
};
