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

namespace F0ska\AutoGridBundle\Action;

use F0ska\AutoGridBundle\Event\ErrorEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ErrorAction extends AbstractAction
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function isRestrictable(): bool
    {
        return false;
    }

    public function isIdRequired(): bool
    {
        return false;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $event = new ErrorEvent($parameters);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());
        $autoGrid->setTemplate($parameters->getActionTemplate('error'));
        $autoGrid->setContext($parameters->render());
    }
}
