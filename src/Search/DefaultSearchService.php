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

namespace F0ska\AutoGridBundle\Search;

use Doctrine\ORM\QueryBuilder;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\QueryFieldResolver;

class DefaultSearchService implements SearchServiceInterface
{
    public function __construct(private readonly QueryFieldResolver $fieldResolver)
    {
    }

    public function apply(QueryBuilder $builder, string $term, array $fields, Parameters $parameters): void
    {
        $expressions = [];
        foreach ($fields as $index => $field) {
            $parameter = 'search_' . $index;
            $expressions[] = sprintf('%s LIKE :%s', $this->fieldResolver->resolve($builder, $field), $parameter);
            $builder->setParameter($parameter, '%' . $term . '%');
        }

        if ($expressions === []) {
            return;
        }

        $builder->andWhere($builder->expr()->orX(...$expressions));
    }
}
