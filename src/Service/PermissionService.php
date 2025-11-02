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

use F0ska\AutoGridBundle\Action\ActionInterface;
use F0ska\AutoGridBundle\Model\Permission;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PermissionService
{
    private MetaDataService $metaDataService;
    /** @var ActionInterface[] */
    private array $actions;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        MetaDataService $metaDataService,
        AuthorizationCheckerInterface $authorizationChecker,
        iterable $actions
    ) {
        /** @var ActionInterface $action */
        foreach ($actions as $action) {
            $this->actions[$action->getCode()] = $action;
        }
        $this->metaDataService = $metaDataService;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getEntityActionPermissions(string $agId): array
    {
        $rules = [];
        $defaultAllowed = !$this->metaDataService->getEntityAttribute($agId, 'permission.disallow_by_default');
        foreach ($this->actions as $action) {
            $key = $action->getCode();
            $rules[$key] = true;
            if (!$action->isRestrictable()) {
                continue;
            }
            $permission = $this->metaDataService->getEntityAttribute($agId, 'permission.action.' . $key);
            $rules[$key] = $this->isAllowed($permission, $defaultAllowed);
        }
        return $rules;
    }

    public function getEntityFieldActionPermissions(string $agId, string $field): array
    {
        $rules = [];
        $defaultAllowed = !$this->metaDataService->getEntityFieldAttribute(
            $agId,
            $field,
            'permission.disallow_by_default'
        );
        foreach ($this->actions as $action) {
            if (!$action->isRestrictable()) {
                continue;
            }
            $key = $action->getCode();
            $permission = $this->metaDataService->getEntityFieldAttribute($agId, $field, 'permission.action.' . $key);
            $rules[$key] = $this->isAllowed($permission, $defaultAllowed);
        }
        return $rules;
    }

    private function isAllowed(?Permission $permission, bool $default): bool
    {
        if ($permission === null) {
            return $default;
        }
        if ($permission->getRole() === null) {
            return $permission->isAllowed();
        }
        if ($this->authorizationChecker->isGranted($permission->getRole())) {
            return $permission->isAllowed();
        }
        return !$permission->isAllowed();
    }

}
