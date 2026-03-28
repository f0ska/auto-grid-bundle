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

namespace F0ska\AutoGridBundle\Builder;

use Doctrine\ORM\EntityManagerInterface;
use F0ska\AutoGridBundle\Exception\ActionException;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\MetaDataService;

class EntityBuilder
{
    private EntityManagerInterface $entityManager;
    private MetaDataService $metaDataService;
    private GridQueryBuilder $queryBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        MetaDataService $metaDataService,
        GridQueryBuilder $queryBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->metaDataService = $metaDataService;
        $this->queryBuilder = $queryBuilder;
    }

    public function getNewEntity(Parameters $parameters): object
    {
        $class = $this->getEntityClass($parameters);
        return new $class();
    }

    public function loadEntity(Parameters $parameters): object
    {
        $entity = $this->findEntity($parameters);
        if (empty($entity)) {
            throw new ActionException('Not found');
        }
        return $entity;
    }

    /**
     * @param Parameters $parameters
     * @return class-string
     */
    private function getEntityClass(Parameters $parameters): string
    {
        $agId = $parameters->agId;
        $metadata = $this->metaDataService->getMetadata($agId);
        return $metadata->rootEntityName;
    }

    private function findEntity(Parameters $parameters): ?object
    {
        $class = $this->getEntityClass($parameters);
        $metadata = $this->entityManager->getClassMetadata($class);
        $identifier = $this->getIdentifierFromRequest($metadata->getIdentifierFieldNames(), $parameters->request);

        if (empty($identifier)) {
            return null;
        }

        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->find($identifier);

        if ($entity && !empty($parameters->query['expression'])) {
            $query = $this->queryBuilder->buildEntityQuery($parameters, $entity);
            return $query->getOneOrNullResult();
        }

        return $entity;
    }

    private function getIdentifierFromRequest(array $identifierFields, array $requestParams): mixed
    {
        $identifier = [];
        foreach ($identifierFields as $field) {
            if (!isset($requestParams[$field])) {
                return null;
            }
            $identifier[$field] = $requestParams[$field];
        }

        if (count($identifier) === 1) {
            return reset($identifier);
        }

        return $identifier;
    }
}
