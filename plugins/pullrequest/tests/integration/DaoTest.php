<?php
/**
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

namespace Tuleap\PullRequest;

use Tuleap\DB\DBFactory;
use Tuleap\PullRequest\Criterion\SearchCriteria;
use Tuleap\PullRequest\Criterion\StatusCriterion;
use Tuleap\PullRequest\Tests\Builders\PullRequestTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

final class DaoTest extends TestCase
{
    private const REPOSITORY_ID = 5;
    private const LIMIT         = 10;
    private const OFFSET        = 0;

    private Dao $dao;

    private int $open_pull_request_id;
    private int $merged_pull_request_id;
    private int $abandoned_pull_request_id;

    protected function setUp(): void
    {
        $this->dao = new Dao();

        $this->open_pull_request_id      = $this->insertOpenPullRequest();
        $this->merged_pull_request_id    = $this->insertMergedPullRequest();
        $this->abandoned_pull_request_id = $this->insertAbandonedPullRequest();
    }

    protected function tearDown(): void
    {
        $db = DBFactory::getMainTuleapDBConnection()->getDB();
        $db->run('DELETE FROM plugin_pullrequest_review');
    }

    public function testItRetrievesOnlyOpenPullRequests(): void
    {
        $pull_requests = $this->dao->getPaginatedPullRequests(
            self::REPOSITORY_ID,
            new SearchCriteria(StatusCriterion::OPEN),
            self::LIMIT,
            self::OFFSET,
        );

        self::assertSame(array_column($pull_requests, "id"), [$this->open_pull_request_id]);
    }

    public function testItRetrievesOnlyClosedPullRequests(): void
    {
        $pull_requests = $this->dao->getPaginatedPullRequests(
            self::REPOSITORY_ID,
            new SearchCriteria(StatusCriterion::CLOSED),
            self::LIMIT,
            self::OFFSET,
        );

        self::assertSame(array_column($pull_requests, "id"), [$this->merged_pull_request_id, $this->abandoned_pull_request_id]);
    }

    public function testItRetrievesAllPullRequests(): void
    {
        $pull_requests = $this->dao->getPaginatedPullRequests(
            self::REPOSITORY_ID,
            new SearchCriteria(),
            self::LIMIT,
            self::OFFSET,
        );

        self::assertSame(array_column($pull_requests, "id"), [
            $this->open_pull_request_id,
            $this->merged_pull_request_id,
            $this->abandoned_pull_request_id,
        ]);
    }

    private function insertOpenPullRequest(): int
    {
        return $this->insertPullRequest(
            PullRequestTestBuilder::aPullRequestInReview()->withRepositoryId(self::REPOSITORY_ID)->build(),
        );
    }

    private function insertMergedPullRequest(): int
    {
        $pull_request_id = $this->insertPullRequest(
            PullRequestTestBuilder::aMergedPullRequest()->withRepositoryId(self::REPOSITORY_ID)->build(),
        );

        $this->dao->markAsMerged($pull_request_id);

        return $pull_request_id;
    }

    private function insertAbandonedPullRequest(): int
    {
        $pull_request_id = $this->insertPullRequest(
            PullRequestTestBuilder::anAbandonedPullRequest()->withRepositoryId(self::REPOSITORY_ID)->build()
        );

        $this->dao->markAsAbandoned($pull_request_id);

        return $pull_request_id;
    }

    private function insertPullRequest(PullRequest $pull_request): int
    {
        return (int) $this->dao->create(
            $pull_request->getRepositoryId(),
            $pull_request->getTitle(),
            $pull_request->getDescription(),
            $pull_request->getUserId(),
            $pull_request->getCreationDate(),
            $pull_request->getBranchSrc(),
            $pull_request->getSha1Src(),
            $pull_request->getRepoDestId(),
            $pull_request->getBranchDest(),
            $pull_request->getSha1Dest(),
            $pull_request->getMergeStatus(),
            $pull_request->getDescriptionFormat(),
        );
    }
}