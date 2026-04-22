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

class RangeCondition implements FilterConditionInterface
{
    public function apply(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): void
    {
        $valueFrom = $value['from'] ?? null;
        $valueTo = $value['to'] ?? null;
        $hasFrom = $valueFrom !== null && $valueFrom !== '';
        $hasTo = $valueTo !== null && $valueTo !== '';

        if ($hasFrom) {
            $aliasFrom = uniqid('param');
            $qb->andWhere($qb->expr()->gte($column, ':' . $aliasFrom));
            $qb->setParameter($aliasFrom, $valueFrom);
        }

        if ($hasTo) {
            $aliasTo = uniqid('param');
            $qb->andWhere($qb->expr()->lte($column, ':' . $aliasTo));
            $qb->setParameter($aliasTo, $valueTo);
        }
    }
}
