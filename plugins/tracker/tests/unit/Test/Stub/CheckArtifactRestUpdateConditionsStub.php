<?php
/**
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Tracker\Test\Stub;

use Luracast\Restler\RestException;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\REST\Artifact\CheckArtifactRestUpdateConditions;

final class CheckArtifactRestUpdateConditionsStub implements CheckArtifactRestUpdateConditions
{
    private function __construct(private bool $will_allow_update)
    {
    }

    public static function allowArtifactUpdate(): self
    {
        return new self(true);
    }

    public static function disallowArtifactUpdate(): self
    {
        return new self(false);
    }

    #[\Override]
    public function checkIfArtifactUpdateCanBePerformedThroughREST(\PFUser $user, Artifact $artifact): void
    {
        if ($this->will_allow_update) {
            return;
        }

        throw new RestException(403, 'Artifact update through the REST API is not allowed');
    }
}
