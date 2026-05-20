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

use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;

use function Symfony\Component\String\u;

class SearchFormBuilder
{
    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
    }

    public function buildSearchForm(Parameters $parameters): FormInterface
    {
        $formName = 'search-' . $parameters->agId;
        $search = $parameters->attributes['searchable'];
        $builder = $this->formFactory->createNamedBuilder(
            $formName,
            FormType::class,
            null,
            ['attr' => ['id' => $formName . uniqid('-'), 'data-turbo' => 'false']]
        );
        $builder->setMethod('POST');
        $builder->setAction($parameters->actionUrl('search'));
        $minLength = (int) $search['min_length'];
        $maxLength = (int) $search['max_length'];
        $builder->add('term', TextType::class, [
            'required' => false,
            'data' => $parameters->request['search']['term'] ?? null,
            'empty_data' => null,
            'attr' => [
                'minlength' => $minLength,
                'maxlength' => $maxLength,
            ],
            'constraints' => [
                new Length(
                    min: $minLength,
                    max: $maxLength
                ),
            ],
        ]);

        if (!empty($search['field_selector'])) {
            $builder->add('fields', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->buildFieldChoices($parameters, $search['fields']),
                'data' => $parameters->request['search']['fields'] ?? $search['fields'],
            ]);
        }

        return $builder->getForm();
    }

    /**
     * @param string[] $fields
     *
     * @return array<string, string>
     */
    private function buildFieldChoices(Parameters $parameters, array $fields): array
    {
        $choices = [];
        foreach ($fields as $field) {
            $choices[$this->getFieldLabel($parameters, $field)] = $field;
        }

        return $choices;
    }

    private function getFieldLabel(Parameters $parameters, string $field): string
    {
        $fieldParameter = $parameters->fields[$field] ?? $parameters->fields[str_replace('.', ':', $field)] ?? null;
        if ($fieldParameter !== null && isset($fieldParameter->attributes['label'])) {
            return (string) $fieldParameter->attributes['label'];
        }

        return u($field)->replace('.', ' ')->snake()->replace('_', ' ')->title()->toString();
    }
}
