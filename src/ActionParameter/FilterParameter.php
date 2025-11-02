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

use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;

class FilterParameter implements ActionParameterInterface
{
    public function getCode(): string
    {
        return 'filter';
    }

    public function normalize(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        $value = (array) $value;
        array_walk_recursive($value, fn($item) => (string) $item);
        return $value;
    }

    public function validate(string $action, mixed $value, Parameters $parameters): bool
    {
        if ($value === null) {
            return true;
        }
        if (!is_array($value) || empty($value)) {
            return false;
        }
        foreach ($value as $field => $fieldValue) {
            if (!isset($parameters->fields[$field])) {
                return false;
            }
            if (!$this->isValueValid($fieldValue, $parameters->fields[$field])) {
                return false;
            }
        }
        return true;
    }

    private function isValueValid($value, FieldParameter $field): bool
    {
        if (is_scalar($value) || $value === null) {
            return true;
        }
        if (is_array($value)) {
            if (
                empty($field->attributes['range_filter'])
                && empty($field->attributes['multiple_filter'])
                && empty($field->attributes['form']['options']['multiple'])
            ) {
                return false;
            }
            foreach ($value as $subValue) {
                if (!is_scalar($subValue) && !is_null($subValue)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
