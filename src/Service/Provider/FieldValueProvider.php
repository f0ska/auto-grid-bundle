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
use F0ska\AutoGridBundle\Service\ParametersService;

class FieldValueProvider
{
    public function getValue(object $entity, FieldParameter $field): mixed
    {
        $object = $entity;
        $property = $field->name;
        if ($field->mappingType === ParametersService::MAPPING_VIRTUAL) {
            $getter = 'get' . ucfirst($field->subObject);
            $object = $entity->{$getter}();
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

    private function getPropertyValue(object $object, string $property): mixed
    {
        $getter = 'get' . ucfirst($property);
        if (method_exists($object, $getter)) {
            return $object->{$getter}();
        }

        $isser = 'is' . ucfirst($property);
        if (method_exists($object, $isser)) {
            return $object->{$isser}();
        }

        throw new RenderException(sprintf('Invalid property "%s" in class "%s"', $property, get_class($object)));
    }
}
