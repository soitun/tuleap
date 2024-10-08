<?php
/**
 * Copyright (c) Enalean, 2024-present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
 *
 *  Tuleap is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Tuleap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);
namespace Tuleap\CrossTracker\REST\v1;

use PFUser;
use Tuleap\CrossTracker\CrossTrackerExpertReport;
use Tuleap\REST\JsonCast;
use Tuleap\Tracker\REST\TrackerReference;

/**
 * @psalm-immutable
 */
final readonly class CrossTrackerExpertReportRepresentation implements CrossTrackerReportRepresentation
{
    /**
     * @param TrackerReference[] $trackers
     */
    private function __construct(
        public int $id,
        public string $uri,
        public string $expert_query,
        public array $trackers,
        public string $report_mode,
    ) {
    }

    public static function fromReport(CrossTrackerExpertReport $report, PFUser $user): self
    {
        $trackers = [];

        foreach ($report->getTrackers() as $tracker) {
            if ($tracker->userCanView($user)) {
                $trackers[] = TrackerReference::build($tracker);
            }
        }

        $report_id = JsonCast::toInt($report->getId());
        return new self(
            $report_id,
            CrossTrackerReportsResource::ROUTE . '/' . $report_id,
            $report->getExpertQuery(),
            $trackers,
            self::MODE_EXPERT
        );
    }
}
