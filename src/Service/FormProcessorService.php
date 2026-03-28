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

namespace F0ska\AutoGridBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use F0ska\AutoGridBundle\Event\SaveEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FormProcessorService
{
    private GridFormFacade $gridFormFacade;
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;
    private RedirectService $redirectService;

    public function __construct(
        GridFormFacade $gridFormFacade,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        RedirectService $redirectService
    ) {
        $this->gridFormFacade = $gridFormFacade;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->redirectService = $redirectService;
    }

    public function process(object $entity, AutoGrid $autoGrid, Parameters $parameters): void
    {
        $form = $this->gridFormFacade->buildEntityForm($entity, $parameters);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new SaveEvent($entity, $form, $parameters);
            $this->dispatcher->dispatch($event, $event::EVENT_NAME);
            $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $autoGrid->setResponse($this->redirectService->getSubmitRedirect($form, $entity->getId(), $parameters));
            return;
        }

        $autoGrid->setTemplate($parameters->getActionTemplate('form'));
        $autoGrid->setContext($parameters->render(['entity' => $entity, 'form' => $form->createView()]));
    }
}
