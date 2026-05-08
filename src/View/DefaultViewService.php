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
use F0ska\AutoGridBundle\View\Helper\FieldValueHelper;

class DefaultViewService implements ViewServiceInterface
{
    public function __construct(private readonly FieldValueHelper $fieldValueHelper)
    {
    }

    public function prepare(object $entity, FieldParameter $field): array
    {
        return [
            'value' => $this->fieldValueHelper->getValue($entity, $field),
        ];
    }
}
