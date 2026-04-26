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

final class EntityEvent extends Event
{
    public const CREATE_EVENT_NAME = 'f0ska.autogrid.entity.create';
    public const EDIT_EVENT_NAME = 'f0ska.autogrid.entity.edit';
    public const VIEW_EVENT_NAME = 'f0ska.autogrid.entity.view';

    public function __construct(
        private readonly object $entity,
        private readonly Parameters $parameters
    )
    {
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
