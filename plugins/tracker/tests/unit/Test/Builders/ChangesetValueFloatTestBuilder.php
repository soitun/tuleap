<?php
/**
 * Copyright (c) Enalean, 2025-Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Test\Builders;

final class ChangesetValueFloatTestBuilder
{
    private ?float $value = 0;

    private function __construct(
        private readonly int $id,
        private readonly \Tracker_Artifact_Changeset $changeset,
        private readonly \Tuleap\Tracker\FormElement\Field\Float\FloatField $field,
    ) {
    }

    public static function aValue(int $id, \Tracker_Artifact_Changeset $changeset, \Tuleap\Tracker\FormElement\Field\Float\FloatField $field): self
    {
        return new self($id, $changeset, $field);
    }

    public function withValue(?float $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function build(): \Tracker_Artifact_ChangesetValue_Float
    {
        $changeset_value = new \Tracker_Artifact_ChangesetValue_Float(
            $this->id,
            $this->changeset,
            $this->field,
            true,
            $this->value
        );

        $this->changeset->setFieldValue($this->field, $changeset_value);

        return $changeset_value;
    }
}
