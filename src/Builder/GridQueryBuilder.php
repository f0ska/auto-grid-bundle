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
use F0ska\AutoGridBundle\Service\Provider\FieldValueProvider;

use function Symfony\Component\String\u;

class GridQueryBuilder
{
    /**
     * @var array<string, bool>
     */
    private array $joins;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MetaDataService $metaDataService,
        private readonly FilterConditionListService $conditionList,
        private readonly FieldValueProvider $fieldValueProvider
    ) {
    }

    public function buildGridQuery(Parameters $parameters): Query
    {
        $page = $parameters->view->pagination['page'];
        $limit = $parameters->view->pagination['limit'];

        $builder = $this->buildGenericParts($parameters);
        $this->buildFilters($builder, $parameters);

        $aliases = $builder->getRootAliases();
        $builder->select(reset($aliases));
        $this->addVirtualDqlSelects($builder, $parameters);
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
        $builder->select(reset($aliases));
        $this->addVirtualDqlSelects($builder, $parameters);
        $builder->andWhere(reset($aliases) . ' = :entity');
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
                $column = $this->prepareQueryField($builder, $field->name, $parameters);
                $this->conditionList->get($field->filterCondition)->apply(
                    $builder,
                    $column,
                    $field,
                    $filters[$field->name]
                );
            }
        }
    }

    private function buildOrder(QueryBuilder $builder, Parameters $parameters): void
    {
        $order = $parameters->request['order'] ?? $parameters->attributes['default_sort'] ?? [];
        foreach ($order as $key => $direction) {
            $builder->addOrderBy($this->prepareAliasField($builder, $key, $parameters), $direction);
        }
    }

    private function prepareQueryField(QueryBuilder $builder, string $key, Parameters $parameters): string
    {
        if (isset($parameters->query['virtual_alias_map'][$key])) {
            $field = $parameters->fields[$key];
            $dql = $field->attributes['virtual_column']['dql'];

            $aliases = $builder->getRootAliases();
            $rootAlias = reset($aliases);
            $thisAlias = $rootAlias;
            if ($field->subObject !== null) {
                $thisAlias = $this->prepareField($builder, $key);
                $thisAlias = strstr($thisAlias, '.', true) ?: $thisAlias;
            }

            $dql = str_replace(['{this}', '{root}'], [$thisAlias, $rootAlias], $dql);

            return sprintf('(%s)', $dql);
        }

        return $this->prepareField($builder, $key);
    }

    private function prepareAliasField(QueryBuilder $builder, string $key, Parameters $parameters): string
    {
        if (isset($parameters->query['virtual_alias_map'][$key])) {
            return $parameters->query['virtual_alias_map'][$key];
        }

        return $this->prepareField($builder, $key);
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

    private function addVirtualDqlSelects(QueryBuilder $builder, Parameters $parameters): void
    {
        if (empty($parameters->query['has_dql'])) {
            return;
        }

        $aliases = $builder->getRootAliases();
        $rootAlias = reset($aliases);

        foreach ($parameters->query['virtual_alias_map'] as $fieldName => $alias) {
            $field = $parameters->fields[$fieldName];
            $dql = $field->attributes['virtual_column']['dql'];

            $thisAlias = $rootAlias;
            if ($field->subObject !== null) {
                $thisAlias = $this->prepareField($builder, $fieldName);
                $thisAlias = strstr($thisAlias, '.', true) ?: $thisAlias;
            }

            $dql = str_replace(['{this}', '{root}'], [$thisAlias, $rootAlias], $dql);

            $builder->addSelect(sprintf('(%s) AS %s', $dql, $alias));
        }
    }

    public function getHydratedResult(Query $query, Parameters $parameters): array
    {
        $results = $query->getResult();
        return $this->hydrateVirtualDql($parameters, $results);
    }

    public function getOneOrNullHydratedResult(Query $query, Parameters $parameters): ?object
    {
        $result = $query->getOneOrNullResult();
        if ($result === null) {
            return null;
        }
        $hydrated = $this->hydrateVirtualDql($parameters, [$result]);
        return $hydrated[0] ?? null;
    }

    private function hydrateVirtualDql(Parameters $parameters, array $results): array
    {
        if (empty($results) || empty($parameters->query['has_dql'])) {
            return $results;
        }

        foreach ($results as &$entity) {
            if (is_array($entity) && isset($entity[0]) && $entity[0] instanceof $parameters->attributes['entity']) {
                $entityObj = $entity[0];

                foreach ($parameters->query['virtual_alias_map'] as $fieldName => $alias) {
                    if (array_key_exists($alias, $entity)) {
                        $this->fieldValueProvider->setValue(
                            $entityObj,
                            $parameters->fields[$fieldName],
                            $entity[$alias]
                        );
                    }
                }

                $entity = $entityObj;
            }
        }

        return $results;
    }
}
