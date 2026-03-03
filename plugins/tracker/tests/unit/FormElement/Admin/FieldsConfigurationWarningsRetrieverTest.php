<?php
/**
 * Copyright (c) Enalean, 2026 - present. All Rights Reserved.
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

namespace Tuleap\Tracker\FormElement\Admin;

use PFUser;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Test\Stubs\EventDispatcherStub;
use Tuleap\Tracker\FormElement\ChartConfigurationWarning;
use Tuleap\Tracker\FormElement\ChartConfigurationWarningCollection;
use Tuleap\Tracker\FormElement\Field\Burndown\BurndownField;
use Tuleap\Tracker\Test\Builders\Fields\BurndownFieldBuilder;
use Tuleap\Tracker\Test\Builders\Fields\IntegerFieldBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;
use Tuleap\Tracker\Test\Stub\FormElement\FetchChartConfigurationWarningsStub;
use Tuleap\Tracker\Test\Stub\FormElement\Field\RetrieveBurndownFieldStub;
use Tuleap\Tracker\Tracker;

#[DisableReturnValueGenerationForTestDoubles]
final class FieldsConfigurationWarningsRetrieverTest extends TestCase
{
    private Tracker $tracker;
    private BurndownField $burndown_field;
    private PFUser $user;

    #[\Override]
    protected function setUp(): void
    {
        $this->tracker        = TrackerTestBuilder::aTracker()->build();
        $this->user           = UserTestBuilder::anActiveUser()->build();
        $this->burndown_field = BurndownFieldBuilder::aBurndownField(1002)->inTracker($this->tracker)->build();
    }

    public function testItReturnsEmptyArrayWhenNoBurndownFieldIsFoundInTracker(): void
    {
        $retriever = new FieldsConfigurationWarningsRetriever(
            RetrieveBurndownFieldStub::withoutField(),
            FetchChartConfigurationWarningsStub::withoutWarnings(),
            EventDispatcherStub::withIdentityCallback(),
        );

        self::assertCount(0, $retriever->retrieveWarnings($this->tracker, $this->user));
    }

    public function testItReturnsEmptyArrayWhenNoWarnings(): void
    {
        $retriever = new FieldsConfigurationWarningsRetriever(
            RetrieveBurndownFieldStub::withField($this->burndown_field),
            FetchChartConfigurationWarningsStub::withoutWarnings(),
            EventDispatcherStub::withIdentityCallback(),
        );

        self::assertCount(0, $retriever->retrieveWarnings($this->tracker, $this->user));
    }

    public function testItReturnsWarningsIndexedByFieldId(): void
    {
        $an_external_field       = IntegerFieldBuilder::anIntField(1000)->build();
        $external_field_warnings = [
            ChartConfigurationWarning::fromMessage('Something is not configured properly from an external point of view.'),
        ];

        $warnings_collection = new ChartConfigurationWarningCollection();
        $warnings_collection->addWarning(ChartConfigurationWarning::fromMessage('Something is not configured as expected.'));

        $retriever = new FieldsConfigurationWarningsRetriever(
            RetrieveBurndownFieldStub::withField($this->burndown_field),
            FetchChartConfigurationWarningsStub::withWarnings($warnings_collection),
            EventDispatcherStub::withCallback(static function (object $event) use ($an_external_field, $external_field_warnings): object {
                if ($event instanceof CollectFieldsConfigurationWarningsEvent) {
                    $event->addWarnings($an_external_field, $external_field_warnings);
                }
                return $event;
            }),
        );

        $indexed_warnings = $retriever->retrieveWarnings($this->tracker, $this->user);

        self::assertCount(2, $indexed_warnings);
        self::assertArrayHasKey($this->burndown_field->getId(), $indexed_warnings);
        self::assertSame($warnings_collection->warnings, $indexed_warnings[$this->burndown_field->getId()]);

        self::assertArrayHasKey($an_external_field->getId(), $indexed_warnings);
        self::assertSame($external_field_warnings, $indexed_warnings[$an_external_field->getId()]);
    }
}
