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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ToManyAssociationMapping;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\AttributeService;
use F0ska\AutoGridBundle\Service\LegacyService;
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

    public function __construct(EntityManagerInterface $entityManager, MetaDataService $metaDataService)
    {
        $this->entityManager = $entityManager;
        $this->metaDataService = $metaDataService;
    }

    public function buildGridQuery(Parameters $parameters): Query
    {
        $page = $parameters->view->pagination['page'];
        $limit = $parameters->view->pagination['limit'];

        $builder = $this->buildGenericParts($parameters);
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
        $aliases = $builder->getRootAliases();
        $builder->select(sprintf('COUNT(DISTINCT %s.id)', reset($aliases)));
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
                $builder->setParameters($parameters->query['parameters']);
            }
        }

        $this->buildFilters($builder, $parameters);

        return $builder;
    }

    private function buildFilters(QueryBuilder $builder, Parameters $parameters): void
    {
        $filters = $parameters->request['filter'] ?? [];
        if (empty($filters)) {
            return;
        }
        foreach ($parameters->fields as $field) {
            if (isset($filters[$field->name])) {
                if (!empty($field->attributes['range_filter'])) {
                    $this->buildFieldRangeFilter($builder, $field, $parameters);
                    continue;
                }
                switch ($field->mappingType) {
                    case AttributeService::MAPPING_ASSOC:
                        $this->buildAssociatedFieldFilter($builder, $field, $parameters);
                        break;
                    default:
                        $this->buildFieldFilter($builder, $field, $parameters);
                        break;
                }
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

    private function buildFieldFilter(QueryBuilder $builder, FieldParameter $field, Parameters $parameters): void
    {
        $column = $this->prepareField($builder, $field->name);
        $value = $parameters->request['filter'][$field->name];
        $alias = uniqid('param');
        switch ($field->fieldMapping?->type) {
            case Types::TEXT:
            case Types::JSON:
            case Types::SIMPLE_ARRAY:
            case LegacyService::TYPES_ARRAY:
            case LegacyService::TYPES_OBJECT:
                $exp = $builder->expr()->like($column, ':' . $alias);
                $value = '%' . $value . '%';
                break;
            case Types::STRING:
                $exp = $builder->expr()->like($column, ':' . $alias);
                $value = $value . '%';
                break;
            default:
                $exp = $builder->expr()->in($column, ':' . $alias);
                break;
        }
        $builder->andWhere($exp);
        $builder->setParameter($alias, $value);
    }

    private function buildAssociatedFieldFilter(
        QueryBuilder $builder,
        FieldParameter $field,
        Parameters $parameters
    ): void {
        $multiple = $field->associationMapping instanceof ToManyAssociationMapping;
        $column = $this->prepareField($builder, $field->name);
        $value = $parameters->request['filter'][$field->name];
        $alias = uniqid('param');

        if ($multiple) {
            $builder->innerJoin($column, $field->name);
            $column = $field->name;
        }

        $exp = $builder->expr()->in($column, ':' . $alias);
        $builder->andWhere($exp);
        $builder->setParameter($alias, $value);
    }

    private function buildFieldRangeFilter(QueryBuilder $builder, FieldParameter $field, Parameters $parameters): void
    {
        $column = $this->prepareField($builder, $field->name);
        $values = $parameters->request['filter'][$field->name];
        $valueFrom = $values['from'] ?? null;
        $valueTo = $values['to'] ?? null;
        $aliasFrom = uniqid('param');
        $aliasTo = $aliasFrom . 'To';
        if ($valueFrom) {
            $builder->andWhere($builder->expr()->gte($column, ':' . $aliasFrom));
            $builder->setParameter($aliasFrom, $valueFrom);
        }
        if ($valueTo) {
            $builder->andWhere($builder->expr()->lte($column, ':' . $aliasTo));
            $builder->setParameter($aliasTo, $valueTo);
        }
    }
}
