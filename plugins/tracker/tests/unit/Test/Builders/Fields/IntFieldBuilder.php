<?php
/**
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

declare(strict_types=1);

namespace Tuleap\Tracker\Test\Builders\Fields;

use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class IntFieldBuilder
{
    use FieldBuilderWithSpecificProperties;

    private string $name                        = 'initial_effort';
    private string $label                       = 'Initial effort';
    private bool $read_permission               = false;
    private ?\PFUser $user_with_read_permission = null;
    private bool $use_it                        = true;
    private \Tuleap\Tracker\Tracker $tracker;
    private bool $is_required = false;

    private function __construct(private readonly int $id)
    {
        $this->tracker = TrackerTestBuilder::aTracker()->withId(10)->build();
    }

    public static function anIntField(int $id): self
    {
        return new self($id);
    }

    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function unused(): self
    {
        $this->use_it = false;

        return $this;
    }

    public function inTracker(\Tuleap\Tracker\Tracker $tracker): self
    {
        $this->tracker = $tracker;
        return $this;
    }

    public function withReadPermission(\PFUser $user, bool $user_can_read): self
    {
        $this->user_with_read_permission = $user;
        $this->read_permission           = $user_can_read;
        return $this;
    }

    public function withLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function thatIsRequired(): self
    {
        $this->is_required = true;
        return $this;
    }

    public function build(): \Tracker_FormElement_Field_Integer
    {
        $field = new \Tracker_FormElement_Field_Integer(
            $this->id,
            $this->tracker->getId(),
            15,
            $this->name,
            $this->label,
            '',
            $this->use_it,
            'P',
            $this->is_required,
            '',
            10,
            null
        );
        $field->setTracker($this->tracker);
        if ($this->user_with_read_permission !== null) {
            $field->setUserCanRead($this->user_with_read_permission, $this->read_permission);
        }
        $this->setSpecificProperties($field);
        return $field;
    }
}
