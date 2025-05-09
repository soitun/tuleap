/*
 * Copyright (c) Enalean, 2025-present. All Rights Reserved.
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

import type { PutQueryRepresentation, QueryRepresentation } from "./cross-tracker-rest-api-types";
import type { ResultAsync } from "neverthrow";
import type { UpdateQuery } from "../domain/UpdateQuery";
import { putJSON, uri } from "@tuleap/fetch-result";
import type { Fault } from "@tuleap/fault";
import type { Query } from "../type";

export const QueryUpdater = (): UpdateQuery => {
    return {
        updateQuery(
            current_query: Query,
            query_to_update: PutQueryRepresentation,
        ): ResultAsync<QueryRepresentation, Fault> {
            return putJSON(uri`/api/v1/crosstracker_query/${current_query.id}`, {
                ...query_to_update,
            });
        },
    };
};
