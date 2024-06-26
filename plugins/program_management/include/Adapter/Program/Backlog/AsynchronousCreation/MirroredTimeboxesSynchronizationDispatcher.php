<?php
/**
 * Copyright (c) Enalean, 2022 - present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\AsynchronousCreation;

use Tuleap\ProgramManagement\Domain\Events\TeamSynchronizationEvent;
use Tuleap\ProgramManagement\Domain\Program\Backlog\AsynchronousCreation\CommandTeamSynchronization;
use Tuleap\ProgramManagement\Domain\Program\Backlog\AsynchronousCreation\DispatchMirroredTimeboxesSynchronization;
use Tuleap\Queue\QueueFactory;
use Tuleap\Queue\Worker;

final readonly class MirroredTimeboxesSynchronizationDispatcher implements DispatchMirroredTimeboxesSynchronization
{
    public function __construct(
        private QueueFactory $queue_factory,
    ) {
    }

    public function dispatchSynchronizationCommand(CommandTeamSynchronization $team_synchronization_command): void
    {
        $queue = $this->queue_factory->getPersistentQueue(Worker::EVENT_QUEUE_NAME);
        $queue->pushSinglePersistentMessage(TeamSynchronizationEvent::TOPIC, [
            'program_id' => $team_synchronization_command->getProgramId(),
            'team_id' => $team_synchronization_command->getTeamId(),
            'user_id' => $team_synchronization_command->getUserId(),
        ]);
    }
}
