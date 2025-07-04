<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

namespace Tuleap\Color;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use Tuleap\Test\PHPUnit\TestCase;

#[DisableReturnValueGenerationForTestDoubles]
final class ItemColorTest extends TestCase
{
    public function testColorCanBeBuiltFromAValidColorName(): void
    {
        $color_name = 'inca-silver';
        $color      = ItemColor::fromName($color_name);
        self::assertEquals($color_name, $color->getName());
    }

    /**
     * @testWith ["inca_silver"]
     *           ["inca-silver"]
     */
    public function testColorCanBeBuiltFromColorNameThatMightNotBeStandardized(string $color_name): void
    {
        $color = ItemColor::fromNotStandardizedName($color_name);
        self::assertEquals('inca-silver', $color->getName());
    }

    public function testDefaultColorCanBeBuilt(): void
    {
        $color = ItemColor::default();
        self::assertNotEmpty($color->getName());
    }

    public function testInvalidColorNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ItemColor::fromName('notvalidcolorname');
    }

    public function testInvalidNotStandardizedColorNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ItemColor::fromNotStandardizedName('notvalidcolorname');
    }
}
