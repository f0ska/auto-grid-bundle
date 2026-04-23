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

use F0ska\AutoGridBundle\Model\Parameters;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectService
{
    public function getSubmitRedirect(FormInterface $form, int $entityId, Parameters $parameters): RedirectResponse
    {
        $actions = !$form->isValid() ? [$parameters->action] : [
            $form->getExtraData()['redirect'] ?? null,
            $parameters->request['redirect'] ?? null,
            $parameters->attributes['redirect_on_submit'] ?? null,
            'view',
            'grid',
            'edit',
            'create',
        ];

        foreach ($actions as $action) {
            if (!empty($action) && $parameters->isAllowed($action)) {
                return new RedirectResponse($parameters->actionUrl($action, ['id' => $entityId]));
            }
        }

        throw new RuntimeException('No allowed redirect target found after form submit.');
    }
}
