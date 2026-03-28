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

namespace F0ska\AutoGridBundle\Builder;

use Doctrine\DBAL\Types\Types;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class FilterFormBuilder
{
    private const FILTER_TYPES_ALLOWED = [
        DateType::class,
        DateTimeType::class,
        TimeType::class,
        NumberType::class,
    ];

    private FormFactoryInterface $formFactory;
    private FormRegistryInterface $formRegistry;
    private ChoiceBuilder $choiceBuilder;

    public function __construct(
        FormFactoryInterface $formFactory,
        FormRegistryInterface $formRegistry,
        ChoiceBuilder $choiceBuilder
    ) {
        $this->formFactory = $formFactory;
        $this->formRegistry = $formRegistry;
        $this->choiceBuilder = $choiceBuilder;
    }

    public function buildSingleFieldFilterForm(string $fieldName, Parameters $parameters): FormInterface
    {
        $action = 'filter';
        $formName = 'filter-' . $fieldName . '-' . $parameters->agId;
        $builder = $this->getFilterFormBuilder($formName, $action, $parameters);
        foreach ($parameters->fields as $field) {
            if ($field->name === $fieldName && $field->canFilter) {
                $this->buildSearchField($builder, $field, true);
                continue;
            }
            if ($builder->has($field->name)) {
                $builder->remove($field->name);
            }
        }
        return $builder->getForm();
    }

    public function buildAdvancedFilterForm(Parameters $parameters): FormInterface
    {
        $action = 'advanced_filter';
        $formName = 'filter-' . $parameters->agId;
        $builder = $this->getFilterFormBuilder($formName, $action, $parameters);
        foreach ($parameters->fields as $field) {
            if ($field->canFilter) {
                $this->buildSearchField($builder, $field, false);
                continue;
            }
            if ($builder->has($field->name)) {
                $builder->remove($field->name);
            }
        }
        return $builder->getForm();
    }

    private function getFilterFormBuilder(
        string $formName,
        string $action,
        Parameters $parameters
    ): FormBuilderInterface {
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            $this->getFormType($action, $parameters),
            null,
            ['attr' => ['id' => $formName . uniqid('-'), 'data-turbo' => 'false']]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl($action));
        return $builder;
    }

    private function buildSearchField(FormBuilderInterface $builder, FieldParameter $field, bool $required): void
    {
        $form = $this->prepareFilterFieldForm($field, $required);
        if (!empty($field->attributes['range_filter'])) {
            $form = $this->prepareRangeFieldForm($form);
        }
        $this->addField($builder, $field, $form);
    }

    private function prepareRangeFieldForm(array $form): array
    {
        $entryType = $form['type'];
        $entryOptions = $form['options'];
        $form['type'] = CollectionType::class;
        $form['options'] = [
            'required' => false,
            'entry_type' => $entryType,
            'data' => ['from' => null, 'to' => null],
            'entry_options' => ['label' => null, 'label_format' => 'f0ska.autogrid.range.%name%'] + $entryOptions,
        ];
        return $form;
    }

    private function prepareFilterFieldForm(FieldParameter $field, bool $required): array
    {
        $mappingType = $field->fieldMapping?->type;
        $form = $field->attributes['form'];

        if ($mappingType) {
            if ($mappingType === Types::BOOLEAN) {
                $form['type'] = ChoiceType::class;
                $form['options'] = [
                    'required' => $required,
                    'expanded' => $required,
                    'choices' => ['f0ska.autogrid.choice.yes' => '1', 'f0ska.autogrid.choice.no' => '0'],
                ];
                return $form;
            }
        }

        $choices = $field->view['choices'] ?? null;
        if ($choices) {
            $form['type'] = ChoiceType::class;
            $form['options'] = [
                'required' => $required,
                'choices' => $this->choiceBuilder->buildChoicesFromChoices($choices),
            ];
            if (!empty($field->attributes['multiple_filter'])) {
                $form['options']['multiple'] = true;
            }
            return $form;
        }

        if (!$this->isFormTypeAllowedInFilter($this->formRegistry->getType($form['type']))) {
            $form['type'] = TextType::class;
            $form['options'] = ['required' => $required];
            return $form;
        }

        $form['options']['required'] = $required;
        return $form;
    }

    private function isFormTypeAllowedInFilter(ResolvedFormTypeInterface $typeInstance): bool
    {
        foreach (self::FILTER_TYPES_ALLOWED as $allowed) {
            if ($typeInstance instanceof $allowed || $typeInstance->getParent() instanceof $allowed) {
                return true;
            }
        }
        return false;
    }

    private function addField(FormBuilderInterface $builder, FieldParameter $field, array $form = []): void
    {
        if ($builder->has($field->name)) {
            return;
        }
        $options = $form['options'] ?? $field->attributes['form']['options'] ?? [];
        $type = $form['type'] ?? $field->attributes['form']['type'] ?? null;
        if (!isset($options['label']) && isset($field->attributes['label'])) {
            $options['label'] = $field->attributes['label'];
        }
        $builder->add($field->name, $type, $options);
        if (!empty($field->attributes['form']['transformer'])) {
            $builder->get($field->name)->addModelTransformer($field->attributes['form']['transformer']);
        }
    }

    private function getFormType(string $action, Parameters $parameters): string
    {
        $attr = $parameters->attributes;
        return $attr['form_type'][$action] ?? FormType::class;
    }
}
