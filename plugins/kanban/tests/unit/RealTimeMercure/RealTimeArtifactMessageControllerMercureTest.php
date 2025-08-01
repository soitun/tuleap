<?php
/**
 * Copyright (c) Enalean, 2022 - Present. All Rights Reserved.
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

namespace Tuleap\Kanban\RealTimeMercure;

use PHPUnit\Framework\MockObject\MockObject;
use Tuleap\Kanban\KanbanFactory;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;
use Tuleap\Tracker\Test\Stub\Semantic\Status\RetrieveSemanticStatusStub;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class RealTimeArtifactMessageControllerMercureTest extends TestCase
{
    private KanbanFactory&MockObject $kanban_factory;
    private KanbanArtifactMessageSenderMercure&MockObject $kanban_artifact_message_sender_mercure;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->kanban_factory                         = $this->createMock(KanbanFactory::class);
        $this->kanban_artifact_message_sender_mercure = $this->createMock(KanbanArtifactMessageSenderMercure::class);
    }

    public function testNoError(): void
    {
        $this->kanban_factory->method('getKanbanIdByTrackerId')->willReturn(1);
        $artifact                    = ArtifactTestBuilder::anArtifact(1)->build();
        $event_name                  = RealTimeArtifactMessageControllerMercure::EVENT_NAME_ARTIFACT_CREATED;
        $realtime_controller_mercure = new RealTimeArtifactMessageControllerMercure($this->kanban_factory, $this->kanban_artifact_message_sender_mercure, RetrieveSemanticStatusStub::build());
        $this->kanban_artifact_message_sender_mercure->expects($this->once())->method('sendMessageArtifactCreated');
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactReordered');
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactMoved');
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactUpdated');
        $realtime_controller_mercure->sendMessageForKanban($artifact, $event_name);
    }

    public function testNoKanban(): void
    {
        $this->kanban_factory->method('getKanbanIdByTrackerId')->willReturn(null);
        $artifact                    = ArtifactTestBuilder::anArtifact(1)->build();
        $event_name                  = RealTimeArtifactMessageControllerMercure::EVENT_NAME_ARTIFACT_CREATED;
        $realtime_controller_mercure = new RealTimeArtifactMessageControllerMercure($this->kanban_factory, $this->kanban_artifact_message_sender_mercure, RetrieveSemanticStatusStub::build());
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactCreated');
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactReordered');
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactMoved');
        $this->kanban_artifact_message_sender_mercure->expects($this->never())->method('sendMessageArtifactCreated');
        $realtime_controller_mercure->sendMessageForKanban($artifact, $event_name);
    }
}
