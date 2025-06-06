<?php
/**
 * Copyright (c) Enalean, 2022 - Present. All Rights Reserved.
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

namespace Tuleap\MediawikiStandalone\Permissions;

use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
class ProjectPermissionsRetrieverTest extends TestCase
{
    public function testReaders(): void
    {
        $retriever = new ProjectPermissionsRetriever(
            ISearchByProjectStub::buildWithPermissions(
                [103, 104],
                [],
                [],
            )
        );

        self::assertEquals(
            [103, 104],
            $retriever->getProjectPermissions(ProjectTestBuilder::aProject()->build())->readers
        );
    }

    public function testReadersDefaultToProjectMembers(): void
    {
        $retriever = new ProjectPermissionsRetriever(ISearchByProjectStub::buildWithoutSpecificPermissions());

        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS],
            $retriever->getProjectPermissions(ProjectTestBuilder::aProject()->build())->readers
        );
    }

    public function testWriters(): void
    {
        $retriever = new ProjectPermissionsRetriever(
            ISearchByProjectStub::buildWithPermissions(
                [],
                [103, 104],
                [],
            )
        );

        self::assertEquals(
            [103, 104],
            $retriever->getProjectPermissions(ProjectTestBuilder::aProject()->build())->writers
        );
    }

    public function testWritersDefaultToProjectMembers(): void
    {
        $retriever = new ProjectPermissionsRetriever(ISearchByProjectStub::buildWithoutSpecificPermissions());

        self::assertEquals(
            [\ProjectUGroup::PROJECT_MEMBERS],
            $retriever->getProjectPermissions(ProjectTestBuilder::aProject()->build())->writers
        );
    }

    public function testAdminAlwaysIncludesProjectAdmin(): void
    {
        $retriever = new ProjectPermissionsRetriever(
            ISearchByProjectStub::buildWithPermissions(
                [],
                [],
                [103, 104],
            )
        );

        self::assertEquals(
            [\ProjectUGroup::PROJECT_ADMIN, 103, 104],
            $retriever->getProjectPermissions(ProjectTestBuilder::aProject()->build())->admins
        );
    }
}
