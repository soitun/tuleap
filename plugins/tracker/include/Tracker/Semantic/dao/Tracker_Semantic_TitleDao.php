<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
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


class Tracker_Semantic_TitleDao extends DataAccessObject //phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'tracker_semantic_title';
    }

    public function getNbOfTrackerWithoutSemanticTitleDefined(array $trackers_id): int
    {
        $trackers_id = $this->da->escapeIntImplode($trackers_id);

        $sql = "SELECT count(*) AS nb
                FROM tracker
                    LEFT JOIN tracker_semantic_title AS title
                    ON (tracker.id = title.tracker_id)
                WHERE tracker.id IN ($trackers_id)
                    AND title.tracker_id IS NULL";

        $row = $this->retrieveFirstRow($sql);

        return (int) $row['nb'];
    }

    public function getTrackerIdsWithoutSemanticTitleDefined(array $trackers_id): array
    {
        $trackers_id = $this->da->escapeIntImplode($trackers_id);

        $sql = "SELECT tracker.id as id
                FROM tracker
                    LEFT JOIN tracker_semantic_title AS title
                    ON (tracker.id = title.tracker_id)
                WHERE tracker.id IN ($trackers_id)
                    AND title.tracker_id IS NULL";

        return $this->retrieveIds($sql);
    }
}
