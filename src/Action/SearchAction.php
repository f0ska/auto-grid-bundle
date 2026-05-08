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

use F0ska\AutoGridBundle\Exception\GridAccessDeniedException;
use F0ska\AutoGridBundle\Exception\InvalidGridParameterException;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchAction extends AbstractAction
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $form = $parameters->view->searchForm;
        if ($form === null) {
            throw new GridAccessDeniedException('Not Allowed: search form is not available');
        }

        $form->handleRequest($this->requestStack->getCurrentRequest());
        if (!$form->isSubmitted()) {
            throw new InvalidGridParameterException('Invalid request parameter: invalid search form submission');
        }

        if (!$form->isValid()) {
            throw new InvalidGridParameterException($this->getFirstFormErrorMessage($form));
        }

        $term = trim((string) $form->get('term')->getData());
        $search = $term === '' ? null : ['term' => $term];

        $autoGrid->setResponse(new RedirectResponse($parameters->actionUrl('grid', [
            'search' => $search,
            'page' => null,
        ])));
    }

    private function getFirstFormErrorMessage(FormInterface $form): string
    {
        $error = $form->getErrors(true)->current();

        return $error instanceof FormError
            ? $error->getMessage()
            : 'Invalid request parameter: invalid search form submission';
    }
}
