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

use Doctrine\ORM\Mapping\ToManyAssociationMapping;
use Doctrine\ORM\QueryBuilder;
use F0ska\AutoGridBundle\Model\FieldParameter;

class AssociationCondition implements FilterExpressionConditionInterface
{
    public function apply(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): void
    {
        $qb->andWhere($this->buildExpression($qb, $column, $field, $value));
    }

    public function buildExpression(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): mixed
    {
        $alias = uniqid('param');

        if ($field->associationMapping instanceof ToManyAssociationMapping) {
            $joinAlias = substr(strrchr($column, '.'), 1);
            $qb->innerJoin($column, $joinAlias);
            $column = $joinAlias;
        }

        $qb->setParameter($alias, $value);

        return $qb->expr()->in($column, ':' . $alias);
    }
}
