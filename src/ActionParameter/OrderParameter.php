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

use F0ska\AutoGridBundle\Exception\InvalidGridParameterException;
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
            throw new InvalidGridParameterException('Invalid request parameter: order must be a non-empty array');
        }
        foreach ($value as $field => $direction) {
            if (!is_string($field)) {
                throw new InvalidGridParameterException('Invalid request parameter: order field must be a string');
            }
            if (!in_array($direction, ['asc', 'desc', null], true)) {
                throw new InvalidGridParameterException(sprintf('Invalid request parameter: invalid order direction for "%s"', $field));
            }
            if (!isset($parameters->fields[$field])) {
                throw new InvalidGridParameterException(sprintf('Invalid request parameter: unknown order field "%s"', $field));
            }
            $result[$field] = $direction;
        }
        return $result;
    }
}
