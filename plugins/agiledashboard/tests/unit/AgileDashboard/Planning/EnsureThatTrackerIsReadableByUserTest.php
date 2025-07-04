<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

namespace Tuleap\AgileDashboard\AgileDashboard\Planning;

use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class EnsureThatTrackerIsReadableByUserTest extends TestCase
{
    public function testCanUserViewTracker(): void
    {
        $verifier = new EnsureThatTrackerIsReadableByUser();

        $user = UserTestBuilder::buildWithDefaults();

        $tracker1 = $this->createMock(\Tuleap\Tracker\Tracker::class);
        $tracker1->method('userCanView')->willReturn(false);
        self::assertFalse($verifier->canUserViewTracker($user, $tracker1));

        $tracker2 = $this->createMock(\Tuleap\Tracker\Tracker::class);
        $tracker2->method('userCanView')->willReturn(true);
        self::assertTrue($verifier->canUserViewTracker($user, $tracker2));
    }
}
