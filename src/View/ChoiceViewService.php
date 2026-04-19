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

namespace F0ska\AutoGridBundle\View;

use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Service\Provider\ChoiceProvider;
use F0ska\AutoGridBundle\Service\Provider\FieldValueProvider;

class ChoiceViewService implements ViewServiceInterface
{
    private FieldValueProvider $fieldValueProvider;
    private ChoiceProvider $choiceProvider;

    public function __construct(
        FieldValueProvider $fieldValueProvider,
        ChoiceProvider $choiceProvider
    ) {
        $this->fieldValueProvider = $fieldValueProvider;
        $this->choiceProvider = $choiceProvider;
    }

    public function prepare(object $entity, FieldParameter $field): array
    {
        $value = $this->fieldValueProvider->getValue($entity, $field);

        return [
            'value'  => $value,
            'labels' => $this->choiceProvider->getLabels($value, $field),
            'values' => $this->choiceProvider->getValues($value, $field),
        ];
    }
}
