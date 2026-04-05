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

use F0ska\AutoGridBundle\Condition\FilterConditionInterface;

class FilterConditionListService
{
    /**
     * @var FilterConditionInterface[]
     */
    private array $conditions = [];

    public function __construct(iterable $conditions)
    {
        foreach ($conditions as $condition) {
            $this->conditions[get_class($condition)] = $condition;
        }
    }

    public function get(string $class): FilterConditionInterface
    {
        return $this->conditions[$class]
            ?? throw new \RuntimeException(sprintf('Filter condition "%s" is not registered. Tag it with "autogrid.filter_condition".', $class));
    }
}
