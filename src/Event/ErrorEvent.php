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

final class ErrorEvent extends Event
{
    public const EVENT_NAME = 'f0ska.autogrid.error.show';

    private Parameters $parameters;

    public function __construct(Parameters $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}
