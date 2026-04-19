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
use F0ska\AutoGridBundle\Attribute\EntityField\Filterable;
use F0ska\AutoGridBundle\Attribute\EntityField\Sortable;
use F0ska\AutoGridBundle\Model\AttributeCollection;
use ReflectionAttribute;
use ReflectionClass;

class AttributeParserService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function parse(string $entityClass): AttributeCollection
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $reflectionClass = $metadata->getReflectionClass();

        $entityAttributes = $this->parseEntityAttributes($reflectionClass);
        [$fieldAttributes, $pureVirtualFieldNames] = $this->parseFieldAttributes(
            $metadata,
            $reflectionClass,
            $entityAttributes
        );

        $this->processFieldsetAttributes($fieldAttributes, $entityAttributes);

        return new AttributeCollection($entityAttributes, $fieldAttributes, $pureVirtualFieldNames);
    }

    private function parseEntityAttributes(ReflectionClass $reflectionClass): array
    {
        $entityAttributes = [];
        foreach ($reflectionClass->getAttributes() as $attribute) {
            $this->addEntityValue($entityAttributes, $attribute);
        }

        return $entityAttributes;
    }

    private function parseFieldAttributes(
        ClassMetadata $metadata,
        ReflectionClass $reflectionClass,
        array &$entityAttributes
    ): array {
        $fieldAttributes = [];
        $defaultSort = [];
        $pureVirtualFieldNames = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $fieldName = $property->getName();
            $isDoctrineMapped = $metadata->hasField($fieldName) || $metadata->hasAssociation($fieldName);

            $propertyAttributes = [];
            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance instanceof Sortable) {
                    $this->processSortableAttribute($instance, $fieldName, $propertyAttributes, $defaultSort);
                } elseif ($instance instanceof Filterable) {
                    $this->processFilterableAttribute($instance, $propertyAttributes);
                } elseif ($instance instanceof AttributeInterface) {
                    $this->addValue($propertyAttributes, $instance->getCode(), $instance->getValue());
                }
            }

            $isVirtualField = $propertyAttributes['virtual_column'] ?? false;

            if ($isVirtualField) {
                $pureVirtualFieldNames[] = $fieldName;
            }

            if ($isDoctrineMapped || $isVirtualField) {
                $fieldAttributes[$fieldName] = $propertyAttributes;
            }
        }

        if (!empty($defaultSort)) {
            uasort($defaultSort, fn($a, $b) => $b['priority'] <=> $a['priority']);
            $entityAttributes['default_sort'] = array_map(fn($item) => $item['direction'], $defaultSort);
        }

        return [$fieldAttributes, $pureVirtualFieldNames];
    }

    private function processSortableAttribute(
        Sortable $attribute,
        string $fieldName,
        array &$propertyAttributes,
        array &$defaultSort
    ): void {
        $sortInfo = $attribute->getValue();
        $this->addValue($propertyAttributes, 'can_sort', $sortInfo['can_sort']);
        if (isset($sortInfo['direction'])) {
            $defaultSort[$fieldName] = [
                'direction' => $sortInfo['direction'],
                'priority'  => $sortInfo['priority'],
            ];
        }
    }

    private function processFilterableAttribute(
        Filterable $attribute,
        array &$propertyAttributes
    ): void {
        $info = $attribute->getValue();
        $this->addValue($propertyAttributes, 'can_filter', $info['enabled']);
        if ($info['condition'] !== null) {
            $this->addValue($propertyAttributes, 'filterable.condition', $info['condition']);
        }
        if ($info['form_type'] !== null) {
            $this->addValue($propertyAttributes, 'filterable.form_type', $info['form_type']);
        }
        if (!empty($info['form_options'])) {
            $this->addValue($propertyAttributes, 'filterable.form_options', $info['form_options']);
        }
    }

    private function processFieldsetAttributes(array $fieldAttributes, array &$entityAttributes): void
    {
        if (!isset($entityAttributes['fieldset'])) {
            return;
        }

        foreach ($fieldAttributes as $fieldName => $attributes) {
            if (isset($attributes['add_to_fieldset'])) {
                $fieldsetName = $attributes['add_to_fieldset'];
                if (isset($entityAttributes['fieldset'][$fieldsetName])) {
                    $entityAttributes['fieldset'][$fieldsetName]['fields'][] = $fieldName;
                }
            }
        }
    }

    private function addEntityValue(array &$entityAttributes, ReflectionAttribute $attribute): void
    {
        $instance = $attribute->newInstance();
        if ($instance instanceof AttributeInterface) {
            $this->addValue($entityAttributes, $instance->getCode(), $instance->getValue());
        }
    }

    private function addValue(array &$link, string $code, mixed $value): void
    {
        $keys = explode('.', $code);
        foreach ($keys as $part) {
            if (!array_key_exists($part, $link)) {
                $link[$part] = [];
            }
            $link = &$link[$part];
        }
        $link = $value;
    }
}
