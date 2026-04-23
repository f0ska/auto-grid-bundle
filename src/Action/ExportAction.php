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

use F0ska\AutoGridBundle\Builder\GridQueryBuilder;
use F0ska\AutoGridBundle\Event\ExportEvent;
use F0ska\AutoGridBundle\Exception\GridAccessDeniedException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\FormFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExportAction extends AbstractAction
{
    private GridQueryBuilder $gridQueryBuilder;
    private FormFacade $formFacade;
    private RequestStack $requestStack;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        GridQueryBuilder $gridQueryBuilder,
        FormFacade $formFacade,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher
    ) {
        $this->gridQueryBuilder = $gridQueryBuilder;
        $this->formFacade = $formFacade;
        $this->requestStack = $requestStack;
        $this->dispatcher = $dispatcher;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $form = $this->formFacade->buildExportActionForm($parameters);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();
            $event = new ExportEvent($code, $this->gridQueryBuilder->buildGenericParts($parameters), $parameters);
            $this->dispatcher->dispatch($event, ExportEvent::EVENT_NAME);
            $this->dispatcher->dispatch($event, ExportEvent::EVENT_NAME . '.' . $code);
            $this->dispatcher->dispatch($event, ExportEvent::EVENT_NAME . '.' . $autoGrid->getId());
            $url = $event->getRedirectUrl() ?? $parameters->actionUrl('grid');
            $autoGrid->setResponse(new RedirectResponse($url));
            return;
        }

        throw new GridAccessDeniedException();
    }
}
