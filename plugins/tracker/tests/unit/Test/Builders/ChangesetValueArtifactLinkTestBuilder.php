<?php
/**
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

use Tracker_Artifact_Changeset;
use Tracker_ArtifactLinkInfo;
use Tuleap\Tracker\Artifact\Changeset\ArtifactLink\ArtifactLinkChangesetValue;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\ArtifactLinkField;

final class ChangesetValueArtifactLinkTestBuilder
{
    /** @var array<int, Tracker_ArtifactLinkInfo> */
    private array $forward_links = [];
    /** @var array<int, Tracker_ArtifactLinkInfo> */
    private array $reverse_links = [];

    private function __construct(
        private readonly int $id,
        private readonly Tracker_Artifact_Changeset $changeset,
        private readonly ArtifactLinkField $field,
    ) {
    }

    public static function aValue(int $id, Tracker_Artifact_Changeset $changeset, ArtifactLinkField $field): self
    {
        return new self($id, $changeset, $field);
    }

    /**
     * @param array<int, Tracker_ArtifactLinkInfo> $links
     */
    public function withForwardLinks(array $links): self
    {
        $this->forward_links = $links;

        return $this;
    }

    /**
     * @param array<int, Tracker_ArtifactLinkInfo> $links
     */
    public function withReverseLinks(array $links): self
    {
        $this->reverse_links = $links;
        return $this;
    }

    public function build(): ArtifactLinkChangesetValue
    {
        $value = new ArtifactLinkChangesetValue(
            $this->id,
            $this->changeset,
            $this->field,
            true,
            $this->forward_links,
            $this->reverse_links,
        );

        $this->changeset->setFieldValue($this->field, $value);

        return $value;
    }
}
