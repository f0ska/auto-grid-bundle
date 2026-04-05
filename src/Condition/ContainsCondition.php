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

class ContainsCondition implements FilterConditionInterface
{
    public function apply(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): void
    {
        $values = is_array($value) ? $value : [$value];
        $conditions = [];
        foreach ($values as $v) {
            $alias = uniqid('param');
            $conditions[] = $qb->expr()->like($column, ':' . $alias);
            $qb->setParameter($alias, '%' . $v . '%');
        }
        $qb->andWhere($qb->expr()->orX(...$conditions));
    }
}
