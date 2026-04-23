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
use F0ska\AutoGridBundle\Model\AttributeCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

class MetaDataService
{
    private int $instanceCount = 0;
    private int $subInstanceCount = 0;

    /** @var array<string, array{metadata: ClassMetadata, attributes: AttributeCollection, instanceAttributes: array}> */
    private array $instanceCache = [];

    private EntityManagerInterface $entityManager;
    private ConfigurationService $configuration;
    private RequestStack $requestStack;
    private SluggerInterface $slugger;
    private AttributeParserService $attributeParser;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigurationService $configuration,
        RequestStack $requestStack,
        SluggerInterface $slugger,
        AttributeParserService $attributeParser
    ) {
        $this->entityManager = $entityManager;
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
        $this->slugger = $slugger;
        $this->attributeParser = $attributeParser;
    }

    public function add(string $entityClass, ?string $customId = null, bool $isSubInstance = false): string
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $instanceCount = $isSubInstance ? ++$this->subInstanceCount : ++$this->instanceCount;
        $agId = $customId ? $this->prepareCustomAgId($customId) : $this->prepareAgId($metadata, $instanceCount);

        $this->instanceCache[$agId] = [
            'metadata' => $metadata,
            'attributes' => $this->attributeParser->parse($entityClass),
        ];
        $this->instanceCache[$agId]['instanceAttributes'] = [];

        if (!$isSubInstance) {
            $this->instanceCache[$agId]['instanceAttributes']['container_id'] = sprintf(
                '%s%s',
                $this->configuration->getFriendlyId(),
                $instanceCount > 1 ? $instanceCount : ''
            );
            $this->instanceCache[$agId]['instanceAttributes']['container_class'] = $this->configuration->getFriendlyId();
        }

        return $agId;
    }

    public function getMetadata(string $agId): ClassMetadata
    {
        return $this->instanceCache[$agId]['metadata'];
    }

    public function getEntityAttributes(string $agId): array
    {
        $collection = $this->instanceCache[$agId]['attributes'];
        return array_merge($collection->getEntityAttributes(), $this->instanceCache[$agId]['instanceAttributes']);
    }

    public function getEntityAttribute(string $agId, string $key): mixed
    {
        $data = $this->getEntityAttributes($agId);
        foreach (explode('.', $key) as $part) {
            $data = $data[$part] ?? null;
        }
        return $data;
    }

    public function getEntityFieldAttributes(string $agId, string $fieldName): array
    {
        $collection = $this->instanceCache[$agId]['attributes'];
        return $collection->getFieldAttributes()[$fieldName] ?? [];
    }

    public function getEntityFieldAttribute(string $agId, string $fieldName, string $key): mixed
    {
        $data = $this->getEntityFieldAttributes($agId, $fieldName);
        foreach (explode('.', $key) as $part) {
            $data = $data[$part] ?? null;
        }
        return $data;
    }

    public function getPureVirtualFieldNames(string $agId): array
    {
        $collection = $this->instanceCache[$agId]['attributes'];
        return $collection->getPureVirtualFieldNames();
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
