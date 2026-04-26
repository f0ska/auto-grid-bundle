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

use F0ska\AutoGridBundle\Model\Permission;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PermissionService
{
    public function __construct(
        private readonly MetaDataService $metaDataService,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ActionListService $actionListService
    ) {
    }

    public function getEntityActionPermissions(string $agId): array
    {
        $rules = [];
        $defaultAllowed = !$this->metaDataService->getEntityAttribute($agId, 'permission.disallow_actions_by_default');
        foreach ($this->actionListService->getActions() as $action) {
            $key = $action->getCode();
            if (!$action->isRestrictable()) {
                $rules[$key] = true;
                continue;
            }

            $genericGlobal = $this->metaDataService->getEntityAttribute($agId, 'permission.all');
            $gridSpecificGlobal = $this->metaDataService->getEntityAttribute($agId, "permission.grid.$agId.all");
            $genericAction = $this->metaDataService->getEntityAttribute($agId, "permission.action.$key");
            $gridSpecificAction = $this->metaDataService->getEntityAttribute($agId, "permission.grid.$agId.action.$key");

            // Resolve global first
            $resolvedGlobal = $this->isAllowed($gridSpecificGlobal, $this->isAllowed($genericGlobal, $defaultAllowed));

            // Resolve action with global as default
            $rules[$key] = $this->isAllowed($gridSpecificAction, $this->isAllowed($genericAction, $resolvedGlobal));
        }
        return $rules;
    }

    public function getEntityFieldActionPermissions(string $agId, string $field, ?string $gridId = null): array
    {
        $rules = [];
        $defaultAllowed = !$this->metaDataService->getEntityAttribute($agId, 'permission.disallow_fields_by_default');
        $gridId = $gridId ?? $agId;

        foreach ($this->actionListService->getActions() as $action) {
            if (!$action->isRestrictable()) {
                continue;
            }
            $key = $action->getCode();

            $genericGlobal = $this->metaDataService->getEntityFieldAttribute($agId, $field, 'permission.all');
            $gridSpecificGlobal = $this->metaDataService->getEntityFieldAttribute($agId, $field, "permission.grid.$gridId.all");
            $genericAction = $this->metaDataService->getEntityFieldAttribute($agId, $field, "permission.action.$key");
            $gridSpecificAction = $this->metaDataService->getEntityFieldAttribute($agId, $field, "permission.grid.$gridId.action.$key");

            // Resolve global first
            $resolvedGlobal = $this->isAllowed($gridSpecificGlobal, $this->isAllowed($genericGlobal, $defaultAllowed));

            // Resolve action with global as default
            $rules[$key] = $this->isAllowed($gridSpecificAction, $this->isAllowed($genericAction, $resolvedGlobal));
        }
        return $rules;
    }

    /**
     * Determines if a permission is granted based on a Permission object and a default value.
     *
     * @param Permission|null $permission The permission object to check.
     * @param bool $default The default value to return if no specific permission is defined.
     * @return bool
     */
    private function isAllowed(?Permission $permission, bool $default): bool
    {
        // If no specific permission attribute is set, fall back to the default.
        if ($permission === null) {
            return $default;
        }

        // If the permission has no role, it's a simple boolean check.
        if ($permission->getRole() === null) {
            return $permission->isAllowed();
        }

        // If the user has the required role, the permission's "allowed" flag is authoritative.
        if ($this->authorizationChecker->isGranted($permission->getRole())) {
            return $permission->isAllowed();
        }

        // If the user does NOT have the role, the result is the inverse of the "allowed" flag.
        // e.g., if allow=true for ROLE_ADMIN, a non-admin is denied (returns false).
        // e.g., if allow=false for ROLE_ADMIN, a non-admin is granted (returns true).
        return !$permission->isAllowed();
    }
}
