<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Workflow\Transition\Condition\CommentNotEmpty;

use PHPUnit\Framework\MockObject\MockObject;
use SimpleXMLElement;
use Tuleap\Tracker\Test\Builders\Fields\List\ListStaticValueBuilder;
use Workflow_Transition_Condition_CommentNotEmpty;
use Workflow_Transition_Condition_CommentNotEmpty_Dao;
use Workflow_Transition_Condition_CommentNotEmpty_Factory;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class CommentNotEmptyFactoryTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private Workflow_Transition_Condition_CommentNotEmpty_Factory $factory;

    private Workflow_Transition_Condition_CommentNotEmpty_Dao&MockObject $dao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dao     = $this->createMock(Workflow_Transition_Condition_CommentNotEmpty_Dao::class);
        $this->factory = new Workflow_Transition_Condition_CommentNotEmpty_Factory($this->dao);
    }

    public function testItReturnsTheConditionForAConditionInXML(): void
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>
            <condition type="commentnotempty" is_comment_required="1"/>'
        );

        $value_01 = ListStaticValueBuilder::aStaticValue('abc')->build();
        $value_02 = ListStaticValueBuilder::aStaticValue('def')->build();

        $transition_id = 1;
        $workflow_id   = 1;

        $transition = new \Transition(
            $transition_id,
            $workflow_id,
            $value_01,
            $value_02
        );

        $condition = $this->factory->getInstanceFromXML($xml, $transition);

        $this->assertInstanceOf(Workflow_Transition_Condition_CommentNotEmpty::class, $condition);
        $this->assertTrue((bool) $condition->isCommentRequired());
    }

    public function testItReturnsNullForAConditionFromNewInXML(): void
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>
            <condition type="commentnotempty" is_comment_required="1"/>'
        );

        $value_01 = ListStaticValueBuilder::aStaticValue('abc')->build();

        $from_new_artifact = null;
        $transition_id     = 1;
        $workflow_id       = 1;

        $transition = new \Transition(
            $transition_id,
            $workflow_id,
            $from_new_artifact,
            $value_01
        );

        $condition = $this->factory->getInstanceFromXML($xml, $transition);

        $this->assertNull($condition);
    }

    public function testItReturnsTheConditionForACondition(): void
    {
        $value_01 = ListStaticValueBuilder::aStaticValue('abc')->build();
        $value_02 = ListStaticValueBuilder::aStaticValue('def')->build();

        $transition_id = 1;
        $workflow_id   = 1;

        $transition = new \Transition(
            $transition_id,
            $workflow_id,
            $value_01,
            $value_02
        );

        $this->dao->method('searchByTransitionId')
            ->with(1)
            ->willReturn([
                'transition_id' => 1,
                'is_comment_required' => 1,
            ]);

        $condition = $this->factory->getCommentNotEmpty($transition);

        $this->assertInstanceOf(Workflow_Transition_Condition_CommentNotEmpty::class, $condition);
    }

    public function testItReturnsNullForAConditionFromNew(): void
    {
        $value_01 = ListStaticValueBuilder::aStaticValue('abc')->build();

        $from_new_artifact = null;
        $transition_id     = 1;
        $workflow_id       = 1;

        $transition = new \Transition(
            $transition_id,
            $workflow_id,
            $from_new_artifact,
            $value_01
        );

        $condition = $this->factory->getCommentNotEmpty($transition);

        $this->assertNull($condition);
    }
}
