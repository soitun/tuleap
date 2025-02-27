<?php
/*
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\User;

interface StoreUserPreference
{
    /**
     * @return array{ preference_value: string } | []
     */
    public function search(int $user_id, string $preference_name): array;

    public function set(int $user_id, string $preference_name, string $preference_value): void;

    public function delete(int $user_id, string $preference_name): void;

    public function deleteByPreferenceNameAndValue(string $preference_name, string $preference_value): void;

    public function deletePreferenceForAllUsers(string $preference_name): void;
}
