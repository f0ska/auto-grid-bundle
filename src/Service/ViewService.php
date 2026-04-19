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

use F0ska\AutoGridBundle\Builder\ChoiceBuilder;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\FormView;

class ViewService
{
    private FormFacade $formFacade;
    private ConfigurationService $configuration;
    private FieldsetService $fieldsetService;
    private TemplateGuesserService $templateGuesserService;
    private ChoiceBuilder $choiceBuilder;

    public function __construct(
        FormFacade $formFacade,
        ConfigurationService $configuration,
        FieldsetService $fieldsetService,
        TemplateGuesserService $templateGuesserService,
        ChoiceBuilder $choiceBuilder
    ) {
        $this->formFacade = $formFacade;
        $this->configuration = $configuration;
        $this->fieldsetService = $fieldsetService;
        $this->templateGuesserService = $templateGuesserService;
        $this->choiceBuilder = $choiceBuilder;
    }

    public function prepareView(Parameters $parameters): void
    {
        $displayFormView = $this->formFacade->buildDisplayForm($parameters)->createView();

        foreach ($parameters->fields as $field) {
            $this->addViewParameters($displayFormView, $field);
            $this->templateGuesserService->guess($field, $parameters->attributes);
        }

        $filterData = $this->formFacade->buildFilterForms($parameters);
        $parameters->view->filterForms = $filterData['filterForms'];
        $parameters->view->filterFormViews = $filterData['filterFormViews'];
        $parameters->view->advancedFilterForm = $filterData['advancedFilterForm'];
        $parameters->view->advancedFilterFormView = $filterData['advancedFilterFormView'];

        $parameters->view->fieldset = $this->fieldsetService->build($parameters);

        $this->buildFormThemes($parameters);
        $this->buildPaginationParameters($parameters);
        $this->buildMassAction($parameters);
        $this->buildExportAction($parameters);
    }

    private function addViewParameters(FormView $displayFormView, FieldParameter $field): void
    {
        $usefulKeys = ['choices', 'money_pattern'];
        if (!isset($displayFormView->children[$field->name])) {
            return;
        }
        $vars = $displayFormView->children[$field->name]->vars;
        foreach ($usefulKeys as $usefulKey) {
            if (!empty($vars[$usefulKey])) {
                $field->view[$usefulKey] = $vars[$usefulKey];
            }
        }
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
            'limit'  => $limit,
            'page'   => $parameters->request['page'] ?? 1,
            'count'  => 0,
        ];
    }

    private function buildMassAction(Parameters $parameters): void
    {
        if (empty($parameters->permissions['mass'])) {
            return;
        }
        $choices = $this->choiceBuilder->buildMassChoices($parameters);
        if (!empty($choices)) {
            $parameters->view->massActionChoices = $choices;
            $parameters->view->massActionFormView = $this->formFacade
                ->buildMassActionForm($parameters)
                ->createView()
            ;
        }
    }

    private function buildExportAction(Parameters $parameters): void
    {
        if (empty($parameters->permissions['export'])) {
            return;
        }
        $choices = $this->choiceBuilder->buildExportChoices($parameters);
        if (!empty($choices)) {
            $parameters->view->exportActionChoices = $choices;
            $parameters->view->exportActionFormView = $this->formFacade
                ->buildExportActionForm($parameters)
                ->createView()
            ;
        }
    }
}
