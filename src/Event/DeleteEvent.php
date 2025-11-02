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

final class DeleteEvent extends Event
{
    public const EVENT_NAME = 'f0ska.autogrid.entity.delete';

    private object $entity;
    private Parameters $parameters;

    public function __construct(object $entity, Parameters $parameters)
    {
        $this->entity = $entity;
        $this->parameters = $parameters;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}
