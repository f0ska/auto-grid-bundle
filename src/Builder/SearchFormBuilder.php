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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Length;

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
            'attr' => [
                'minlength' => $minLength,
                'maxlength' => $maxLength,
            ],
            'constraints' => [
                new AtLeastOneOf([
                    new Blank(),
                    new Length(
                        min: $minLength,
                        max: $maxLength
                    ),
                ]),
            ],
        ]);

        return $builder->getForm();
    }
}
