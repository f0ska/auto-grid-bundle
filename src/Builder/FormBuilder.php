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
use F0ska\AutoGridBundle\Exception\ActionException;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\AttributeService;
use F0ska\AutoGridBundle\Service\LegacyService;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;

class FormBuilder
{
    private FormFactoryInterface $formFactory;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(FormFactoryInterface $formFactory, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(object $entity, Parameters $parameters): FormInterface
    {
        $formName = 'form-' . $parameters->agId;
        $action = $parameters->action;
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            $this->getFormType($action, $parameters),
            $entity,
            ['attr' => ['id' => $formName, 'data-turbo' => 'false']]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl($action));

        $this->addFields($builder, $action, $parameters, true);

        return $builder->getForm();
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

    public function buildDisplayForm(Parameters $parameters): ?FormInterface
    {
        $action = $parameters->action;
        $builder = $this->formFactory->createBuilder(
            $this->getFormType($action, $parameters),
        );
        $this->addFields($builder, $action, $parameters, false);
        return $builder->getForm();
    }

    public function buildMassActionForm(Parameters $parameters): ?FormInterface
    {
        $formName = 'mass-' . $parameters->agId;
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            $this->getFormType('mass', $parameters),
            null,
            ['attr' => ['id' => $formName . uniqid('-'), 'data-turbo' => 'false']]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl('mass'));

        $builder->add(
            'code',
            ChoiceType::class,
            ['choices' => $this->buildMassChoices($parameters), 'constraints' => [new NotBlank()]]
        );
        $builder->add(
            'ids',
            CollectionType::class,
            [
                'entry_options' => ['constraints' => [new NotBlank(), new Type(type: 'digit'), new Positive()]],
                'allow_add' => true,
            ]
        );

        return $builder->getForm();
    }

    public function buildExportActionForm(Parameters $parameters): ?FormInterface
    {
        $formName = 'export-' . $parameters->agId;
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            $this->getFormType('export', $parameters),
            null,
            ['attr' => ['id' => $formName . uniqid('-'), 'data-turbo' => 'false']]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl('export'));

        $builder->add(
            'code',
            ChoiceType::class,
            ['choices' => $this->buildExportChoices($parameters), 'constraints' => [new NotBlank()]]
        );

        return $builder->getForm();
    }

    /**
     * @param Parameters $parameters
     * @return array<string, string>
     */
    public function buildMassChoices(Parameters $parameters): array
    {
        $choices = [];
        $actions = $parameters->attributes['mass_action'] ?? [];
        foreach ($actions as $action) {
            if ($action['role'] !== null && !$this->authorizationChecker->isGranted($action['role'])) {
                continue;
            }
            $choices[$action['name']] = $action['code'];
        }
        return $choices;
    }

    /**
     * @param Parameters $parameters
     * @return array<string, string>
     */
    public function buildExportChoices(Parameters $parameters): array
    {
        $choices = [];
        $actions = $parameters->attributes['export_action'] ?? [];
        foreach ($actions as $action) {
            if ($action['role'] !== null && !$this->authorizationChecker->isGranted($action['role'])) {
                continue;
            }
            $choices[$action['name']] = $action['code'];
        }
        return $choices;
    }

    public function getSubmitRedirect(FormInterface $form, int $entityId, Parameters $parameters): RedirectResponse
    {
        $actions = !$form->isValid() ? [$parameters->action] : [
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
                    !empty($field->associationMapping->mappedBy)
                    || $field->mappingType === AttributeService::MAPPING_VIRTUAL
                )
            ) {
                continue;
            }

            if ($isTrueForm && $field->mappingType === AttributeService::MAPPING_FIELD && $field->fieldMapping?->id) {
                if ($builder->has($field->name)) {
                    $builder->remove($field->name);
                }
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
                'data' => ['from' => null, 'to' => null],
                'entry_options' => ['label' => null, 'label_format' => 'f0ska.autogrid.range.%name%'] + $entryOptions,
            ];
        }
        $this->addField($builder, $field, $form);
    }

    private function prepareFilterFieldForm(FieldParameter $field, bool $required): array
    {
        $mappingType = $field->fieldMapping?->type;
        $form = $field->attributes['form'];
        if ($mappingType) {
            if (in_array(
                $mappingType,
                [
                    Types::TEXT,
                    Types::JSON,
                    Types::SIMPLE_ARRAY,
                    LegacyService::TYPES_ARRAY,
                    LegacyService::TYPES_OBJECT,
                ],
                true
            )) {
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

    /**
     * @param array<string|int, ChoiceView> $choices
     * @return array<string, string>
     */
    private function buildChoicesFromChoices(array $choices): array
    {
        $result = [];
        foreach ($choices as $choice) {
            if (is_string($choice->label)) {
                $result[$choice->label] = $choice->value;
            }
        }
        return $result;
    }
}
