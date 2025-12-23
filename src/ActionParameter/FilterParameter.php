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

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use F0ska\AutoGridBundle\Exception\ActionParameterException;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;

class FilterParameter implements ActionParameterInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
            $field = $parameters->fields[$key1];
            if (is_array($value1)) {
                foreach ($value1 as $key2 => $value2) {
                    if (!is_scalar($value2)) {
                        $value[$key1][$key2] = $this->normalizeNotScalarValue($value2, $field);
                        continue;
                    }
                    $value[$key1][$key2] = strval($value2);
                }
                continue;
            }
            if (!is_scalar($value1)) {
                $value[$key1] = $this->normalizeNotScalarValue($value1, $field);
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
        if ($this->isValueTypeValid($value)) {
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
                if (!$this->isValueTypeValid($subValue)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private function isValueTypeValid(mixed $value): bool
    {
        return is_scalar($value)
            || $value === null
            || $value instanceof DateTimeInterface
            || (is_object($value) && method_exists($value, 'getId'));
    }

    private function normalizeNotScalarValue(mixed $value, FieldParameter $field): ?string
    {
        if ($value instanceof DateTimeInterface && $field->fieldMapping !== null) {
            return $this->entityManager
                ->getConnection()
                ->convertToDatabaseValue($value, $field->fieldMapping->type);
        }
        return $value?->getId() ? strval($value->getId()) : null;
    }
}
