<?php
/**
 * Copyright (c) Enalean, 2023-Present. All Rights Reserved.
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

namespace Tuleap\PullRequest\REST\v1\InlineComment;

use Luracast\Restler\RestException;
use Tuleap\Git\RetrieveGitRepository;
use Tuleap\Markdown\ContentInterpretor;
use Tuleap\PullRequest\InlineComment\InlineCommentCreator;
use Tuleap\PullRequest\InlineComment\NewInlineComment;
use Tuleap\PullRequest\PullRequest;
use Tuleap\PullRequest\PullRequest\Timeline\TimelineComment;
use Tuleap\PullRequest\REST\v1\PullRequestInlineCommentPOSTRepresentation;
use Tuleap\PullRequest\REST\v1\PullRequestInlineCommentRepresentation;
use Tuleap\User\REST\MinimalUserRepresentation;

final class POSTHandler
{
    public function __construct(
        private readonly RetrieveGitRepository $repository_retriever,
        private readonly InlineCommentCreator $inline_comment_creator,
        private readonly \Codendi_HTMLPurifier $purifier,
        private readonly ContentInterpretor $content_interpreter,
    ) {
    }

    /**
     * @throws RestException
     */
    public function handle(
        PullRequestInlineCommentPOSTRepresentation $comment_data,
        \PFUser $user,
        \DateTimeImmutable $post_date,
        PullRequest $pull_request,
    ): PullRequestInlineCommentRepresentation {
        $git_repository_source = $this->getRepository($pull_request->getRepositoryId());

        $format = $comment_data->format;
        if (! $format) {
            $format = TimelineComment::FORMAT_MARKDOWN;
        }

        $new_comment             = new NewInlineComment(
            $pull_request,
            (int) $git_repository_source->getProjectId(),
            $comment_data->file_path,
            $comment_data->unidiff_offset,
            $comment_data->content,
            $format,
            $comment_data->position,
            $comment_data->parent_id ?? 0,
            $user,
            $post_date
        );
        $inserted_inline_comment = $this->inline_comment_creator->insert($new_comment);

        $user_representation = MinimalUserRepresentation::build($user);
        return PullRequestInlineCommentRepresentation::build(
            $this->purifier,
            $this->content_interpreter,
            $new_comment->unidiff_offset,
            $user_representation,
            $new_comment->post_date->getTimestamp(),
            $new_comment->content,
            $new_comment->project_id,
            $new_comment->position,
            $new_comment->parent_id,
            $inserted_inline_comment->id,
            $new_comment->file_path,
            $inserted_inline_comment->color,
            $new_comment->format
        );
    }

    /**
     * @throws RestException
     */
    private function getRepository(int $repository_id): \GitRepository
    {
        $repository = $this->repository_retriever->getRepositoryById($repository_id);

        if (! $repository) {
            throw new RestException(404, 'Git repository not found');
        }

        return $repository;
    }
}