<?php
/**
 * Copyright (c) Enalean, 2025 - present. All Rights Reserved.
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

namespace Tuleap\Artidoc\REST\v1\ArtifactSection\Field;

use Tuleap\Artidoc\Domain\Document\Section\Field\FieldWithValue\UserValue;

/**
 * @psalm-immutable
 */
final readonly class UserListValueRepresentation
{
    public string $display_name;
    public string $avatar_url;

    public function __construct(UserValue $value)
    {
        $this->display_name = $value->display_name;
        $this->avatar_url   = $value->avatar_url;
    }
}
