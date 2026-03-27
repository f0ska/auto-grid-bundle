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

namespace F0ska\AutoGridBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use F0ska\AutoGridBundle\Attribute\AttributeInterface;
use F0ska\AutoGridBundle\Model\AttributeCollection;
use ReflectionAttribute;

class AttributeParserService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function parse(string $entityClass): AttributeCollection
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $entityAttributes = [];
        $fieldAttributes = [];

        foreach ($metadata->getReflectionClass()->getAttributes() as $attribute) {
            $this->addEntityValue($entityAttributes, $attribute);
        }

        foreach ($metadata->getFieldNames() as $fieldName) {
            foreach ($metadata->getReflectionClass()->getProperty($fieldName)->getAttributes() as $attribute) {
                $this->addFieldValue($fieldAttributes, $attribute, $fieldName);
            }
        }

        foreach ($metadata->getAssociationNames() as $fieldName) {
            foreach ($metadata->getReflectionClass()->getProperty($fieldName)->getAttributes() as $attribute) {
                $this->addFieldValue($fieldAttributes, $attribute, $fieldName);
            }
        }

        return new AttributeCollection($entityAttributes, $fieldAttributes);
    }

    private function addEntityValue(array &$entityAttributes, ReflectionAttribute $attribute): void
    {
        $instance = $attribute->newInstance();
        if ($instance instanceof AttributeInterface) {
            $this->addValue($entityAttributes, $instance->getCode(), $instance->getValue());
        }
    }

    private function addFieldValue(array &$fieldAttributes, ReflectionAttribute $attribute, string $fieldName): void
    {
        $instance = $attribute->newInstance();
        if ($instance instanceof AttributeInterface) {
            $this->addValue($fieldAttributes, $fieldName . '.' . $instance->getCode(), $instance->getValue());
        }
    }

    private function addValue(array &$link, string $code, mixed $value): void
    {
        foreach (explode('.', $code) as $part) {
            if (!array_key_exists($part, $link)) {
                $link[$part] = [];
            }
            $link = &$link[$part];
        }
        $link = $value;
    }
}
