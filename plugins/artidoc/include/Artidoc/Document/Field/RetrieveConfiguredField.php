<?php
/**
 * Copyright (c) Enalean, 2025 - Present. All Rights Reserved.
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

namespace Tuleap\Artidoc\Document\Field;

use Tuleap\Artidoc\Domain\Document\Section\Field\ArtifactSectionField;
use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifier;

interface RetrieveConfiguredField
{
    /**
     * @return list<ArtifactSectionField>
     */
    public function retrieveConfiguredFieldsFromItemId(int $item_id): array;

    /**
     * @return list<ArtifactSectionField>
     */
    public function retrieveConfiguredFieldsFromSectionId(SectionIdentifier $section_identifier): array;
}
