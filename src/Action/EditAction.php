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
use F0ska\AutoGridBundle\Builder\FormBuilder;
use F0ska\AutoGridBundle\Event\SaveEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EditAction extends AbstractAction
{
    protected EntityBuilder $entityBuilder;
    protected FormBuilder $formBuilder;
    protected RequestStack $requestStack;
    protected EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        EntityBuilder $entityBuilder,
        FormBuilder $formBuilder,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityBuilder = $entityBuilder;
        $this->formBuilder = $formBuilder;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->entityBuilder->loadEntity($parameters);
        $this->processForm($entity, $autoGrid, $parameters);
    }

    protected function processForm(object $entity, AutoGrid $autoGrid, Parameters $parameters): void
    {
        $form = $this->formBuilder->buildForm($entity, $parameters);
        $form->handleRequest($this->requestStack->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dispatcher->dispatch(new SaveEvent($entity, $form, $parameters), SaveEvent::EVENT_NAME);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $autoGrid->setResponse($this->formBuilder->getSubmitRedirect($form, $entity->getId(), $parameters));
            return;
        }

        $autoGrid->setTemplate($parameters->getTemplate('form'));
        $autoGrid->setContext($parameters->render(['entity' => $entity, 'form' => $form->createView()]));
    }
}
