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

use F0ska\AutoGridBundle\Event\MassEvent;
use F0ska\AutoGridBundle\Exception\GridAccessDeniedException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\FormFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MassAction extends AbstractAction
{
    public function __construct(
        private readonly FormFacade $formFacade,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $form = $this->formFacade->buildMassActionForm($parameters);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();
            $ids = array_map('intval', $form->get('ids')->getData());
            $event = new MassEvent($code, $ids, $parameters);
            $this->dispatcher->dispatch($event, MassEvent::EVENT_NAME);
            $this->dispatcher->dispatch($event, MassEvent::EVENT_NAME . '.' . $code);
            $this->dispatcher->dispatch($event, MassEvent::EVENT_NAME . '.' . $autoGrid->getId());
            $url = $event->getRedirectUrl() ?? $parameters->actionUrl('grid');
            $autoGrid->setResponse(new RedirectResponse($url));
            return;
        }

        throw new GridAccessDeniedException();
    }
}
