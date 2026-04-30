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

namespace F0ska\AutoGridBundle\Service\Provider;

use Doctrine\Common\Collections\Collection;
use F0ska\AutoGridBundle\Exception\RenderException;
use F0ska\AutoGridBundle\Model\FieldParameter;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class FieldValueProvider
{
    public function __construct(private readonly PropertyAccessorInterface $propertyAccessor)
    {
    }

    public function getValue(object $entity, FieldParameter $field): mixed
    {
        $object = $entity;
        $property = $field->name;
        if ($field->subObject !== null) {
            $object = $this->getPropertyValue($entity, $field->subObject);
            $property = $field->subName;
        }

        if ($object instanceof Collection) {
            $result = [];
            foreach ($object as $item) {
                $result[] = $this->getPropertyValue($item, $property);
            }

            return implode(', ', $result);
        }

        if ($object === null) {
            return null;
        }

        return $this->getPropertyValue($object, $property);
    }

    public function setValue(object $entity, FieldParameter $field, mixed $value): void
    {
        $object = $entity;
        $property = $field->name;
        if ($field->subObject !== null) {
            $object = $this->getPropertyValue($entity, $field->subObject);
            $property = $field->subName;
        }
        $this->setPropertyValue($object, $property, $value);
    }

    private function getPropertyValue(object $object, string $property): mixed
    {
        try {
            return $this->propertyAccessor->getValue($object, $property);
        } catch (ExceptionInterface $exception) {
            throw new RenderException(
                sprintf('Invalid property "%s" in class "%s"', $property, get_class($object)),
                previous: $exception
            );
        }
    }

    private function setPropertyValue(object $object, string $property, mixed $value): void
    {
        try {
            $this->propertyAccessor->setValue($object, $property, $value);
        } catch (ExceptionInterface $exception) {
            throw new RenderException(
                sprintf('Unable to set property "%s" in class "%s"', $property, get_class($object)),
                previous: $exception
            );
        }
    }
}
