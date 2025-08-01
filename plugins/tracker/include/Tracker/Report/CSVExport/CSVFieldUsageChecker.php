<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\Tracker\Report\CSVExport;

use Tracker_FormElement_Field;
use Tuleap\Tracker\FormElement\Field\ArtifactId\ArtifactIdField;
use Tracker_FormElement_Field_Burndown;
use Tuleap\Tracker\FormElement\Field\PerTrackerArtifactId\PerTrackerArtifactIdField;

class CSVFieldUsageChecker
{
    public static function canFieldBeExportedToCSV(Tracker_FormElement_Field $field): bool
    {
        return $field->isUsed()
            && $field->userCanRead()
            && ! (
                ($field instanceof ArtifactIdField && ! $field instanceof PerTrackerArtifactIdField)
                || $field instanceof Tracker_FormElement_Field_Burndown
            );
    }
}
