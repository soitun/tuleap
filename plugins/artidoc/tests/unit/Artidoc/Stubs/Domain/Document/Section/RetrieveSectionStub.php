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

namespace Tuleap\Artidoc\Stubs\Domain\Document\Section;

use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifier;
use Tuleap\Artidoc\Domain\Document\Section\RetrievedSection;
use Tuleap\Artidoc\Domain\Document\Section\RetrieveSection;
use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Fault;
use Tuleap\NeverThrow\Ok;
use Tuleap\NeverThrow\Result;

/**
 * @psalm-immutable
 */
final readonly class RetrieveSectionStub implements RetrieveSection
{
    /**
     * @param Ok<RetrievedSection>|Err<Fault> $read
     * @param Ok<RetrievedSection>|Err<Fault> $write
     */
    private function __construct(private Ok|Err $read, private Ok|Err $write)
    {
    }

    public static function withoutMatchingSection(): self
    {
        $err = Result::err(Fault::fromMessage('No matching section found'));

        return new self($err, $err);
    }

    public static function witMatchingSectionUserCanRead(RetrievedSection $section): self
    {
        $err = Result::err(Fault::fromMessage('No matching section found'));

        return new self(Result::ok($section), $err);
    }

    public static function witMatchingSectionUserCanWrite(RetrievedSection $section): self
    {
        $ok = Result::ok($section);

        return new self($ok, $ok);
    }

    #[\Override]
    public function retrieveSectionUserCanRead(SectionIdentifier $id): Ok|Err
    {
        return $this->read;
    }

    #[\Override]
    public function retrieveSectionUserCanWrite(SectionIdentifier $id): Ok|Err
    {
        return $this->write;
    }
}
