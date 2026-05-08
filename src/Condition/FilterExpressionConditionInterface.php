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

interface FilterExpressionConditionInterface extends FilterConditionInterface
{
    public function buildExpression(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): mixed;
}
