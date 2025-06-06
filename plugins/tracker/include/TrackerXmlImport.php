<?php
/**
 * Copyright (c) Enalean, 2013 - Present. All Rights Reserved.
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

use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Ok;
use Tuleap\NeverThrow\Result;
use Tuleap\Project\UGroupRetrieverWithLegacy;
use Tuleap\Project\XML\Import\ExternalFieldsExtractor;
use Tuleap\Project\XML\Import\ImportConfig;
use Tuleap\Tracker\Admin\ArtifactLinksUsageDao;
use Tuleap\Tracker\Admin\ArtifactLinksUsageUpdater;
use Tuleap\Tracker\Artifact\XMLImport\MoveImportConfig;
use Tuleap\Tracker\Artifact\XMLImport\TrackerXmlImportConfig;
use Tuleap\Tracker\CreateTrackerFromXMLEvent;
use Tuleap\Tracker\Creation\TrackerCreationDataChecker;
use Tuleap\Tracker\Creation\TrackerCreationNotificationsSettings;
use Tuleap\Tracker\Creation\TrackerCreationNotificationsSettingsFromXmlBuilder;
use Tuleap\Tracker\Creation\TrackerCreationSettings;
use Tuleap\Tracker\Events\XMLImportArtifactLinkTypeCanBeDisabled;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\ArtifactLinkField;
use Tuleap\Tracker\FormElement\Field\File\CreatedFileURLMapping;
use Tuleap\Tracker\FormElement\Field\XMLCriteriaValueCache;
use Tuleap\Tracker\Hierarchy\HierarchyDAO;
use Tuleap\Tracker\Tracker\XML\Importer\GetInstanceFromXml;
use Tuleap\Tracker\TrackerColor;
use Tuleap\Tracker\TrackerIsInvalidException;
use Tuleap\Tracker\TrackerXMLFieldMappingFromExistingTracker;
use Tuleap\Tracker\Webhook\WebhookDao;
use Tuleap\Tracker\Webhook\WebhookFactory;
use Tuleap\Tracker\XML\Importer\ImportedChangesetMapping;
use Tuleap\Tracker\XML\Importer\ImportXMLProjectTrackerDone;
use Tuleap\Tracker\XML\TrackerXmlImportFeedbackCollector;
use Tuleap\XML\MappingsRegistry;
use Tuleap\XML\PHPCast;
use Tuleap\XML\SimpleXMLElementBuilder;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class TrackerXmlImport
{
    public const XML_PARENT_ID_EMPTY = '0';

    public const DEFAULT_NOTIFICATIONS_LEVEL = 0;

    /** @var TrackerFactory */
    private $tracker_factory;

    /** @var EventManager */
    private $event_manager;

    /** @var HierarchyDAO */
    private $hierarchy_dao;

    /** @var Tracker_FormElementFactory */
    private $formelement_factory;

    /** @var XML_RNGValidator */
    private $rng_validator;

    /** @var Tracker_Workflow_Trigger_RulesManager */
    private $trigger_rulesmanager;

    private $xml_fields_mapping = [];

    /**
     * @var array
     */
    private $renderers_xml_mapping     = [];
    private array $reports_xml_mapping = [];

    /** @var Tracker_Artifact_XMLImport */
    private $xml_import;

    /** @var User\XML\Import\IFindUserFromXMLReference */
    private $user_finder;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @var ArtifactLinksUsageUpdater
     */
    private $artifact_links_usage_updater;

    /**
     * @var ArtifactLinksUsageDao
     */
    private $artifact_links_usage_dao;

    /**
     * @var TrackerXMLFieldMappingFromExistingTracker
     */
    private $existing_tracker_field_mapping;

    /**
     * @var ExternalFieldsExtractor
     */
    private $external_fields_extractor;
    /**
     * @var TrackerXmlImportFeedbackCollector
     */
    private $feedback_collector;
    /**
     * @var \Tuleap\Tracker\Creation\TrackerCreationDataChecker
     */
    private $creation_data_checker;

    public function __construct(
        TrackerFactory $tracker_factory,
        EventManager $event_manager,
        HierarchyDAO $hierarchy_dao,
        private readonly GetInstanceFromXml $get_instance_from_xml,
        Tracker_FormElementFactory $formelement_factory,
        XML_RNGValidator $rng_validator,
        Tracker_Workflow_Trigger_RulesManager $trigger_rulesmanager,
        Tracker_Artifact_XMLImport $xml_import,
        User\XML\Import\IFindUserFromXMLReference $user_finder,
        \Psr\Log\LoggerInterface $logger,
        ArtifactLinksUsageUpdater $artifact_links_usage_updater,
        ArtifactLinksUsageDao $artifact_links_usage_dao,
        TrackerXMLFieldMappingFromExistingTracker $tracker_XML_field_mapping_from_existing_tracker,
        ExternalFieldsExtractor $external_fields_extractor,
        TrackerXmlImportFeedbackCollector $feedback_collector,
        TrackerCreationDataChecker $creation_data_checker,
        private readonly TrackerCreationNotificationsSettingsFromXmlBuilder $notifications_settings_from_xml_builder,
    ) {
        $this->tracker_factory                = $tracker_factory;
        $this->event_manager                  = $event_manager;
        $this->hierarchy_dao                  = $hierarchy_dao;
        $this->formelement_factory            = $formelement_factory;
        $this->rng_validator                  = $rng_validator;
        $this->trigger_rulesmanager           = $trigger_rulesmanager;
        $this->xml_import                     = $xml_import;
        $this->user_finder                    = $user_finder;
        $this->logger                         = $logger;
        $this->artifact_links_usage_updater   = $artifact_links_usage_updater;
        $this->artifact_links_usage_dao       = $artifact_links_usage_dao;
        $this->existing_tracker_field_mapping = $tracker_XML_field_mapping_from_existing_tracker;
        $this->external_fields_extractor      = $external_fields_extractor;
        $this->feedback_collector             = $feedback_collector;
        $this->creation_data_checker          = $creation_data_checker;
    }

    /**
     * @return TrackerXmlImport
     */
    public static function build(
        User\XML\Import\IFindUserFromXMLReference $user_finder,
        ?\Psr\Log\LoggerInterface $logger = null,
    ) {
        $builder         = new Tracker_Artifact_XMLImportBuilder();
        $tracker_factory = TrackerFactory::instance();

        $logger = $logger === null ? new \Psr\Log\NullLogger() : $logger;

        $artifact_links_usage_dao     = new ArtifactLinksUsageDao();
        $artifact_links_usage_updater = new ArtifactLinksUsageUpdater($artifact_links_usage_dao);
        $event_manager                = EventManager::instance();
        $ugroup_manager               = new UGroupManager();

        return new TrackerXmlImport(
            $tracker_factory,
            $event_manager,
            new HierarchyDAO(),
            new GetInstanceFromXml(
                $tracker_factory,
                Tracker_CannedResponseFactory::instance(),
                Tracker_FormElementFactory::instance(),
                $user_finder,
                new TrackerXmlImportFeedbackCollector(),
                Tracker_SemanticFactory::instance(),
                new Tracker_RuleFactory(
                    new Tracker_RuleDao()
                ),
                Tracker_ReportFactory::instance(),
                WorkflowFactory::instance(),
                new WebhookFactory(new WebhookDao()),
                new UGroupRetrieverWithLegacy($ugroup_manager),
                $logger,
            ),
            Tracker_FormElementFactory::instance(),
            new XML_RNGValidator(),
            $tracker_factory->getTriggerRulesManager(),
            $builder->build(
                $user_finder,
                $logger
            ),
            $user_finder,
            new WrapperLogger($logger, 'TrackerXMLImport'),
            $artifact_links_usage_updater,
            $artifact_links_usage_dao,
            new TrackerXMLFieldMappingFromExistingTracker(),
            new ExternalFieldsExtractor($event_manager),
            new TrackerXmlImportFeedbackCollector(),
            TrackerCreationDataChecker::build(),
            new TrackerCreationNotificationsSettingsFromXmlBuilder(),
        );
    }

    /**
     * @return String | bool the attribute value in String, False if this attribute does not exist
     */
    private function getXmlTrackerAttribute(SimpleXMLElement $xml_tracker, string $attribute_name)
    {
        $tracker_attributes = $xml_tracker->attributes();
        if (! $tracker_attributes[$attribute_name]) {
            return false;
        }
        return (string) $tracker_attributes[$attribute_name];
    }

    /**
     * @return Tracker[]|void
     * @throws TrackerFromXmlException
     * @throws TrackerFromXmlImportCannotBeCreatedException
     * @throws Tracker_Exception
     * @throws XML_ParseException
     */
    public function import(
        ImportConfig $configuration,
        Project $project,
        SimpleXMLElement $xml_input,
        MappingsRegistry $registery,
        string $extraction_path,
        PFUser $user,
    ) {
        if (! $xml_input->trackers) {
            return;
        }

        $partial_element = SimpleXMLElementBuilder::buildSimpleXMLElementToLoadHugeFiles((string) $xml_input->asXml());
        $this->external_fields_extractor->extractExternalFieldFromProjectElement($partial_element);
        $this->rng_validator->validate(
            $partial_element->trackers,
            __DIR__ . '/../resources/trackers.rng'
        );

        $tracker_import_config = new TrackerXmlImportConfig($user, new \DateTimeImmutable(), MoveImportConfig::buildForRegularImport(), false);

        $this->activateArtlinkV2($project, $xml_input->trackers);

        $this->xml_fields_mapping = [];
        $created_trackers_mapping = [];
        $created_trackers_objects = [];
        $artifacts_id_mapping     = new Tracker_XML_Importer_ArtifactImportedMapping();
        $changeset_id_mapping     = new ImportedChangesetMapping();
        $url_mapping              = new CreatedFileURLMapping();

        $ordered_xml_trackers = $this->getAllXmlTrackersOrderedByPriority($xml_input);

        foreach ($ordered_xml_trackers as $xml_tracker_id => $ordered_xml_tracker) {
            $tracker_created                           = $this->instantiateTrackerFromXml($project, $ordered_xml_tracker, $configuration, $created_trackers_mapping);
            $created_trackers_objects[$xml_tracker_id] = $tracker_created;
            $created_trackers_mapping                  = $created_trackers_mapping + [(string) $xml_tracker_id => $tracker_created->getId()];
            $registery->addReference($xml_tracker_id, $tracker_created->getId());
        }

        foreach ($this->renderers_xml_mapping as $xml_reference => $renderer_xml_mapping) {
            $registery->addReference($xml_reference, $renderer_xml_mapping);
        }
        foreach ($this->reports_xml_mapping as $xml_reference => $report_xml_mapping) {
            $registery->addReference($xml_reference, $report_xml_mapping);
        }

        $xml_field_values_mapping = new TrackerXmlFieldsMapping_FromAnotherPlatform($this->xml_fields_mapping);

        $created_artifacts = $this->importBareArtifacts(
            $ordered_xml_trackers,
            $created_trackers_objects,
            $artifacts_id_mapping,
            $tracker_import_config
        );

        $this->importChangesets(
            $ordered_xml_trackers,
            $created_trackers_objects,
            $extraction_path,
            $xml_field_values_mapping,
            $artifacts_id_mapping,
            $url_mapping,
            $created_artifacts,
            $changeset_id_mapping,
            $tracker_import_config
        );

        // Deal with artifact link types after changesets import to keep the history of types
        $this->disableArtifactLinkTypes($xml_input, $project);

        if (
            $this->artifact_links_usage_dao->isTypeDisabledInProject(
                $project->getID(),
                ArtifactLinkField::TYPE_IS_CHILD
            )
        ) {
            $this->logger->warning('Artifact link type _is_child is disabled, skipping the hierarchy');
        } else {
            $this->importHierarchy($xml_input, $created_trackers_mapping);
        }

        if (isset($xml_input->trackers->triggers)) {
            $this->trigger_rulesmanager->createFromXML($xml_input->trackers->triggers, $this->xml_fields_mapping);
        }

        $event = new ImportXMLProjectTrackerDone(
            $project,
            $xml_input,
            $created_trackers_mapping,
            $this->xml_fields_mapping,
            $registery,
            $artifacts_id_mapping,
            $changeset_id_mapping,
            $extraction_path,
            $this->logger,
            $xml_field_values_mapping,
            $this->user_finder,
            $created_trackers_objects,
            $user
        );
        $this->event_manager->processEvent($event);

        $this->event_manager->processEvent(
            Event::IMPORT_COMPAT_REF_XML,
            [
                'logger'          => $this->logger,
                'created_refs'    => [
                    'tracker'  => $created_trackers_mapping,
                    'artifact' => $artifacts_id_mapping->getMapping(),
                ],
                'service_name'    => 'tracker',
                'xml_content'     => $xml_input->trackers->references,
                'project'         => $project,
                'configuration'   => $configuration,
            ]
        );

        return $created_trackers_mapping;
    }

    private function disableArtifactLinkTypes(SimpleXMLElement $xml_input, Project $project)
    {
        if (! $xml_input->natures) {
            return;
        }

        foreach ($xml_input->natures->nature as $xml_type) {
            assert($xml_type instanceof SimpleXMLElement);
            $is_used = ! isset($xml_type['is_used']) || PHPCast::toBoolean($xml_type['is_used']) === true;

            if (! $is_used) {
                $type_name = (string) $xml_type;

                $event = new XMLImportArtifactLinkTypeCanBeDisabled($project, $type_name);
                $this->event_manager->processEvent($event);

                if ($this->typeCanBeDisabled($event)) {
                    $this->logger->info("Artifact link type $type_name will be deactivated.");
                    $this->artifact_links_usage_dao->disableTypeInProject($project->getID(), $type_name);
                } else {
                    $this->logger->warning($event->getMessage());
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function typeCanBeDisabled(XMLImportArtifactLinkTypeCanBeDisabled $event)
    {
        return ! $event->doesPluginCheckedTheType() ||
            ($event->doesPluginCheckedTheType() && $event->canTypeBeUnused());
    }

    /**
     * @return string
     * @throws Tracker_Exception
     */
    public function collectErrorsWithoutImporting(Project $project, SimpleXMLElement $xml_input)
    {
        if (! $xml_input->trackers) {
            return '';
        }
        $partial_element = SimpleXMLElementBuilder::buildSimpleXMLElementToLoadHugeFiles((string) $xml_input->asXml());
        $this->external_fields_extractor->extractExternalFieldFromProjectElement($partial_element);
        $this->rng_validator->validate($partial_element->trackers, __DIR__ . '/../resources/trackers.rng');

        $xml_trackers = $this->getAllXmlTrackersOrderedByPriority($xml_input);
        $trackers     = [];

        foreach ($xml_trackers as $xml_tracker_id => $xml_tracker) {
            $name        = (string) $xml_tracker->name;
            $description = (string) $xml_tracker->description;
            $item_name   = (string) $xml_tracker->item_name;
            $trackers[]  = $this->get_instance_from_xml->getInstanceFromXML(
                $xml_tracker,
                $project,
                $name,
                $description,
                $item_name,
                TrackerColor::default()->getName(),
                [],
                $this->xml_fields_mapping,
                $this->reports_xml_mapping,
                $this->renderers_xml_mapping,
            );
        }

        $trackers_name_error = $this->tracker_factory->collectTrackersNameInErrorOnMandatoryCreationInfo(
            $trackers,
            $project->getID()
        );

        $errors = '';

        if (! empty($trackers_name_error)) {
            $list_trackers_name = implode(', ', $trackers_name_error);
            $errors             = sprintf(dgettext('tuleap-tracker', 'The following trackers cannot be imported due to invalid data, name or short name already in use: %1$s'), $list_trackers_name);
        }

        return $errors;
    }

    private function activateArtlinkV2(Project $project, SimpleXMLElement $xml_element)
    {
        $use_natures = $xml_element['use-natures'];
        if ($use_natures == 'true') {
            if ($this->artifact_links_usage_updater->isProjectAllowedToUseArtifactLinkTypes($project)) {
                $this->logger->info('This project already uses artifact links nature feature.');
            } else {
                $this->artifact_links_usage_updater->forceUsageOfArtifactLinkTypes($project);
                $this->logger->info('Artifact links nature feature is now active.');
            }
        } elseif ($use_natures == 'false') {
            if (! $this->artifact_links_usage_updater->isProjectAllowedToUseArtifactLinkTypes($project)) {
                $this->logger->warning('This project will not be able to use artifact links nature feature.');
            }
        } else {
            $this->artifact_links_usage_updater->forceUsageOfArtifactLinkTypes($project);
            $this->logger->info("No attribute 'use-natures' found. By default, projects use the typed artifact links");
        }
    }

    /**
     * @return array of created artifacts
     * @throws Tracker_Artifact_Exception_XMLImportException
     */
    private function importBareArtifacts(
        array $xml_trackers,
        array $created_trackers_objects,
        Tracker_XML_Importer_ArtifactImportedMapping $artifacts_id_mapping,
        TrackerXmlImportConfig $tracker_import_config,
    ) {
        $created_artifacts = [];
        foreach ($xml_trackers as $xml_tracker_id => $xml_tracker) {
            if (isset($xml_tracker->artifacts)) {
                $created_artifacts[$xml_tracker_id] = $this->xml_import->importBareArtifactsFromXML(
                    $created_trackers_objects[$xml_tracker_id],
                    $xml_tracker->artifacts,
                    $artifacts_id_mapping,
                    $tracker_import_config
                );
            }
        }
        return $created_artifacts;
    }

    /**
     * @throws Tracker_Artifact_Exception_XMLImportException
     */
    private function importChangesets(
        array $xml_trackers,
        array $created_trackers_objects,
        $extraction_path,
        TrackerXmlFieldsMapping_FromAnotherPlatform $xml_mapping,
        Tracker_XML_Importer_ArtifactImportedMapping $artifacts_id_mapping,
        CreatedFileURLMapping $url_mapping,
        array $created_artifacts,
        ImportedChangesetMapping $changeset_id_mapping,
        TrackerXmlImportConfig $tracker_import_config,
    ): void {
        foreach ($xml_trackers as $xml_tracker_id => $xml_tracker) {
            if (isset($xml_tracker->artifacts)) {
                $this->xml_import->importArtifactChangesFromXML(
                    $created_trackers_objects[$xml_tracker_id],
                    $xml_tracker->artifacts,
                    $extraction_path,
                    $xml_mapping,
                    $artifacts_id_mapping,
                    $url_mapping,
                    $created_artifacts[$xml_tracker_id],
                    $changeset_id_mapping,
                    $tracker_import_config
                );
            }
        }
    }

    private function importHierarchy(SimpleXMLElement $xml_input, array $created_trackers_list)
    {
        $all_hierarchies = [];
        foreach ($this->getAllXmlTrackersOrderedByPriority($xml_input) as $xml_tracker) {
            $all_hierarchies = $this->buildTrackersHierarchy($all_hierarchies, $xml_tracker, $created_trackers_list);
        }

        $this->storeHierarchyInDB($all_hierarchies);
    }

    /**
     * protected for testing purpose
     *
     * @throws TrackerFromXmlException
     * @throws TrackerFromXmlImportCannotBeCreatedException
     * @throws Tracker_Exception
     * @throws XML_ParseException
     */
    protected function instantiateTrackerFromXml(
        Project $project,
        SimpleXMLElement $xml_tracker,
        ImportConfig $configuration,
        array $created_trackers_mapping,
    ): Tracker {
        $tracker_existing = $this->getTrackerToReUse($project, $xml_tracker, $configuration);
        if ($tracker_existing !== null) {
            return $tracker_existing;
        }

        try {
            return $this->createFromXML(
                $xml_tracker,
                $project,
                (string) $xml_tracker->name,
                (string) $xml_tracker->description,
                (string) $xml_tracker->item_name,
                (string) $xml_tracker->color,
                $created_trackers_mapping
            );
        } catch (\Tuleap\Tracker\TrackerIsInvalidException $exception) {
            $this->feedback_collector->addErrors($exception->getTranslatedMessage());
            $this->feedback_collector->displayErrors($this->logger);
            throw new TrackerFromXmlImportCannotBeCreatedException((string) $xml_tracker->name);
        }
    }

    /**
     * @return null|Tracker
     */
    private function getTrackerToReUse(
        Project $project,
        SimpleXMLElement $xml_tracker,
        ImportConfig $configuration,
    ) {
        foreach ($configuration->getExtraConfiguration() as $extra_configuration) {
            if ($extra_configuration->getServiceName() !== trackerPlugin::SERVICE_SHORTNAME) {
                continue;
            }

            $tracker_shortname = (string) $xml_tracker->item_name;

            if (in_array($tracker_shortname, $extra_configuration->getValue())) {
                $tracker_existing = $this->tracker_factory->getTrackerByShortnameAndProjectId(
                    $tracker_shortname,
                    (int) $project->getID()
                );

                if ($tracker_existing) {
                    $this->fillFieldMappingFromExistingTracker($tracker_existing, $xml_tracker);

                    return $tracker_existing;
                }
            }
        }

        return null;
    }

    private function fillFieldMappingFromExistingTracker(Tracker $tracker, SimpleXMLElement $xml_tracker)
    {
        $form_elements_existing   = $this->formelement_factory->getFields($tracker);
        $this->xml_fields_mapping = $this->existing_tracker_field_mapping->getXmlFieldsMapping(
            $xml_tracker,
            $form_elements_existing
        );
    }

    /**
     * @return Tracker|null
     * @throws TrackerFromXmlException
     * @throws Tracker_Exception
     * @throws XML_ParseException
     */
    public function createFromXMLFile(Project $project, string $filepath)
    {
        $tracker_xml = $this->loadXmlFile($filepath);
        if (! $tracker_xml) {
            return null;
        }

        try {
            $name        = (string) $tracker_xml->name;
            $description = (string) $tracker_xml->description;
            $item_name   = (string) $tracker_xml->item_name;

            return $this->createFromXML($tracker_xml, $project, $name, $description, $item_name, TrackerColor::default()->getName(), []);
        } catch (\Tuleap\Tracker\TrackerIsInvalidException $exception) {
            $this->feedback_collector->addErrors($exception->getTranslatedMessage());
            $this->feedback_collector->displayErrors($this->logger);
            return null;
        }
    }

    /**
     * @throws TrackerFromXmlException
     * @throws Tracker_Exception
     * @throws XML_ParseException
     * @throws \Tuleap\Tracker\TrackerIsInvalidException
     */
    public function createFromXMLFileWithInfo(
        Project $project,
        string $filepath,
        string $name,
        string $description,
        string $item_name,
        ?string $color,
    ): Tracker {
        $tracker_xml = $this->loadXmlFile($filepath);
        if (! $tracker_xml) {
            throw TrackerIsInvalidException::invalidXmlFile();
        }
        $event = new CreateTrackerFromXMLEvent($project, $tracker_xml);
        $this->event_manager->processEvent($event);

        return $this->createFromXML($tracker_xml, $project, $name, $description, $item_name, $color, []);
    }

    /**
     * First, creates a new Tracker Object by importing its structure from an XML file,
     * then, imports it into the Database, before verifying the consistency
     *
     * @throws TrackerFromXmlException
     * @throws Tracker_Exception
     * @throws XML_ParseException
     * @throws \Tuleap\Tracker\TrackerIsInvalidException
     */
    public function createFromXML(
        SimpleXMLElement $xml_element,
        Project $project,
        string $name,
        string $description,
        string $itemname,
        ?string $color,
        array $created_trackers_mapping,
    ): Tracker {
        $this->creation_data_checker->checkAtProjectCreation((int) $project->getId(), $name, $itemname);

        $partial_element = SimpleXMLElementBuilder::buildSimpleXMLElementToLoadHugeFiles((string) $xml_element->asXml());
        $this->external_fields_extractor->extractExternalFieldsFromTracker($partial_element);
        $this->rng_validator->validate(
            $partial_element,
            realpath(__DIR__ . '/../resources/tracker.rng')
        );

        $tracker = $this->get_instance_from_xml->getInstanceFromXML(
            $xml_element,
            $project,
            $name,
            $description,
            $itemname,
            $color,
            $created_trackers_mapping,
            $this->xml_fields_mapping,
            $this->reports_xml_mapping,
            $this->renderers_xml_mapping,
        );
        //Testing consistency of the imported tracker before updating database
        if ($tracker->testImport()) {
            $attributes                   = $xml_element->attributes();
            $is_displayed_in_new_dropdown = isset($attributes['is_displayed_in_new_dropdown']) ?
                (bool) $attributes['is_displayed_in_new_dropdown'] : false;
            $is_private_comment_used      = isset($attributes['use_private_comments']) ?
                PHPCast::toBoolean($attributes['use_private_comments']) : true;
            $is_move_artifacts_enabled    = (isset($attributes['enable_move_artifacts']) && $attributes['enable_move_artifacts'] !== null) ?
                PHPCast::toBoolean($attributes['enable_move_artifacts']) : true;

            $settings = new TrackerCreationSettings(
                $is_displayed_in_new_dropdown,
                $is_private_comment_used,
                $is_move_artifacts_enabled,
            );

            $this->notifications_settings_from_xml_builder
                ->getCreationNotificationsSettings($attributes, $tracker)
                ->andThen(fn (TrackerCreationNotificationsSettings $notifications_settings) => $this->saveTracker($tracker, $settings, $notifications_settings))
                ->match(
                    static fn (int $tracker_id)  => $tracker->setId($tracker_id),
                    function (string $error): void {
                        throw new TrackerFromXmlException($error);
                    }
                );
        } else {
            throw new TrackerFromXmlException('XML file cannot be imported');
        }

        $this->displayWarnings();

        XMLCriteriaValueCache::clearInstances();
        $this->formelement_factory->clearCaches();
        $this->tracker_factory->clearCaches();

        return $tracker;
    }

    /**
     * @return Ok<int>|Err<string>
     */
    private function saveTracker(Tracker $tracker, TrackerCreationSettings $settings, TrackerCreationNotificationsSettings $notifications_settings): Ok|Err
    {
        $tracker_id = $this->tracker_factory->saveObject($tracker, $settings, $notifications_settings);
        if (! $tracker_id) {
            return Result::err(
                dgettext(
                    'tuleap-tracker',
                    'Oops. Something weird occured. Unable to create the tracker. Please try again.'
                )
            );
        }

        return Result::ok($tracker_id);
    }

    /**
     *
     *
     * @return array The hierarchy array with new elements added
     */
    protected function buildTrackersHierarchy(array $hierarchy, SimpleXMLElement $xml_tracker, array $mapper)
    {
        $xml_parent_id = $this->getXmlTrackerAttribute($xml_tracker, 'parent_id');

        if ($xml_parent_id != self::XML_PARENT_ID_EMPTY) {
            $parent_id  = $mapper[$xml_parent_id];
            $tracker_id = $mapper[$this->getXmlTrackerAttribute($xml_tracker, 'id')];

            if (! isset($hierarchy[$parent_id])) {
                $hierarchy[$parent_id] = [];
            }

            array_push($hierarchy[$parent_id], $tracker_id);
        }

        return $hierarchy;
    }

    /**
     *
     * @param array $all_hierarchies
     *
     * Stores in database the hierarchy between created trackers
     */
    public function storeHierarchyInDB(array $all_hierarchies)
    {
        foreach ($all_hierarchies as $parent_id => $hierarchy) {
            $this->hierarchy_dao->updateChildren($parent_id, $hierarchy);
        }
    }

    private function displayWarnings()
    {
        if (empty($this->feedback_collector->getWarnings())) {
            return;
        }

        $this->feedback_collector->displayWarnings($this->logger);
    }

    /**
     * @return SimpleXMLElement|false
     */
    protected function loadXmlFile(string $filepath)
    {
        return \simplexml_load_string(\file_get_contents($filepath));
    }

    /**
     * protected for testing purpose
     * @return array Array of SimpleXmlElement with each tracker
     */
    protected function getAllXmlTrackersOrderedByPriority(SimpleXMLElement $xml_input): array
    {
        $xml_trackers = [];
        foreach ($xml_input->trackers->tracker as $xml_tracker) {
            $xml_trackers[$this->getXmlTrackerAttribute($xml_tracker, 'id')] = $xml_tracker;
        }

        uasort($xml_trackers, function (SimpleXMLElement $xml_tracker_a, SimpleXMLElement $xml_tracker_b) {
            $is_a_inherited_from_tracker = $this->hasTimeframeSemanticInheritedFromAnotherTracker($xml_tracker_a);
            $is_b_inherited_from_tracker = $this->hasTimeframeSemanticInheritedFromAnotherTracker($xml_tracker_b);

            if ($is_a_inherited_from_tracker === $is_b_inherited_from_tracker) {
                return 0;
            }

            if ($is_a_inherited_from_tracker) {
                return 1;
            }
            return -1;
        });

        return $xml_trackers;
    }

    private function hasTimeframeSemanticInheritedFromAnotherTracker(SimpleXMLElement $xml_tracker): bool
    {
        if (! $xml_tracker->semantics) {
            return false;
        }

        $inherited_from_tracker_xml_element = $xml_tracker->semantics->xpath("./semantic[@type='timeframe']/inherited_from_tracker");

        return $inherited_from_tracker_xml_element !== null && (is_array($inherited_from_tracker_xml_element) && count($inherited_from_tracker_xml_element) > 0);
    }
}
