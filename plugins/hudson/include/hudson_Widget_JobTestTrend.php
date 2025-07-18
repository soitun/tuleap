<?php
/**
 * Copyright (c) Enalean, 2016 - Present. All rights reserved
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
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

use Tuleap\Dashboard\User\UserDashboardController;
use Tuleap\Http\HttpClientFactory;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Hudson\HudsonJobBuilder;

class hudson_Widget_JobTestTrend extends HudsonJobWidget //phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
{
    /**
     * @var HudsonJob
     */
    private $job;
    private ?HudsonTestResult $test_result;
    /**
     * @var HudsonJobBuilder
     */
    private $job_builder;

    /**
     * Constructor
     *
     * @param String           $owner_type The owner type
     * @param Int              $owner_id   The owner id
     * @param MinimalHudsonJobFactory $factory    The HudsonJob factory
     *
     * @return void
     */
    public function __construct($owner_type, $owner_id, MinimalHudsonJobFactory $factory, HudsonJobBuilder $job_builder)
    {
        $request = HTTPRequest::instance();
        if ($owner_type == UserDashboardController::LEGACY_DASHBOARD_TYPE) {
            $this->widget_id = 'plugin_hudson_my_jobtesttrend';
            $this->group_id  = $owner_id;
        } else {
            $this->widget_id = 'plugin_hudson_project_jobtesttrend';
            $this->group_id  = $request->get('group_id');
        }
        parent::__construct($this->widget_id, $factory);

        $this->setOwner($owner_id, $owner_type);
        $this->job_builder = $job_builder;
    }

    public function getTitle()
    {
        $title = '';
        if ($this->job) {
            $title .= sprintf(dgettext('tuleap-hudson', '%1$s Test Result Trend'), $this->job->getName());
        } else {
             $title .= sprintf(dgettext('tuleap-hudson', '%1$s Test Result Trend'), '');
        }
        return $title;
    }

    public function getDescription()
    {
        return dgettext('tuleap-hudson', 'Show the test result trend for the selected job. To display something, your job needs to have tests. The graph will show the number of tests (failed and successful) along  time. The number of tests is increasing while the number of build and commits are increasing too.');
    }

    public function loadContent($id)
    {
        $this->content_id = $id;
    }

    protected function initContent()
    {
        $job_id = $this->getJobIdFromWidgetConfiguration();
        if ($job_id) {
            $this->job_id = $job_id;

            $jobs = $this->getAvailableJobs();

            if (array_key_exists($this->job_id, $jobs)) {
                try {
                    $used_job          = $jobs[$this->job_id];
                    $this->job         = $this->job_builder->getHudsonJob($used_job);
                    $this->test_result = new HudsonTestResult(
                        $this->job->getUrl(),
                        HttpClientFactory::createClient(),
                        HTTPFactoryBuilder::requestFactory()
                    );
                } catch (Exception $e) {
                    $this->test_result = null;
                }
            } else {
                $this->job         = null;
                $this->test_result = null;
            }
        }
    }

    public function getContent(): string
    {
        $this->initContent();

        $purifier = Codendi_HTMLPurifier::instance();
        $html     = '';
        if ($this->job != null && $this->test_result != null) {
            $job = $this->job;

            $html .= '<div style="padding: 20px;">';
            $html .= '<a href="/plugins/hudson/?action=view_test_trend&group_id=' . urlencode($this->group_id) . '&job_id=' . urlencode($this->job_id) . '">';
            $html .= '<img src="' . $purifier->purify($job->getUrl()) . '/test/trend?width=320&height=240" alt="' . $purifier->purify(sprintf(dgettext('tuleap-hudson', '%1$s Test Result Trend'), $this->job->getName())) . '" title="' . $purifier->purify(sprintf(dgettext('tuleap-hudson', '%1$s Test Result Trend'), $this->job->getName())) . '" />';
            $html .= '</a>';
            $html .= '</div>';
        } else {
            if ($this->job != null) {
                $html .= $purifier->purify(dgettext('tuleap-hudson', 'No test found for this job.'));
            } else {
                $html .= $purifier->purify(dgettext('tuleap-hudson', 'Job not found.'));
            }
        }

        return $html;
    }
}
