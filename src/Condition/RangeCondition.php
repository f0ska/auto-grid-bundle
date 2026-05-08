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

namespace F0ska\AutoGridBundle\Condition;

use Doctrine\ORM\QueryBuilder;
use F0ska\AutoGridBundle\Model\FieldParameter;

class RangeCondition implements FilterExpressionConditionInterface
{
    public function apply(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): void
    {
        $expression = $this->buildExpression($qb, $column, $field, $value);
        if ($expression !== null) {
            $qb->andWhere($expression);
        }
    }

    public function buildExpression(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): mixed
    {
        $valueFrom = $value['from'] ?? null;
        $valueTo = $value['to'] ?? null;
        $hasFrom = $valueFrom !== null && $valueFrom !== '';
        $hasTo = $valueTo !== null && $valueTo !== '';
        $conditions = [];

        if ($hasFrom) {
            $aliasFrom = uniqid('param');
            $conditions[] = $qb->expr()->gte($column, ':' . $aliasFrom);
            $qb->setParameter($aliasFrom, $valueFrom);
        }

        if ($hasTo) {
            $aliasTo = uniqid('param');
            $conditions[] = $qb->expr()->lte($column, ':' . $aliasTo);
            $qb->setParameter($aliasTo, $valueTo);
        }

        return $conditions === [] ? null : $qb->expr()->andX(...$conditions);
    }
}
