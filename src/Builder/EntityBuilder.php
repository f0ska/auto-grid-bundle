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
use F0ska\AutoGridBundle\Exception\GridEntityNotFoundException;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\MetaDataService;
use LogicException;

class EntityBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MetaDataService $metaDataService,
        private readonly GridQueryBuilder $queryBuilder
    ) {
    }

    public function getNewEntity(Parameters $parameters): object
    {
        $class = $this->getEntityClass($parameters);
        return new $class();
    }

    public function loadEntity(Parameters $parameters): object
    {
        if (empty($parameters->request['id'])) {
            throw new GridEntityNotFoundException();
        }
        $entity = $this->findEntity($parameters);
        if (empty($entity)) {
            throw new GridEntityNotFoundException();
        }
        if (!method_exists($entity, 'getId')) {
            throw new LogicException('ID getter is missing');
        }
        if (!is_int($entity->getId())) {
            throw new LogicException('ID must be an integer');
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
        $entityId = $parameters->request['id'] ?? null;
        $class = $this->getEntityClass($parameters);
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->find($entityId);
        if (!empty($parameters->query['expression']) || !empty($parameters->query['has_dql'])) {
            $query = $this->queryBuilder->buildEntityQuery($parameters, $entity);
            return $this->queryBuilder->getOneOrNullHydratedResult($query, $parameters);
        }
        return $entity;
    }
}
