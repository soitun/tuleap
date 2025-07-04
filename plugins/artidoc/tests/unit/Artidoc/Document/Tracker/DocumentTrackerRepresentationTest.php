<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

namespace Tuleap\Artidoc\Document\Tracker;

use Tracker_FormElement_Field_File;
use Tracker_FormElement_Field_String;
use Tracker_FormElement_Field_Text;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Semantic\Title\TrackerSemanticTitle;
use Tuleap\Tracker\Test\Stub\RetrieveSemanticDescriptionFieldStub;
use Tuleap\Tracker\Test\Builders\Fields\TextFieldBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;
use Tuleap\Tracker\Test\Stub\Artifact\GetFileUploadDataStub;
use Tuleap\Tracker\Tracker;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class DocumentTrackerRepresentationTest extends TestCase
{
    private Tracker $tracker;

    protected function setUp(): void
    {
        $this->tracker = TrackerTestBuilder::aTracker()->withId(101)->withName('Bugs')->withProject(ProjectTestBuilder::aProject()->withId(101)->build())->build();
    }

    protected function tearDown(): void
    {
        TrackerSemanticTitle::clearInstances();
    }

    public function testIdAndLabelAreExposed(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, null),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withNoField(),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertSame(101, $representation->id);
        self::assertSame('Bugs', $representation->label);
    }

    public function testItExposesNullForTitleIfNoSemanticTitle(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, null),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withNoField(),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->title);
    }

    public function testItExposesNullForTitleFieldIfNotAStringField(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, TextFieldBuilder::aTextField(1004)->build()),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withNoField(),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->title);
    }

    public function testItExposesNullForTitleFieldIfNotSubmittable(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, false)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withNoField(),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->title);
    }

    public function testItExposesTheTitleField(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, true)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withNoField(),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNotNull($representation->title);
        self::assertSame(1004, $representation->title->field_id);
        self::assertSame('A String Field', $representation->title->label);
    }

    public function testItExposesNullForDescriptionIfNoSemanticDescription(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, null),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withNoField(),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->description);
    }

    public function testItExposesNullForDescriptionFieldIfNotSubmittable(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, true)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withTextField($this->getTextField(1005, false)),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->description);
    }

    public function testItExposesTheDescriptionField(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, true)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withTextField($this->getTextField(1005, true)),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNotNull($representation->description);
        self::assertSame(1005, $representation->description->field_id);
        self::assertSame('A Text Field', $representation->description->label);
    }

    public function testItExposesNullForFileFieldIfNoAttachmentField(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, true)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withoutField(),
            RetrieveSemanticDescriptionFieldStub::withTextField($this->getTextField(1005, true)),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->file);
    }

    public function testItExposesNullForFileFieldIfNotSubmittable(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, true)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withField($this->getFileField(1006, false)),
            RetrieveSemanticDescriptionFieldStub::withTextField($this->getTextField(1005, true)),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNull($representation->file);
    }

    public function testItExposesTheFileUploadField(): void
    {
        TrackerSemanticTitle::setInstance(
            new TrackerSemanticTitle($this->tracker, $this->getStringField(1004, true)),
            $this->tracker,
        );

        $representation = DocumentTrackerRepresentation::fromTracker(
            GetFileUploadDataStub::withField($this->getFileField(1006, true)),
            RetrieveSemanticDescriptionFieldStub::withTextField($this->getTextField(1005, true)),
            $this->tracker,
            UserTestBuilder::buildWithDefaults(),
        );

        self::assertNotNull($representation->file);
        self::assertSame(1006, $representation->file->field_id);
        self::assertSame('A File Field', $representation->file->label);
    }

    private function getFileField(int $id, bool $submittable): Tracker_FormElement_Field_File
    {
        $field = $this->createMock(Tracker_FormElement_Field_File::class);
        $field->method('getId')->willReturn($id);
        $field->method('getLabel')->willReturn('A File Field');
        $field->method('userCanSubmit')->willReturn($submittable);

        return $field;
    }

    private function getStringField(int $id, bool $submittable): Tracker_FormElement_Field_String
    {
        $field = $this->createMock(Tracker_FormElement_Field_String::class);
        $field->method('getId')->willReturn($id);
        $field->method('getLabel')->willReturn('A String Field');
        $field->method('userCanSubmit')->willReturn($submittable);
        $field->method('getDefaultRESTValue')->willReturn('');

        return $field;
    }

    private function getTextField(int $id, bool $submittable): Tracker_FormElement_Field_Text
    {
        $field = $this->createMock(Tracker_FormElement_Field_Text::class);
        $field->method('getId')->willReturn($id);
        $field->method('getLabel')->willReturn('A Text Field');
        $field->method('userCanSubmit')->willReturn($submittable);
        $field->method('getDefaultRESTValue')->willReturn(['format' => 'html', 'content' => '']);

        return $field;
    }
}
