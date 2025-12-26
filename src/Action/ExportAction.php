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

use F0ska\AutoGridBundle\Builder\FormBuilder;
use F0ska\AutoGridBundle\Builder\GridQueryBuilder;
use F0ska\AutoGridBundle\Event\ExportEvent;
use F0ska\AutoGridBundle\Exception\ActionException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExportAction extends AbstractAction
{
    private GridQueryBuilder $gridQueryBuilder;
    private FormBuilder $formBuilder;
    private RequestStack $requestStack;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        GridQueryBuilder $gridQueryBuilder,
        FormBuilder $formBuilder,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher
    ) {
        $this->gridQueryBuilder = $gridQueryBuilder;
        $this->formBuilder = $formBuilder;
        $this->requestStack = $requestStack;
        $this->dispatcher = $dispatcher;
    }

    public function isIdRequired(): bool
    {
        return false;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $form = $this->formBuilder->buildExportActionForm($parameters);
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

        throw new ActionException('Not Allowed');
    }
}
