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

use Doctrine\ORM\EntityManagerInterface;
use F0ska\AutoGridBundle\Builder\EntityBuilder;
use F0ska\AutoGridBundle\Event\DeleteEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteAction extends AbstractAction
{
    private EntityBuilder $entityBuilder;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityBuilder $entityBuilder,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityBuilder = $entityBuilder;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->entityBuilder->loadEntity($parameters);
        $event = new DeleteEvent($entity, $parameters);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $autoGrid->setResponse(new RedirectResponse($parameters->actionUrl('grid')));
    }
}
