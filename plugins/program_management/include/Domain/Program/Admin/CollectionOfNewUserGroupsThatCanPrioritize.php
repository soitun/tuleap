<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Program\Admin;

use Tuleap\ProgramManagement\Domain\Program\Plan\InvalidProgramUserGroup;
use Tuleap\ProgramManagement\Domain\Program\Plan\NewUserGroupThatCanPrioritize;
use Tuleap\ProgramManagement\Domain\Program\Plan\RetrieveProgramUserGroup;

/**
 * @psalm-immutable
 */
final readonly class CollectionOfNewUserGroupsThatCanPrioritize
{
    /**
     * @param list<NewUserGroupThatCanPrioritize> $user_group_identifiers
     */
    private function __construct(private array $user_group_identifiers)
    {
    }

    /**
     * @param non-empty-list<string> $raw_user_group_ids
     * @throws InvalidProgramUserGroup
     */
    public static function fromRawIdentifiers(
        RetrieveProgramUserGroup $user_group_builder,
        ProgramForAdministrationIdentifier $program,
        array $raw_user_group_ids,
    ): self {
        $program_user_groups = [];

        foreach ($raw_user_group_ids as $raw_user_group_id) {
            $program_user_groups[] = NewUserGroupThatCanPrioritize::fromId(
                $user_group_builder,
                $raw_user_group_id,
                $program
            );
        }

        return new self($program_user_groups);
    }

    /**
     * @param list<NewUserGroupThatCanPrioritize> $user_groups_that_can_prioritize
     */
    public static function fromUserGroups(array $user_groups_that_can_prioritize): self
    {
        return new self($user_groups_that_can_prioritize);
    }

    /**
     * @return list<int>
     */
    public function getUserGroupIds(): array
    {
        return array_map(
            static fn(NewUserGroupThatCanPrioritize $user_group) => $user_group->id,
            $this->user_group_identifiers
        );
    }
}
