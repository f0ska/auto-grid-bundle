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
use F0ska\AutoGridBundle\Model\FormProcessResult;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FormProcessorService
{
    private FormFacade $formFacade;
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        FormFacade $formFacade,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->formFacade = $formFacade;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function process(object $entity, AutoGrid $autoGrid, Parameters $parameters): FormProcessResult
    {
        $form = $this->formFacade->buildEntityForm($entity, $parameters);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new SaveEvent($entity, $form, $parameters);
            $this->dispatcher->dispatch($event, $event::EVENT_NAME);
            $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return new FormProcessResult(true, $entity, $form);
        }

        return new FormProcessResult(false, $entity, $form);
    }
}
