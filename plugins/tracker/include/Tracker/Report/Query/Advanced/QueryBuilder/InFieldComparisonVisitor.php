<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

namespace Tuleap\Tracker\Report\Query\Advanced\QueryBuilder;

use BaseLanguageFactory;
use Tracker_FormElement_Field;
use Tuleap\Tracker\FormElement\Field\ArtifactId\ArtifactIdField;
use Tracker_FormElement_Field_Burndown;
use Tracker_FormElement_Field_Checkbox;
use Tuleap\Tracker\FormElement\Field\Computed\ComputedField;
use Tracker_FormElement_Field_CrossReferences;
use Tracker_FormElement_Field_Date;
use Tracker_FormElement_Field_File;
use Tracker_FormElement_Field_LastModifiedBy;
use Tracker_FormElement_Field_LastUpdateDate;
use Tracker_FormElement_Field_List;
use Tracker_FormElement_Field_MultiSelectbox;
use Tracker_FormElement_Field_OpenList;
use Tracker_FormElement_Field_PermissionsOnArtifact;
use Tuleap\Tracker\FormElement\Field\PerTrackerArtifactId\PerTrackerArtifactIdField;
use Tracker_FormElement_Field_Radiobutton;
use Tracker_FormElement_Field_Selectbox;
use Tracker_FormElement_Field_SubmittedBy;
use Tracker_FormElement_Field_SubmittedOn;
use Tracker_FormElement_FieldVisitor;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\ArtifactLinkField;
use Tuleap\Tracker\FormElement\Field\Float\FloatField;
use Tuleap\Tracker\FormElement\Field\Integer\IntegerField;
use Tuleap\Tracker\FormElement\Field\Priority\PriorityField;
use Tuleap\Tracker\FormElement\Field\String\StringField;
use Tuleap\Tracker\FormElement\Field\Text\TextField;
use Tuleap\Tracker\FormElement\TrackerFormElementExternalField;
use Tuleap\Tracker\Report\Query\Advanced\CollectionOfListValuesExtractor;
use Tuleap\Tracker\Report\Query\Advanced\FieldFromWhereBuilder;
use Tuleap\Tracker\Report\Query\Advanced\ListFieldBindValueNormalizer;
use Tuleap\Tracker\Report\Query\Advanced\UgroupLabelConverter;
use UserManager;

final class InFieldComparisonVisitor implements
    Tracker_FormElement_FieldVisitor,
    FieldComparisonVisitor
{
    /** @return FieldFromWhereBuilder */
    public function getFromWhereBuilder(Tracker_FormElement_Field $field)
    {
        return $field->accept($this);
    }

    public function visitArtifactLink(ArtifactLinkField $field)
    {
        return null;
    }

    public function visitDate(Tracker_FormElement_Field_Date $field)
    {
        return null;
    }

    public function visitFile(Tracker_FormElement_Field_File $field)
    {
        return null;
    }

    public function visitFloat(FloatField $field)
    {
        return null;
    }

    public function visitInteger(IntegerField $field)
    {
        return null;
    }

    public function visitOpenList(Tracker_FormElement_Field_OpenList $field)
    {
        return null;
    }

    public function visitPermissionsOnArtifact(Tracker_FormElement_Field_PermissionsOnArtifact $field)
    {
        return null;
    }

    public function visitString(StringField $field)
    {
        return null;
    }

    public function visitText(TextField $field)
    {
        return null;
    }

    public function visitRadiobutton(Tracker_FormElement_Field_Radiobutton $field)
    {
        return $this->visitList($field);
    }

    public function visitCheckbox(Tracker_FormElement_Field_Checkbox $field)
    {
        return $this->visitList($field);
    }

    public function visitMultiSelectbox(Tracker_FormElement_Field_MultiSelectbox $field)
    {
        return $this->visitList($field);
    }

    public function visitSelectbox(Tracker_FormElement_Field_Selectbox $field)
    {
        return $this->visitList($field);
    }

    private function visitList(Tracker_FormElement_Field_List $field)
    {
        $static_bind_builder  = new InComparison\ForListBindStatic(
            new CollectionOfListValuesExtractor(),
            new FromWhereComparisonListFieldBuilder()
        );
        $users_bind_builder   = new InComparison\ForListBindUsers(
            new CollectionOfListValuesExtractor(),
            new FromWhereComparisonListFieldBuilder()
        );
        $ugroups_bind_builder = new InComparison\ForListBindUGroups(
            new CollectionOfListValuesExtractor(),
            new FromWhereComparisonListFieldBindUgroupsBuilder(),
            new UgroupLabelConverter(
                new ListFieldBindValueNormalizer(),
                new BaseLanguageFactory()
            )
        );

        $bind_builder = new ListFieldBindVisitor(
            $static_bind_builder,
            $users_bind_builder,
            $ugroups_bind_builder
        );

        return $bind_builder->getFromWhereBuilder($field);
    }

    public function visitSubmittedBy(Tracker_FormElement_Field_SubmittedBy $field)
    {
        return new ListReadOnlyFieldFromWhereBuilder(
            new CollectionOfListValuesExtractor(),
            new FromWhereComparisonFieldReadOnlyBuilder(),
            new InComparison\ForSubmittedBy(
                UserManager::instance()
            )
        );
    }

    public function visitLastModifiedBy(Tracker_FormElement_Field_LastModifiedBy $field)
    {
        return new ListReadOnlyFieldFromWhereBuilder(
            new CollectionOfListValuesExtractor(),
            new FromWhereComparisonFieldReadOnlyBuilder(),
            new InComparison\ForLastUpdatedBy(
                UserManager::instance()
            )
        );
    }

    public function visitArtifactId(ArtifactIdField $field)
    {
        return null;
    }

    public function visitPerTrackerArtifactId(PerTrackerArtifactIdField $field)
    {
        return null;
    }

    public function visitCrossReferences(Tracker_FormElement_Field_CrossReferences $field)
    {
        return null;
    }

    public function visitBurndown(Tracker_FormElement_Field_Burndown $field)
    {
        return null;
    }

    public function visitLastUpdateDate(Tracker_FormElement_Field_LastUpdateDate $field)
    {
        return null;
    }

    public function visitSubmittedOn(Tracker_FormElement_Field_SubmittedOn $field)
    {
        return null;
    }

    public function visitComputed(ComputedField $field)
    {
        return null;
    }

    public function visitExternalField(TrackerFormElementExternalField $element)
    {
        return null;
    }

    public function visitPriority(PriorityField $field)
    {
        return null;
    }
}
