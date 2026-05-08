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

namespace F0ska\AutoGridBundle\Service;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class QueryFieldResolver
{
    public function resolve(QueryBuilder $builder, string $field): string
    {
        $aliases = $builder->getRootAliases();
        $rootAlias = reset($aliases);
        $field = str_replace(':', '.', $field);

        if (!str_contains($field, '.')) {
            return $rootAlias . '.' . $field;
        }

        $alias = strstr($field, '.', true);
        if (is_string($alias) && !$this->hasJoin($builder, $alias)) {
            $builder->leftJoin($rootAlias . '.' . $alias, $alias);
        }

        return $field;
    }

    private function hasJoin(QueryBuilder $builder, string $alias): bool
    {
        foreach ($builder->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if ($join instanceof Join && $join->getAlias() === $alias) {
                    return true;
                }
            }
        }

        return false;
    }
}
