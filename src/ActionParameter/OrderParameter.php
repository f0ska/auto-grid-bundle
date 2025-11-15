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

namespace F0ska\AutoGridBundle\ActionParameter;

use F0ska\AutoGridBundle\Exception\ActionParameterException;
use F0ska\AutoGridBundle\Model\Parameters;

class OrderParameter implements ActionParameterInterface
{
    public function getCode(): string
    {
        return 'order';
    }

    public function normalize(mixed $value, Parameters $parameters): array
    {
        $result = [];
        if (!is_array($value) || empty($value)) {
            throw new ActionParameterException();
        }
        foreach ($value as $field => $direction) {
            if (!is_string($field)) {
                throw new ActionParameterException();
            }
            if (!in_array($direction, ['asc', 'desc', null], true)) {
                throw new ActionParameterException();
            }
            if (!isset($parameters->fields[$field])) {
                throw new ActionParameterException();
            }
            $result[$field] = $direction;
        }
        return $result;
    }
}
