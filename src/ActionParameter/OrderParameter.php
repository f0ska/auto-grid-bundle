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

use F0ska\AutoGridBundle\Model\Parameters;

class OrderParameter implements ActionParameterInterface
{
    public function getCode(): string
    {
        return 'order';
    }

    public function validate(string $action, mixed $value, Parameters $parameters): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        foreach ($value as $field => $direction) {
            if (!in_array($direction, ['asc', 'desc', null], true)) {
                return false;
            }
            if (!isset($parameters->fields[$field])) {
                return false;
            }
        }
        return true;
    }

    public function normalize(mixed $value): array
    {
        return (array) $value;
    }
}
