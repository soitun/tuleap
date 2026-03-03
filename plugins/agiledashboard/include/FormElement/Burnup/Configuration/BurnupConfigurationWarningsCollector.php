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

use Tuleap\AgileDashboard\AgileDashboard\FormElement\BurnupChartFieldUsage;
use Tuleap\AgileDashboard\AgileDashboard\FormElement\RetrieveBurnupField;
use Tuleap\Tracker\FormElement\Admin\CollectFieldsConfigurationWarningsEvent;
use Tuleap\Tracker\FormElement\FetchChartConfigurationWarnings;

final readonly class BurnupConfigurationWarningsCollector
{
    public function __construct(
        private RetrieveBurnupField $burnup_field_retriever,
        private FetchChartConfigurationWarnings $warnings_fetcher,
    ) {
    }

    public function collectBurnupConfigurationWarnings(CollectFieldsConfigurationWarningsEvent $event): void
    {
        $burnup_field = $this->burnup_field_retriever->getField($event->tracker, $event->user);
        if ($burnup_field === null) {
            return;
        }

        $warnings = $this->warnings_fetcher->fetchWarnings($burnup_field, BurnupChartFieldUsage::build())->warnings;
        if (count($warnings) > 0) {
            $event->addWarnings($burnup_field, $warnings);
        }
    }
}
