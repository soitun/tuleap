<?php
/*
 * Copyright (c) Enalean, 2015 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Semantic\Description;

use Codendi_HTMLPurifier;
use Codendi_Request;
use Feedback;
use PFUser;
use SimpleXMLElement;
use TemplateRendererFactory;
use Tracker_FormElement_Field;
use Tracker_FormElementFactory;
use TrackerManager;
use Tuleap\Tracker\FormElement\Field\Text\TextField;
use Tuleap\Tracker\Semantic\TrackerSemantic;
use Tuleap\Tracker\Semantic\TrackerSemanticManager;
use Tuleap\Tracker\Tracker;

class TrackerSemanticDescription extends TrackerSemantic
{
    public const NAME = 'description';

    /**
     * @var TextField
     */
    protected $text_field;

    /**
     * Constructor
     *
     * @param Tracker $tracker The tracker
     * @param TextField $text_field The field
     */
    public function __construct(Tracker $tracker, ?TextField $text_field = null)
    {
        parent::__construct($tracker);
        $this->text_field = $text_field;
    }

    /**
     * The short name of the semantic: tooltip, description, status, owner, ...
     *
     * @return string
     */
    public function getShortName()
    {
         return self::NAME;
    }

    /**
     * The label of the semantic: Tooltip, ...
     *
     * @return string
     */
    public function getLabel()
    {
        return dgettext('tuleap-tracker', 'Description');
    }

    /**
     * The description of the semantics. Used for breadcrumbs
     *
     * @return string
     */
    public function getDescription()
    {
        return dgettext('tuleap-tracker', 'Define the description of an artifact');
    }

    /**
     * The Id of the (text) field used for description semantic
     *
     * @return int The Id of the (text) field used for description semantic, or 0 if no field
     */
    public function getFieldId()
    {
        if ($this->text_field) {
            return $this->text_field->getId();
        } else {
            return 0;
        }
    }

    /**
     * The (text) field used for description semantic
     *
     * @return TextField The (text) field used for description semantic, or null if no field
     */
    public function getField()
    {
        return $this->text_field;
    }

    public function fetchForSemanticsHomepage(): string
    {
        $warning = '';
        $field   = Tracker_FormElementFactory::instance()->getUsedFormElementById($this->getFieldId());

        if ($field) {
            $purifier = Codendi_HTMLPurifier::instance();
            $content  = '<p>' . sprintf(
                dgettext('tuleap-tracker', 'The artifacts of this tracker will be described by the field %s.'),
                '<strong>' . $purifier->purify($field->getLabel()) . '</strong>'
            ) . '</p>';

            if (Tracker_FormElementFactory::instance()->getUsedFieldByIdAndType($this->tracker, $field->getId(), ['string', 'ref'])) {
                $warning = '<p class="alert alert-warning">' .
                    dgettext('tuleap-tracker', 'String fields are no longer supported for description semantic. Please update.') .
                    '</p>';
            }
        } else {
            $content = '<p>' . sprintf(
                dgettext(
                    'tuleap-tracker',
                    'The artifacts of this tracker do not have any %s description %s yet.'
                ),
                '<em>',
                '</em>'
            ) . '</p>';
        }

        return $warning . '<p>' .
            dgettext('tuleap-tracker', 'The description is a free-text account of the artifact.') . '</p>' .
            $content;
    }

    public function displayAdmin(
        TrackerSemanticManager $semantic_manager,
        TrackerManager $tracker_manager,
        Codendi_Request $request,
        PFUser $current_user,
    ): void {
        $this->tracker->displayAdminItemHeaderBurningParrot(
            $tracker_manager,
            'editsemantic',
            $this->getLabel()
        );

        $template_rendreder      = TemplateRendererFactory::build()->getRenderer(TRACKER_TEMPLATE_DIR);
        $admin_presenter_builder = new \Tuleap\Tracker\Semantic\Description\AdminPresenterBuilder(Tracker_FormElementFactory::instance());

        echo $template_rendreder->renderToString(
            'semantics/admin-description',
            $admin_presenter_builder->build($this, $this->tracker, $this->getCSRFToken())
        );

        $semantic_manager->displaySemanticFooter($this, $tracker_manager);
    }

    public function process(
        TrackerSemanticManager $semantic_manager,
        TrackerManager $tracker_manager,
        Codendi_Request $request,
        PFUser $current_user,
    ): void {
        if ($request->exist('update')) {
            $this->getCSRFToken()->check();
            if ($field = Tracker_FormElementFactory::instance()->getUsedFieldByIdAndType($this->tracker, $request->get('text_field_id'), ['text'])) {
                $this->text_field = $field;
                if ($this->save()) {
                    $GLOBALS['Response']->addFeedback('info', dgettext('tuleap-tracker', 'Semantic description updated'));
                    $GLOBALS['Response']->redirect($this->getUrl());
                } else {
                    $GLOBALS['Response']->addFeedback('error', dgettext('tuleap-tracker', 'Unable to save the description'));
                }
            } else {
                $GLOBALS['Response']->addFeedback('error', dgettext('tuleap-tracker', 'The field you submitted is not a text field'));
            }
        } elseif ($request->exist('delete')) {
            $this->getCSRFToken()->check();
            $this->deleteDescription();
            $GLOBALS['Response']->addFeedback(Feedback::INFO, dgettext('tuleap-tracker', 'Semantic description unset'));
            $GLOBALS['Response']->redirect($this->getUrl());
        }
        $this->displayAdmin($semantic_manager, $tracker_manager, $request, $current_user);
    }

    public function save(): bool
    {
        $dao = new DescriptionSemanticDAO();
        $dao->save($this->tracker->getId(), $this->getFieldId());
        return true;
    }

    private function deleteDescription(): void
    {
        $dao = new DescriptionSemanticDAO();
        $dao->deleteForTracker($this->tracker->getId());
    }

    /**
     * Export semantic to XML
     *
     * @param SimpleXMLElement &$root the node to which the semantic is attached (passed by reference)
     * @param array $xml_mapping correspondance between real ids and xml IDs
     *
     * @return void
     */
    public function exportToXml(SimpleXMLElement $root, $xml_mapping)
    {
        if ($this->getFieldId() && in_array($this->getFieldId(), $xml_mapping)) {
            $child = $root->addChild('semantic');
            $child->addAttribute('type', $this->getShortName());
            $cdata = new \XML_SimpleXMLCDATAFactory();
            $cdata->insert($child, 'shortname', $this->getShortName());
            $cdata->insert($child, 'label', $this->getLabel());
            $cdata->insert($child, 'description', $this->getDescription());
            $child->addChild('field')->addAttribute('REF', array_search($this->getFieldId(), $xml_mapping));
        }
    }

    /**
     * Is the field used in semantics?
     *
     * @param Tracker_FormElement_Field the field to test if it is used in semantics or not
     *
     * @return bool returns true if the field is used in semantics, false otherwise
     */
    public function isUsedInSemantics(Tracker_FormElement_Field $field)
    {
        return $this->getFieldId() == $field->getId();
    }
}
