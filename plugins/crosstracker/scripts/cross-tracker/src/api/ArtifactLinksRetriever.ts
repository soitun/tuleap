/**
 * Copyright (c) Enalean, 2025 - Present. All Rights Reserved.
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

import type { ResultAsync } from "neverthrow";
import type { Fault } from "@tuleap/fault";
import { decodeJSON, getResponse, uri, getAllJSON } from "@tuleap/fetch-result";
import type { ArtifactsTableWithTotal } from "../domain/RetrieveArtifactsTable";
import type { RetrieveArtifactLinks } from "../domain/RetrieveArtifactLinks";
import type { ArtifactsTable } from "../domain/ArtifactsTable";
import type { SelectableQueryContentRepresentation } from "./cross-tracker-rest-api-types";
import type { ArtifactsTableBuilder } from "./ArtifactsTableBuilder";

export const MAXIMAL_LIMIT_OF_ARTIFACT_LINKS_FETCHED = 50;

export const ArtifactLinksRetriever = (
    table_builder: ArtifactsTableBuilder,
): RetrieveArtifactLinks => {
    return {
        getForwardLinks(
            widget_id: number,
            artifact_id: number,
            tql_query: string,
        ): ResultAsync<ArtifactsTableWithTotal, Fault> {
            return getResponse(uri`/api/v1/crosstracker_widget/${widget_id}/forward_links`, {
                params: {
                    source_artifact_id: artifact_id,
                    tql_query,
                    limit: MAXIMAL_LIMIT_OF_ARTIFACT_LINKS_FETCHED,
                },
            }).andThen((response) => {
                const total = Number.parseInt(response.headers.get("X-PAGINATION-SIZE") ?? "0", 10);
                return decodeJSON<SelectableQueryContentRepresentation>(response).map(
                    (query_content) => {
                        return {
                            table: table_builder.mapQueryContentToArtifactsTable(query_content),
                            total,
                        };
                    },
                );
            });
        },

        getReverseLinks(
            widget_id: number,
            artifact_id: number,
            tql_query: string,
        ): ResultAsync<ArtifactsTableWithTotal, Fault> {
            return getResponse(uri`/api/v1/crosstracker_widget/${widget_id}/reverse_links`, {
                params: {
                    target_artifact_id: artifact_id,
                    tql_query,
                    limit: MAXIMAL_LIMIT_OF_ARTIFACT_LINKS_FETCHED,
                },
            }).andThen((response) => {
                const total = Number.parseInt(response.headers.get("X-PAGINATION-SIZE") ?? "0", 10);
                return decodeJSON<SelectableQueryContentRepresentation>(response).map(
                    (query_content) => {
                        return {
                            table: table_builder.mapQueryContentToArtifactsTable(query_content),
                            total,
                        };
                    },
                );
            });
        },

        getAllForwardLinks(
            widget_id: number,
            artifact_id: number,
            tql_query: string,
        ): ResultAsync<ArtifactsTable[], Fault> {
            return getAllJSON<SelectableQueryContentRepresentation>(
                uri`/api/v1/crosstracker_widget/${widget_id}/forward_links`,
                {
                    params: {
                        source_artifact_id: artifact_id,
                        tql_query,
                        limit: MAXIMAL_LIMIT_OF_ARTIFACT_LINKS_FETCHED,
                    },
                },
            ).map((query_content) => {
                return query_content.map((table) =>
                    table_builder.mapQueryContentToArtifactsTable(table),
                );
            });
        },

        getAllReverseLinks(
            widget_id: number,
            artifact_id: number,
            tql_query: string,
        ): ResultAsync<ArtifactsTable[], Fault> {
            return getAllJSON<SelectableQueryContentRepresentation>(
                uri`/api/v1/crosstracker_widget/${widget_id}/reverse_links`,
                {
                    params: {
                        target_artifact_id: artifact_id,
                        tql_query,
                        limit: MAXIMAL_LIMIT_OF_ARTIFACT_LINKS_FETCHED,
                    },
                },
            ).map((query_content) => {
                return query_content.map((table) =>
                    table_builder.mapQueryContentToArtifactsTable(table),
                );
            });
        },
    };
};
