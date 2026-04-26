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
use F0ska\AutoGridBundle\Service\Provider\BinarySizeProvider;
use F0ska\AutoGridBundle\Service\Provider\FieldValueProvider;

class BinaryViewService implements ViewServiceInterface
{
    public function __construct(
        private readonly FieldValueProvider $fieldValueProvider,
        private readonly BinarySizeProvider $binarySizeProvider
    ) {
    }

    public function prepare(object $entity, FieldParameter $field): array
    {
        $value = $this->fieldValueProvider->getValue($entity, $field);

        return [
            'value' => $value,
            'size'  => $this->binarySizeProvider->getFormattedSize($value),
        ];
    }
}
