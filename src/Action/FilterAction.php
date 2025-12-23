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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterAction extends AbstractAction
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
        foreach ($parameters->view->filterForms as $fieldName => $form) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                $filter[$fieldName] = $form->get($fieldName)->getViewData();
            }
        }
        $autoGrid->setResponse(new RedirectResponse($parameters->actionUrl('grid', ['filter' => $filter])));
    }

    public function isIdRequired(): bool
    {
        return false;
    }
}
