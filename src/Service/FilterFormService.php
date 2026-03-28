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

use F0ska\AutoGridBundle\Builder\FormBuilder;
use F0ska\AutoGridBundle\Model\Parameters;

class FilterFormService
{
    private FormBuilder $formBuilder;

    public function __construct(FormBuilder $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    public function build(Parameters $parameters): array
    {
        $filterForms = [];
        $filterFormViews = [];

        foreach ($parameters->fields as $field) {
            if ($field->canFilter) {
                $form = $this->formBuilder->buildSingleFieldFilterForm($field->name, $parameters);
                $value = $parameters->request['filter'][$field->name] ?? null;
                if ($value !== null && $parameters->action !== 'filter' && !$form->isSubmitted()) {
                    $form->get($field->name)->submit($value);
                }
                $filterForms[$field->name] = $form;
                $filterFormViews[$field->name] = $form->createView();
            }
        }

        $advancedFilterForm = null;
        $advancedFilterFormView = null;
        if (!empty($parameters->attributes['advanced_filter'])) {
            $form = $this->formBuilder->buildAdvancedFilterForm($parameters);
            foreach ($form->all() as $formField) {
                $value = $parameters->request['filter'][$formField->getName()] ?? null;
                if ($value !== null && $parameters->action !== 'advanced_filter' && !$form->isSubmitted()) {
                    $formField->submit($value);
                }
            }
            $advancedFilterForm = $form;
            $advancedFilterFormView = $form->createView();
        }

        return [
            'filterForms' => $filterForms,
            'filterFormViews' => $filterFormViews,
            'advancedFilterForm' => $advancedFilterForm,
            'advancedFilterFormView' => $advancedFilterFormView,
        ];
    }
}
