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
use Doctrine\ORM\Mapping\FieldMapping;
use F0ska\AutoGridBundle\Exception\ActionException;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\AttributeService;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FormBuilder
{
    private FormFactoryInterface $formFactory;

    public function __construct(
        FormFactoryInterface $formFactory
    ) {
        $this->formFactory = $formFactory;
    }

    public function buildForm(object $entity, Parameters $parameters): ?FormInterface
    {
        $formName = 'form-' . $parameters->agId;
        $action = $parameters->action;
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            $this->getFormType($action, $parameters),
            $entity,
            ['attr' => ['id' => $formName]]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl($action));

        $this->addFields($builder, $action, $parameters, true);

        return $builder->getForm();
    }

    public function buildFilterForm(
        ?string $fieldName,
        string $action,
        Parameters $parameters
    ): ?FormInterface {
        $formName = 'filter-' . $parameters->agId;
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            $this->getFormType($action, $parameters),
            null,
            ['attr' => ['id' => $formName . uniqid('-')]]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl($action));
        foreach ($parameters->fields as $field) {
            if (($fieldName === null || $field->name === $fieldName) && $field->canFilter) {
                $this->buildSearchField($builder, $field, $fieldName !== null);
                continue;
            }
            if ($builder->has($field->name)) {
                $builder->remove($field->name);
            }
        }
        $builder->add(
            $parameters->agId,
            HiddenType::class,
            ['mapped' => false, 'allow_extra_fields' => true, 'data' => $fieldName]
        );
        return $builder->getForm();
    }

    public function buildDisplayForm(Parameters $parameters): ?FormInterface
    {
        $action = $parameters->action;
        $builder = $this->formFactory->createBuilder(
            $this->getFormType($action, $parameters),
        );
        $this->addFields($builder, $action, $parameters, false);
        return $builder->getForm();
    }

    public function getSubmitRedirect(FormInterface $form, int $entityId, Parameters $parameters): RedirectResponse
    {
        $actions = [
            $form->getExtraData()['redirect'] ?? null,
            $parameters->request['redirect'] ?? null,
            $parameters->attributes['redirect_on_submit'] ?? null,
            'view',
            'grid',
            'edit',
            'create',
        ];

        foreach ($actions as $action) {
            if (!empty($action) && $parameters->isAllowed($action)) {
                return new RedirectResponse($parameters->actionUrl($action, ['id' => $entityId]));
            }
        }

        throw new ActionException('Something bad happened');
    }

    private function addFields(
        FormBuilderInterface $builder,
        string $action,
        Parameters $parameters,
        bool $isTrueForm
    ): void {
        foreach ($parameters->fields as $field) {
            if (!$parameters->isFieldAllowed($field, $action)) {
                if ($builder->has($field->name)) {
                    $builder->remove($field->name);
                }
                continue;
            }

            if (
                $isTrueForm
                && (
                    !empty($field->associationMapping?->mappedBy)
                    || $field->mappingType === AttributeService::MAPPING_VIRTUAL
                )
            ) {
                continue;
            }

            if ($isTrueForm && $field->mappingType === AttributeService::MAPPING_FIELD && $field->fieldMapping->id) {
                if ($builder->has($field->name)) {
                    $builder->remove($field->name);
                }
                $builder->add($field->name, HiddenType::class);
                continue;
            }

            $this->addField($builder, $field);
        }
    }

    private function buildSearchField(FormBuilderInterface $builder, FieldParameter $field, bool $required): void
    {
        $form = $this->prepareFilterFieldForm($field, $required);
        if (!empty($field->attributes['range_filter'])) {
            $entryType = $form['type'];
            $entryOptions = $form['options'];
            $form['type'] = CollectionType::class;
            $form['options'] = [
                'required' => false,
                'entry_type' => $entryType,
                'data' => ['from' => '', 'to' => ''],
                'entry_options' => ['label' => null, 'label_format' => 'f0ska.autogrid.range.%name%'] + $entryOptions,
            ];
        }
        $this->addField($builder, $field, $form);
    }

    private function prepareFilterFieldForm(FieldParameter $field, bool $required): array
    {
        /** @var FieldMapping $mapping */
        $mappingType = $field->fieldMapping?->type;
        $form = $field->attributes['form'];
        if ($mappingType) {
            if (in_array($mappingType, [Types::TEXT, Types::JSON, Types::SIMPLE_ARRAY], true)) {
                $form['type'] = TextType::class;
                $form['options'] = ['required' => $required];
                return $form;
            }

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
                'choices' => $this->buildChoicesFromChoices($choices),
            ];
            if (!empty($field->attributes['multiple_filter'])) {
                $form['options']['multiple'] = true;
            }
            return $form;
        }

        $form['options']['required'] = $required;
        return $form;
    }

    private function addField(FormBuilderInterface $builder, FieldParameter $field, ?array $form = null): void
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
    }

    private function getFormType(string $action, Parameters $parameters): string
    {
        $attr = $parameters->attributes;
        return $attr['form_type_' . $action] ?? $attr['form_type'] ?? FormType::class;
    }

    private function buildChoicesFromChoices(array $choices): array
    {
        $result = [];
        /** @var ChoiceView $choice */
        foreach ($choices as $choice) {
            $result[$choice->label] = $choice->value;
        }
        return $result;
    }
}
