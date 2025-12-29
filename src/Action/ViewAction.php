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

use F0ska\AutoGridBundle\Builder\EntityBuilder;
use F0ska\AutoGridBundle\Event\ViewEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ViewAction extends AbstractAction
{
    private EntityBuilder $entityBuilder;
    private EventDispatcherInterface $dispatcher;

    public function __construct(EntityBuilder $uiEntityBuilder, EventDispatcherInterface $dispatcher)
    {
        $this->entityBuilder = $uiEntityBuilder;
        $this->dispatcher = $dispatcher;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->entityBuilder->loadEntity($parameters);
        $event = new ViewEvent($entity, $parameters);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());
        $autoGrid->setTemplate($parameters->getActionTemplate('view'));
        $autoGrid->setContext($parameters->render(['entity' => $entity]));
    }
}
