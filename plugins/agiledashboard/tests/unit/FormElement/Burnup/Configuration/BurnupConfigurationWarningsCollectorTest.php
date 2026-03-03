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

namespace Tuleap\AgileDashboard\FormElement\Burnup\Configuration;

use Tuleap\AgileDashboard\FormElement\Burnup;
use Tuleap\AgileDashboard\Stub\FormElement\RetrieveBurnupFieldStub;
use Tuleap\AgileDashboard\Test\Builders\BurnupTestBuilder;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\FormElement\Admin\CollectFieldsConfigurationWarningsEvent;
use Tuleap\Tracker\FormElement\ChartConfigurationWarning;
use Tuleap\Tracker\FormElement\ChartConfigurationWarningCollection;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;
use Tuleap\Tracker\Test\Stub\FormElement\FetchChartConfigurationWarningsStub;
use Tuleap\Tracker\Tracker;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class BurnupConfigurationWarningsCollectorTest extends TestCase
{
    private Burnup $burnup;
    private Tracker $tracker;
    private CollectFieldsConfigurationWarningsEvent $event;

    #[\Override]
    protected function setUp(): void
    {
        $this->burnup  = BurnupTestBuilder::aBurnupField(102)->build();
        $this->tracker = TrackerTestBuilder::aTracker()->withId(12)->build();
        $this->event   = new CollectFieldsConfigurationWarningsEvent(
            [],
            $this->tracker,
            UserTestBuilder::anActiveUser()->build(),
        );
    }

    public function testItCollectsWarnings(): void
    {
        $warnings_collection = new ChartConfigurationWarningCollection();
        $warnings_collection->addWarning(ChartConfigurationWarning::fromMessage('Oops I did it again...'));

        new BurnupConfigurationWarningsCollector(
            RetrieveBurnupFieldStub::build()->withBurnupFieldForTracker($this->tracker, $this->burnup),
            FetchChartConfigurationWarningsStub::withWarnings($warnings_collection),
        )->collectBurnupConfigurationWarnings($this->event);

        self::assertCount(1, $this->event->warnings);
        self::assertArrayHasKey($this->burnup->getId(), $this->event->warnings);
        self::assertSame($warnings_collection->warnings, $this->event->warnings[$this->burnup->getId()]);
    }

    public function testWhenThereIsNoBurnupField(): void
    {
        new BurnupConfigurationWarningsCollector(
            RetrieveBurnupFieldStub::build(),
            FetchChartConfigurationWarningsStub::withoutWarnings()
        )->collectBurnupConfigurationWarnings($this->event);

        self::assertEmpty($this->event->warnings);
    }

    public function testWhenThereIsNoWarning(): void
    {
        new BurnupConfigurationWarningsCollector(
            RetrieveBurnupFieldStub::build()->withBurnupFieldForTracker($this->tracker, $this->burnup),
            FetchChartConfigurationWarningsStub::withoutWarnings()
        )->collectBurnupConfigurationWarnings($this->event);

        self::assertEmpty($this->event->warnings);
    }
}
