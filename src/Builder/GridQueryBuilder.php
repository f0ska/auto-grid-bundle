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
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\FilterConditionListService;
use F0ska\AutoGridBundle\Service\MetaDataService;

use function Symfony\Component\String\u;

class GridQueryBuilder
{
    /**
     * @var array<string, bool>
     */
    private array $joins;
    private EntityManagerInterface $entityManager;
    private MetaDataService $metaDataService;
    private FilterConditionListService $conditionList;

    public function __construct(
        EntityManagerInterface $entityManager,
        MetaDataService $metaDataService,
        FilterConditionListService $conditionList
    ) {
        $this->entityManager = $entityManager;
        $this->metaDataService = $metaDataService;
        $this->conditionList = $conditionList;
    }

    public function buildGridQuery(Parameters $parameters): Query
    {
        $page = $parameters->view->pagination['page'];
        $limit = $parameters->view->pagination['limit'];

        $builder = $this->buildGenericParts($parameters);
        $this->buildFilters($builder, $parameters);
        $aliases = $builder->getRootAliases();
        $builder->select(reset($aliases));
        $builder->setMaxResults($limit);
        $builder->setFirstResult(($page - 1) * $limit);
        $this->buildOrder($builder, $parameters);

        return $builder->getQuery();
    }

    public function buildGridCountQuery(Parameters $parameters): Query
    {
        $builder = $this->buildGenericParts($parameters);
        $this->buildFilters($builder, $parameters);
        $aliases = $builder->getRootAliases();
        $builder->select(sprintf('COUNT(DISTINCT %s.id)', reset($aliases)));
        return $builder->getQuery();
    }

    public function buildEntityQuery(Parameters $parameters, object $entity): Query
    {
        $builder = $this->buildGenericParts($parameters);
        $this->buildFilters($builder, $parameters);
        $aliases = $builder->getRootAliases();
        $alias = reset($aliases);
        $builder->select($alias);
        $builder->andWhere($alias . ' = :entity');
        $builder->setParameter('entity', $entity);
        return $builder->getQuery();
    }

    public function buildGenericParts(Parameters $parameters): QueryBuilder
    {
        $this->joins = [];
        $agId = $parameters->agId;
        $metadata = $this->metaDataService->getMetadata($agId);
        $builder = $this->entityManager->createQueryBuilder();
        $alias = u($metadata->rootEntityName)->afterLast('\\')->camel()->toString();
        $builder->from($metadata->rootEntityName, $alias);

        if ($parameters->query['expression']) {
            $builder->andWhere($parameters->query['expression']);
            if ($parameters->query['parameters']) {
                $builder->setParameters(clone $parameters->query['parameters']);
            }
        }

        return $builder;
    }

    private function buildFilters(QueryBuilder $builder, Parameters $parameters): void
    {
        $filters = $parameters->request['filter'] ?? [];
        if (empty($filters)) {
            return;
        }
        foreach ($parameters->fields as $field) {
            if (isset($filters[$field->name]) && $field->filterCondition !== null) {
                $column = $this->prepareField($builder, $field->name);
                $this->conditionList->get($field->filterCondition)->apply($builder, $column, $field, $filters[$field->name]);
            }
        }
    }

    private function buildOrder(QueryBuilder $builder, Parameters $parameters): void
    {
        $order = $parameters->request['order'] ?? $parameters->attributes['default_sort'] ?? [];
        foreach ($order as $key => $direction) {
            $builder->addOrderBy($this->prepareField($builder, $key), $direction);
        }
    }

    private function prepareField(QueryBuilder $builder, string $key): string
    {
        $aliases = $builder->getRootAliases();
        $key = str_replace(':', '.', $key);
        $rootAlias = reset($aliases);
        if (!str_contains($key, '.')) {
            return $rootAlias . '.' . $key;
        }

        $alias = strstr($key, '.', true);
        if (is_string($alias) && !isset($this->joins[$alias])) {
            $this->joins[$alias] = true;
            $builder->leftJoin($rootAlias . '.' . $alias, $alias);
        }

        return $key;
    }
}
