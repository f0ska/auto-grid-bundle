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

use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\ParametersService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;

class EntityFormBuilder
{
    private FormFactoryInterface $formFactory;
    private ChoiceBuilder $choiceBuilder;

    public function __construct(
        FormFactoryInterface $formFactory,
        ChoiceBuilder $choiceBuilder
    ) {
        $this->formFactory = $formFactory;
        $this->choiceBuilder = $choiceBuilder;
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

    public function buildDisplayForm(Parameters $parameters): FormInterface
    {
        $action = $parameters->action;
        $builder = $this->formFactory->createBuilder(
            $this->getFormType($action, $parameters),
        );
        $this->addFields($builder, $action, $parameters, false);
        return $builder->getForm();
    }

    public function buildMassActionForm(Parameters $parameters): FormInterface
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
            ['choices' => $this->choiceBuilder->buildMassChoices($parameters), 'constraints' => [new NotBlank()]]
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

    public function buildExportActionForm(Parameters $parameters): FormInterface
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
            ['choices' => $this->choiceBuilder->buildExportChoices($parameters), 'constraints' => [new NotBlank()]]
        );

        return $builder->getForm();
    }

    private function addFields(
        FormBuilderInterface $builder,
        string $action,
        Parameters $parameters,
        bool $isTrueForm
    ): void {
        foreach ($parameters->fields as $field) {
            if ($isTrueForm) {
                if (!$parameters->isFieldAllowed($field, $action)) {
                    if ($builder->has($field->name)) {
                        $builder->remove($field->name);
                    }
                    continue;
                }
                if (
                    !empty($field->associationMapping->mappedBy)
                    || $field->mappingType === ParametersService::MAPPING_ASSOCIATED_SUBFIELD
                ) {
                    continue;
                }
                if ($field->mappingType === ParametersService::MAPPING_FIELD && $field->fieldMapping?->id) {
                    if ($builder->has($field->name)) {
                        $builder->remove($field->name);
                    }
                    continue;
                }
            }

            $this->addField($builder, $field);
        }
    }

    private function addField(FormBuilderInterface $builder, FieldParameter $field): void
    {
        if ($builder->has($field->name)) {
            return;
        }
        $options = $field->attributes['form']['options'] ?? [];
        $type = $field->attributes['form']['type'] ?? null;
        if (!$field->canEdit) {
            return;
        }
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
