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

namespace Tuleap\Tracker\Test\Stub\Semantic\Title;

use Tuleap\Tracker\FormElement\Field\Text\TextField;
use Tuleap\Tracker\Semantic\Title\RetrieveSemanticTitleField;
use Tuleap\Tracker\Tracker;

final class RetrieveSemanticTitleFieldStub implements RetrieveSemanticTitleField
{
    private int $call_count = 0;
    /** @var array<int, (TextField | null)> */
    private array $tracker_titles = [];

    private function __construct()
    {
    }

    public static function build(): self
    {
        return new self();
    }

    public function withTitleField(TextField $title_field): self
    {
        $this->tracker_titles[$title_field->getTrackerId()] = $title_field;
        return $this;
    }

    public function withoutTitleField(TextField $title_field): self
    {
        $this->tracker_titles[$title_field->getTrackerId()] = null;
        return $this;
    }

    #[\Override]
    public function fromTracker(Tracker $tracker): ?TextField
    {
        $this->call_count++;
        return $this->tracker_titles[$tracker->getId()] ?? null;
    }

    public function getCallCount(): int
    {
        return $this->call_count;
    }
}
