<?php
/**
 * Copyright (c) Enalean, 2015-Present. All Rights Reserved.
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

namespace User\XML\Import;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class EmailDoesNotMatchUserTest extends \Tuleap\Test\PHPUnit\TestCase
{
    /** @var EmailDoesNotMatchUser */
    private $user;

    #[\Override]
    protected function setUp(): void
    {
        $this->user = new EmailDoesNotMatchUser(
            new \PFUser(['user_name' => 'cstevens', 'language_id' => 'en']),
            'email.in.xml',
            104,
            'cs1234'
        );
    }

    public function testItReturnsFalseWhenActionIsCreate(): void
    {
        $this->assertFalse($this->user->isActionAllowed('create'));
    }

    public function testItReturnsFalseWhenActionIsActivate(): void
    {
        $this->assertFalse($this->user->isActionAllowed('activate'));
    }

    public function testItReturnsFalseWhenActionIsMap(): void
    {
        $this->assertTrue($this->user->isActionAllowed('map'));
    }
}
