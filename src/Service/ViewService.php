<?php
/*
 * This file is part of the F0ska/AutoGrid package.
 *
 * (c) Victor Shvets
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace F0ska\AutoGridBundle\Service;

use Doctrine\DBAL\Types\Types;
use F0ska\AutoGridBundle\Builder\FormBuilder;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormView;

class ViewService
{
    private array $templateByFormType = [
        CheckboxType::class => 'boolean',
        EntityType::class => 'debug',
    ];
    private array $templateByDbalType = [
        Types::SIMPLE_ARRAY => 'simple_array',
        Types::JSON => 'json',
        LegacyService::TYPES_ARRAY => 'json',
        LegacyService::TYPES_OBJECT => 'json',
        Types::ASCII_STRING => 'ascii_string',
        Types::BINARY => 'binary',
        Types::BLOB => 'binary',
    ];
    private array $dateFormats = [
        Types::DATE_MUTABLE => 'date',
        Types::DATE_IMMUTABLE => 'date',
        Types::DATEINTERVAL => 'interval',
        Types::DATETIME_MUTABLE => 'datetime',
        Types::DATETIME_IMMUTABLE => 'datetime',
        Types::DATETIMETZ_MUTABLE => 'datetime',
        Types::DATETIMETZ_IMMUTABLE => 'datetime',
        Types::TIME_MUTABLE => 'time',
        Types::TIME_IMMUTABLE => 'time',
        LegacyService::TYPES_DATE_POINT => 'datetime',
    ];
    private array $viewForms = [];
    private FormBuilder $formBuilder;
    private ConfigurationService $configuration;

    public function __construct(FormBuilder $formBuilder, ConfigurationService $configuration)
    {
        $this->formBuilder = $formBuilder;
        $this->configuration = $configuration;
    }

    public function prepareView(Parameters $parameters)
    {
        $entity = $parameters->attributes['entity'];
        $this->prepareViewForm($entity, $parameters);
        foreach ($parameters->fields as $field) {
            $this->addViewParameters($entity, $field);
            $this->buildTemplateAttributes($field);
        }
        $this->addFilterForms($parameters);
        $this->buildFieldsets($parameters);
        $this->buildFormThemes($parameters);
        $this->buildPaginationParameters($parameters);
        $this->buildMassAction($parameters);
        $this->buildExportAction($parameters);
    }

    private function prepareViewForm(string $entityClass, Parameters $parameters): void
    {
        if (!isset($this->viewForms[$entityClass])) {
            $this->viewForms[$entityClass] = $this->formBuilder->buildDisplayForm($parameters)->createView();
        }
    }

    private function addViewParameters(string $entity, FieldParameter $field): void
    {
        /** @var FormView $view */
        $view = $this->viewForms[$entity];
        $usefulKeys = ['choices', 'money_pattern'];
        if (!isset($view->children[$field->name])) {
            return;
        }
        $vars = $view->children[$field->name]->vars;
        foreach ($usefulKeys as $usefulKey) {
            if (!empty($vars[$usefulKey])) {
                $field->view[$usefulKey] = $vars[$usefulKey];
            }
        }
    }

    private function buildTemplateAttributes(FieldParameter $field): void
    {
        $field->view['template'] = $field->attributes['view_template'] ?? null;

        $this->setTruncate($field);
        $this->setFormat($field);
        $this->setFormExtra($field);

        if (!empty($field->view['template'])) {
            return;
        }

        if (isset($field->view['choices'])) {
            $field->view['template'] = $this->configuration->getFieldTemplate('choice');
            return;
        }

        $field->view['template'] = $this->getFieldTemplate($field);
    }

    private function setTruncate(FieldParameter $field): void
    {
        $field->view['grid_truncate'] = $this->configuration->getGridTextTruncate();
        if (!empty($field->attributes['grid_truncate'])) {
            $field->view['grid_truncate'] = $field->attributes['grid_truncate'];
        }
    }

    private function setFormat(FieldParameter $field): void
    {
        $type = $field->fieldMapping?->type;
        if (!$type || !isset($this->dateFormats[$type])) {
            return;
        }
        if (empty($field->view['format'])) {
            $field->view['format'] = $this->configuration->getFieldFormat($this->dateFormats[$type]);
        }
        if (empty($field->view['template'])) {
            $field->view['template'] = $this->configuration->getFieldTemplate('date');
        }
    }

    private function addFilterForms(Parameters $parameters): void
    {
        $parameters->view->filterForms = [];
        foreach ($parameters->fields as $field) {
            if ($field->canFilter) {
                $form = $this->formBuilder->buildSingleFieldFilterForm($field->name, $parameters);
                $value = $parameters->request['filter'][$field->name] ?? null;
                if ($value !== null && $parameters->action !== 'filter' && !$form->isSubmitted()) {
                    $form->get($field->name)->submit($value);
                }
                $parameters->view->filterForms[$field->name] = $form;
                $parameters->view->filterFormViews[$field->name] = $form->createView();
            }
        }

        if (!empty($parameters->attributes['advanced_filter'])) {
            $form = $this->formBuilder->buildAdvancedFilterForm($parameters);
            foreach ($form->all() as $formField) {
                $value = $parameters->request['filter'][$formField->getName()] ?? null;
                if ($value !== null && $parameters->action !== 'advanced_filter' && !$form->isSubmitted()) {
                    $formField->submit($value);
                }
            }
            $parameters->view->advancedFilterForm = $form;
            $parameters->view->advancedFilterFormView = $form->createView();
        }
    }

    private function getFieldTemplate(FieldParameter $field): string
    {
        $code = $this->templateByFormType[$field->attributes['form']['type']] ?? null;
        if ($code) {
            return $this->configuration->getFieldTemplate($code);
        }

        $code = $this->templateByDbalType[$field->fieldMapping?->type] ?? null;
        if ($code) {
            return $this->configuration->getFieldTemplate($code);
        }

        return $this->configuration->getFieldTemplate('text');
    }

    private function setFormExtra(FieldParameter $field): void
    {
        if (isset($field->view['money_pattern']) && str_contains($field->view['money_pattern'], '{{ widget }}')) {
            [$prefix, $suffix] = explode('{{ widget }}', $field->view['money_pattern']);
            if (!isset($field->attributes['value_prefix'])) {
                $field->attributes['value_prefix'] = $prefix;
            }
            if (!isset($field->attributes['value_suffix'])) {
                $field->attributes['value_suffix'] = $suffix;
            }
        }
    }

    private function buildFieldsets(Parameters $parameters): void
    {
        $fieldSet = $parameters->attributes['fieldset'] ?? [];
        $allowedFields = [];
        $noFieldset = [];

        foreach ($parameters->fields as $field) {
            if (!empty($field->permissions[$parameters->action])) {
                $allowedFields[] = $field->name;
                $noFieldset[$field->name] = null;
            }
        }

        foreach ($fieldSet as $set) {
            $set['fields'] = array_intersect($set['fields'], $allowedFields);
            $noFieldset = array_diff_key($noFieldset, array_flip($set['fields']));
        }

        foreach ($allowedFields as $fieldName) {
            $key = $parameters->fields[$fieldName]->attributes['add_to_fieldset'] ?? null;
            if ($key === null || !isset($fieldSet[$key])) {
                continue;
            }
            if (!in_array($fieldName, $fieldSet[$key]['fields'], true)) {
                $fieldSet[$key]['fields'][] = $fieldName;
                unset($noFieldset[$fieldName]);
            }
        }

        foreach ($fieldSet as $key => $set) {
            if (empty($set['fields'])) {
                unset($fieldSet[$key]);
            }
        }

        $parameters->view->fieldset = [
            'defined' => $fieldSet,
            'not_defined' => array_keys($noFieldset),
        ];
    }

    private function buildFormThemes(Parameters $parameters): void
    {
        $themes = $parameters->attributes['form_themes'] ?? null;
        $parameters->view->formThemes = $themes ?? $this->configuration->getFormThemes();
    }

    private function buildPaginationParameters(Parameters $parameters): void
    {
        $pageLimits = $parameters->attributes['page_limits'] ?? $this->configuration->getPaginationLimits();
        $limit = $parameters->request['limit'] ?? null;
        if (!$limit || !in_array($limit, $pageLimits, true)) {
            $limit = reset($pageLimits);
        }
        $parameters->view->pagination = [
            'limits' => $pageLimits,
            'limit' => $limit,
            'page' => $parameters->request['page'] ?? 1,
            'count' => 0,
        ];
    }

    private function buildMassAction(Parameters $parameters): void
    {
        if (empty($parameters->permissions['mass'])) {
            return;
        }
        $choices = $this->formBuilder->buildMassChoices($parameters);
        if (!empty($choices)) {
            $parameters->view->massActionChoices = $choices;
            $parameters->view->massActionFormView = $this->formBuilder
                ->buildMassActionForm($parameters)
                ->createView();
        }
    }

    private function buildExportAction(Parameters $parameters): void
    {
        if (empty($parameters->permissions['export'])) {
            return;
        }
        $choices = $this->formBuilder->buildExportChoices($parameters);
        if (!empty($choices)) {
            $parameters->view->exportActionChoices = $choices;
            $parameters->view->exportActionFormView = $this->formBuilder
                ->buildExportActionForm($parameters)
                ->createView();
        }
    }
}
