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
use F0ska\AutoGridBundle\Service\FormProcessorService;
use F0ska\AutoGridBundle\Service\RedirectService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateAction extends AbstractAction
{
    public function __construct(
        private readonly EntityBuilder $entityBuilder,
        private readonly FormProcessorService $formProcessor,
        private readonly RedirectService $redirectService,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->entityBuilder->getNewEntity($parameters);
        $event = new EntityEvent($entity, $parameters);
        $this->dispatcher->dispatch($event, EntityEvent::CREATE_EVENT_NAME);
        $this->dispatcher->dispatch($event, EntityEvent::CREATE_EVENT_NAME . '.' . $autoGrid->getId());
        $result = $this->formProcessor->process($entity, $autoGrid, $parameters);

        if ($result->isSuccess()) {
            $autoGrid->setResponse(
                $this->redirectService->getSubmitRedirect(
                    $result->getForm(),
                    $result->getEntity()->getId(),
                    $parameters
                )
            );
            return;
        }

        $autoGrid->setTemplate($parameters->getActionTemplate('form'));
        $autoGrid->setContext(
            $parameters->render(['entity' => $result->getEntity(), 'form' => $result->getForm()->createView()])
        );
    }
}
