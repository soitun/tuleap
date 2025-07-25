<?php
/**
 * Copyright (c) Enalean 2023 - Present. All Rights Reserved.
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

use SimpleXMLElement;
use Tuleap\Tracker\Tracker\XML\Updater\UpdateArtifactLinkXML;

final class UpdateArtifactLinkXMLStub implements UpdateArtifactLinkXML
{
    private function __construct()
    {
    }

    public static function build(): self
    {
        return new self();
    }

    #[\Override]
    public function updateArtifactLinks(SimpleXMLElement $changeset_xml, \Tuleap\Tracker\FormElement\Field\ArtifactLink\ArtifactLinkField $destination_field, int $index): void
    {
        // Side-effects
    }
}
