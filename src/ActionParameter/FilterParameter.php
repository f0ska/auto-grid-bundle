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
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;

class FilterParameter implements ActionParameterInterface
{
    public function getCode(): string
    {
        return 'filter';
    }

    public function normalize(mixed $value, Parameters $parameters): array
    {
        if (!$this->validate($value, $parameters)) {
            throw new ActionParameterException();
        }

        if (!is_array($value)) {
            return [];
        }

        foreach ($value as $key1 => $value1) {
            if (!is_string($key1)) {
                unset($value[$key1]);
                continue;
            }
            if (is_array($value1)) {
                foreach ($value1 as $key2 => $value2) {
                    if (!is_scalar($value2)) {
                        unset($value1[$key2]);
                        continue;
                    }
                    $value1[$key2] = strval($value2);
                }
                if (empty($value1)) {
                    unset($value[$key1]);
                }
                continue;
            }
            if (!is_scalar($value1)) {
                unset($value[$key1]);
                continue;
            }
            $value[$key1] = strval($value1);
        }

        return $value ?: [];
    }

    private function validate(mixed $value, Parameters $parameters): bool
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

    private function isValueValid(mixed $value, FieldParameter $field): bool
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
