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
use F0ska\AutoGridBundle\Exception\InvalidGridParameterException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\FormFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteAction extends AbstractAction
{
    public function __construct(
        private readonly EntityBuilder $entityBuilder,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly FormFacade $formFacade,
        private readonly RequestStack $requestStack
    ) {
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $form = $this->formFacade->buildDeleteActionForm($parameters);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new InvalidGridParameterException('Bad Request');
        }

        $parameters->request['id'] = (int) $form->get('id')->getData();
        $entity = $this->entityBuilder->loadEntity($parameters);
        $event = new DeleteEvent($entity, $parameters);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $autoGrid->setResponse(new RedirectResponse($parameters->actionUrl('grid')));
    }
}
