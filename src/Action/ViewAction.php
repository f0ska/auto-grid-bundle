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
use F0ska\AutoGridBundle\Event\EntityEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\RowActionPermissionService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ViewAction extends AbstractEntityAction
{
    public function __construct(
        EntityBuilder $entityBuilder,
        RowActionPermissionService $rowActionPermissionService,
        private readonly EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($entityBuilder, $rowActionPermissionService);
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->loadEntityForAction($parameters);
        $event = new EntityEvent($entity, $parameters);
        $this->dispatcher->dispatch($event, EntityEvent::VIEW_EVENT_NAME);
        $this->dispatcher->dispatch($event, EntityEvent::VIEW_EVENT_NAME . '.' . $autoGrid->getId());
        $autoGrid->setTemplate($parameters->getActionTemplate('view'));
        $autoGrid->setContext($parameters->render(['entity' => $entity]));
    }
}
