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

    public function __construct(EntityManagerInterface $entityManager, MetaDataService $metaDataService)
    {
        $this->entityManager = $entityManager;
        $this->metaDataService = $metaDataService;
    }

    public function getNewEntity(Parameters $parameters): object
    {
        $class = $this->getEntityClass($parameters);
        return new $class();
    }

    public function loadEntity(Parameters $parameters): object
    {
        $entityId = $parameters->request['id'] ?? null;
        if (empty($entityId)) {
            throw new ActionException('Not found');
        }
        $class = $this->getEntityClass($parameters);
        $entity = $this->entityManager->getRepository($class)->find($entityId);
        if (empty($entity)) {
            throw new ActionException('Not found');
        }
        if (!method_exists($entity, 'getId')) {
            throw new ActionException('ID getter is missing');
        }
        if (!is_int($entity->getId())) {
            throw new ActionException('ID must be an integer');
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
}
