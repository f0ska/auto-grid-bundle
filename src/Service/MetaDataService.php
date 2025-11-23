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
use ReflectionAttribute;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

class MetaDataService
{
    private int $instanceCount = 0;
    private int $subInstanceCount = 0;
    private array $metadata = [];
    private array $entityAttributes = [];
    private array $entityFieldAttributes = [];
    private EntityManagerInterface $entityManager;
    private ConfigurationService $configuration;
    private RequestStack $requestStack;
    private SluggerInterface $slugger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigurationService $configuration,
        RequestStack $requestStack,
        SluggerInterface $slugger
    ) {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
        $this->slugger = $slugger;
    }

    public function add(string $entityClass, ?string $customId = null, bool $isSubInstance = false): string
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $instanceCount = $isSubInstance ? ++$this->subInstanceCount : ++$this->instanceCount;
        $agId = $customId ? $this->prepareCustomAgId($customId) : $this->prepareAgId($metadata, $instanceCount);
        $this->metadata[$agId] = $metadata;
        $this->entityAttributes[$agId] = [];
        $this->entityFieldAttributes[$agId] = [];
        $this->buildEntityAttributes($agId, $metadata);
        if (!$isSubInstance) {
            $this->entityAttributes[$agId]['container_id'] = sprintf(
                '%s%s',
                $this->configuration->getFriendlyId(),
                $instanceCount > 1 ? $instanceCount : ''
            );
            $this->entityAttributes[$agId]['container_class'] = $this->configuration->getFriendlyId();
        }
        return $agId;
    }

    public function getMetadata(string $agId): ClassMetadata
    {
        return $this->metadata[$agId];
    }

    public function getEntityAttributes(string $agId): array
    {
        return $this->entityAttributes[$agId];
    }

    public function getEntityAttribute(string $agId, string $key): mixed
    {
        $data = $this->entityAttributes[$agId];
        foreach (explode('.', $key) as $part) {
            $data = $data[$part] ?? null;
        }
        return $data;
    }

    public function getEntityFieldAttributes(string $agId, string $fieldName): array
    {
        return $this->entityFieldAttributes[$agId][$fieldName] ?? [];
    }

    public function getEntityFieldAttribute(string $agId, string $fieldName, string $key): mixed
    {
        $data = $this->entityFieldAttributes[$agId][$fieldName] ?? [];
        foreach (explode('.', $key) as $part) {
            $data = $data[$part] ?? null;
        }
        return $data;
    }

    private function buildEntityAttributes(string $agId, ClassMetadata $metadata): void
    {
        foreach ($metadata->getReflectionClass()->getAttributes() as $attribute) {
            $this->addEntityValue($agId, $attribute);
        }
        foreach ($metadata->getFieldNames() as $fieldName) {
            foreach ($metadata->getReflectionClass()->getProperty($fieldName)->getAttributes() as $attribute) {
                $this->addEntityFieldValue($agId, $attribute, $fieldName);
            }
        }
        foreach ($metadata->getAssociationNames() as $fieldName) {
            foreach ($metadata->getReflectionClass()->getProperty($fieldName)->getAttributes() as $attribute) {
                $this->addEntityFieldValue($agId, $attribute, $fieldName);
            }
        }
    }

    private function addEntityValue(string $agId, ReflectionAttribute $attribute): void
    {
        $instance = $attribute->newInstance();
        if ($instance instanceof AttributeInterface) {
            $link = &$this->entityAttributes[$agId];
            $this->addPropertyValue($link, $instance->getCode(), $instance->getValue());
        }
    }

    private function addEntityFieldValue(string $agId, ReflectionAttribute $attribute, string $fieldName): void
    {
        $instance = $attribute->newInstance();
        if ($instance instanceof AttributeInterface) {
            $link = &$this->entityFieldAttributes[$agId];
            $this->addPropertyValue($link, $fieldName . '.' . $instance->getCode(), $instance->getValue());
        }
    }

    private function addPropertyValue(array &$link, string $code, mixed $value): void
    {
        foreach (explode('.', $code) as $part) {
            if (!array_key_exists($part, $link)) {
                $link[$part] = [];
            }
            $link = &$link[$part];
        }
        $link = $value;
    }

    private function prepareAgId(ClassMetadata $metadata, int $instanceNumber): string
    {
        return base_convert(
            sha1(
                sprintf(
                    '%s_%s_%s',
                    $this->requestStack->getCurrentRequest()->attributes->get('_route'),
                    $metadata->rootEntityName,
                    $instanceNumber
                )
            ),
            16,
            36
        );
    }

    private function prepareCustomAgId(string $customId): string
    {
        return $this->slugger->slug(u($customId)->normalize()->lower()->toString())->toString();
    }
}
