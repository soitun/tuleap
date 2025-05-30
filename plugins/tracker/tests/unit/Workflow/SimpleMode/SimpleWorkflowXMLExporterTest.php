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

namespace Tuleap\Tracker\Workflow\SimpleMode;

use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use SimpleXMLElement;
use Transition;
use Transition_PostAction_CIBuild;
use Transition_PostAction_Field_Int;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Workflow\SimpleMode\State\State;
use Tuleap\Tracker\Workflow\SimpleMode\State\StateFactory;
use Tuleap\Tracker\Workflow\SimpleMode\State\TransitionExtractor;
use Workflow;
use Workflow_Transition_ConditionsCollection;

#[DisableReturnValueGenerationForTestDoubles]
final class SimpleWorkflowXMLExporterTest extends TestCase
{
    public function testItExportsTheSimpleWorkflowInXML(): void
    {
        $xml           = new SimpleXMLElement('<simple_workflow/>');
        $dao           = $this->createMock(SimpleWorkflowDao::class);
        $extractor     = new TransitionExtractor();
        $state_factory = $this->createMock(StateFactory::class);

        $exporter = new SimpleWorkflowXMLExporter($dao, $state_factory, $extractor);

        $workflow = $this->createMock(Workflow::class);

        $workflow->expects($this->once())->method('getFieldId')->willReturn(114);
        $workflow->expects($this->once())->method('isUsed')->willReturn(true);
        $workflow->expects($this->once())->method('getId')->willReturn('999');
        $dao->expects($this->once())->method('searchStatesForWorkflow')->with(999)->willReturn([
            ['to_id' => 200],
            ['to_id' => 201],
        ]);

        $transition_01 = $this->createMock(Transition::class);
        $transition_02 = $this->createMock(Transition::class);
        $transition_03 = $this->createMock(Transition::class);

        $transition_01->method('getIdFrom')->willReturn('');
        $transition_02->method('getIdFrom')->willReturn('');
        $transition_03->method('getIdFrom')->willReturn('410');

        $post_action_01 = $this->createMock(Transition_PostAction_CIBuild::class);
        $post_action_02 = $this->createMock(Transition_PostAction_Field_Int::class);

        $transition_01->method('getPostActions')->willReturn([]);
        $transition_03->method('getPostActions')->willReturn([
            $post_action_01,
            $post_action_02,
        ]);

        $post_action_01->expects($this->once())->method('exportToXML');
        $post_action_02->expects($this->once())->method('exportToXML');

        $conditions_collection_01 = $this->createMock(Workflow_Transition_ConditionsCollection::class);
        $conditions_collection_02 = $this->createMock(Workflow_Transition_ConditionsCollection::class);

        $transition_01->method('getConditions')->willReturn($conditions_collection_01);
        $transition_03->method('getConditions')->willReturn($conditions_collection_02);

        $conditions_collection_01->expects($this->once())->method('exportToXML');
        $conditions_collection_02->expects($this->once())->method('exportToXML');

        $state_factory->method('getStateFromValueId')
            ->willReturnCallback(static fn(Workflow $workflow, int $value_id) => match ($value_id) {
                200 => new State(200, [$transition_01]),
                201 => new State(201, [$transition_02, $transition_03])
            });

        $mapping = [
            'F114'   => '114',
            'values' => [
                'V114-0' => '200',
                'V114-1' => '201',
                'V114-2' => '410',
            ],
        ];

        $exporter->exportToXML($workflow, $xml, $mapping);

        self::assertEquals('F114', (string) $xml->field_id['REF']);
        self::assertEquals('1', (string) $xml->is_used);

        self::assertTrue(isset($xml->states));
        self::assertCount(2, $xml->states->state);

        $xml_state_01 = $xml->states->state[0];
        $xml_state_02 = $xml->states->state[1];

        self::assertSame((string) $xml_state_01->to_id['REF'], 'V114-0');
        self::assertSame((string) $xml_state_02->to_id['REF'], 'V114-1');

        $xml_state_01_transitions = $xml_state_01->transitions;
        $xml_state_02_transitions = $xml_state_02->transitions;

        self::assertCount(1, $xml_state_01_transitions->transition);
        self::assertCount(2, $xml_state_02_transitions->transition);

        self::assertSame((string) $xml_state_01_transitions->transition->from_id['REF'], 'null');
        self::assertSame((string) $xml_state_02_transitions->transition[0]->from_id['REF'], 'null');
        self::assertSame((string) $xml_state_02_transitions->transition[1]->from_id['REF'], 'V114-2');
    }

    public function testItSkipsMissingMappingValues(): void
    {
        $xml           = new SimpleXMLElement('<simple_workflow/>');
        $dao           = $this->createMock(SimpleWorkflowDao::class);
        $extractor     = new TransitionExtractor();
        $state_factory = $this->createMock(StateFactory::class);

        $exporter = new SimpleWorkflowXMLExporter($dao, $state_factory, $extractor);

        $workflow = $this->createMock(Workflow::class);
        $workflow->expects($this->once())->method('getFieldId')->willReturn(114);
        $workflow->expects($this->once())->method('isUsed')->willReturn(true);
        $workflow->expects($this->once())->method('getId')->willReturn('999');

        $dao->method('searchStatesForWorkflow')->with('999')->willReturn([
            ['to_id' => 200],
            ['to_id' => 201],
        ]);

        $state_factory->expects($this->never())->method('getStateFromValueId');

        $exporter->exportToXML($workflow, $xml, [
            'F114'   => '114',
            'values' => [],
        ]);

        self::assertEquals('F114', (string) $xml->field_id['REF']);
        self::assertEquals('1', (string) $xml->is_used);

        self::assertTrue(isset($xml->states));
        self::assertCount(0, $xml->states->state);
    }
}
