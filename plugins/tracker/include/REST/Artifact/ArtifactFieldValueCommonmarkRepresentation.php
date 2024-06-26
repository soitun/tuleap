<?php
/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 *
 */

declare(strict_types=1);

namespace Tuleap\Tracker\REST\Artifact;

use Tracker_Artifact_ChangesetValue_Text;
use Tuleap\REST\JsonCast;

/**
 * @psalm-immutable
 */
final class ArtifactFieldValueCommonmarkRepresentation implements ArtifactTextFieldValueRepresentation
{
    /**
     * @var int ID of the field
     */
    public $field_id;

    /**
     * @var string Type of the field
     */
    public $type;

    /**
     * @var string Label of the field
     */
    public $label;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $format;
    /**
     * @var string
     */
    public $commonmark;

    public function __construct(
        int $id,
        string $type,
        string $label,
        string $value,
        string $commonmark,
        public string $post_processed_value,
    ) {
        $this->field_id   = JsonCast::toInt($id);
        $this->type       = $type;
        $this->label      = $label;
        $this->value      = $value;
        $this->format     = Tracker_Artifact_ChangesetValue_Text::HTML_CONTENT;
        $this->commonmark = $commonmark;
    }
}
