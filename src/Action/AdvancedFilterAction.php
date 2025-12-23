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

use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class AdvancedFilterAction extends AbstractAction
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $filter = [];
        $request = $this->requestStack->getCurrentRequest();
        $form = $parameters->view->advancedFilterForm;
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $filter = $this->getFilter($form);
        }
        $autoGrid->setResponse(new RedirectResponse($parameters->actionUrl('grid', ['filter' => $filter])));
    }

    public function isIdRequired(): bool
    {
        return false;
    }

    private function getFilter(FormInterface $form): array
    {
        $filter = [];
        foreach ($form->all() as $item) {
            $filter[$item->getName()] = $item->getViewData();
        }
        return $filter;
    }
}
