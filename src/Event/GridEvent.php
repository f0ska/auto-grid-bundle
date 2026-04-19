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

namespace F0ska\AutoGridBundle\Event;

use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Contracts\EventDispatcher\Event;

final class GridEvent extends Event
{
    public const EVENT_NAME = 'f0ska.autogrid.entity.grid';

    private array $entities;
    private int $count;
    private Parameters $parameters;

    public function __construct(array $entities, int $count, Parameters $parameters)
    {
        $this->parameters = $parameters;
        $this->entities = $entities;
        $this->count = $count;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}
