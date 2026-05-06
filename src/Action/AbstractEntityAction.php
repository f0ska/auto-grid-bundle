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

use F0ska\AutoGridBundle\Builder\EntityBuilder;
use F0ska\AutoGridBundle\Exception\GridAccessDeniedException;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\RowActionPermissionService;

abstract class AbstractEntityAction extends AbstractAction
{
    public function __construct(
        protected readonly EntityBuilder $entityBuilder,
        private readonly RowActionPermissionService $rowActionPermissionService
    ) {
    }

    public function isIdRequired(): bool
    {
        return true;
    }

    protected function loadEntityForAction(Parameters $parameters): object
    {
        $entity = $this->entityBuilder->loadEntity($parameters);
        $this->validateEntity($entity, $parameters);

        return $entity;
    }

    protected function validateEntity(object $entity, Parameters $parameters): void
    {
        if (!$this->rowActionPermissionService->isGranted($this->getCode(), $entity, $parameters)) {
            throw new GridAccessDeniedException();
        }
    }
}
