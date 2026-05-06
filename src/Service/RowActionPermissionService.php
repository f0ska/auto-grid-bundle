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

use F0ska\AutoGridBundle\Attribute\Entity\RowActionPermission;
use F0ska\AutoGridBundle\RowActionPermission\RowActionPermissionRegistry;
use F0ska\AutoGridBundle\Model\Parameters;

class RowActionPermissionService
{
    public function __construct(private readonly RowActionPermissionRegistry $registry)
    {
    }

    public function isGranted(string $action, object $entity, Parameters $parameters): bool
    {
        foreach ($this->getMatchingRules($action, $parameters) as $rule) {
            $result = $this->registry
                ->get($rule['service'])
                ->isGranted($action, $entity, $parameters)
            ;

            if ($rule['effect'] === RowActionPermission::Allow && !$result) {
                return false;
            }

            if ($rule['effect'] === RowActionPermission::Deny && $result) {
                return false;
            }
        }

        return true;
    }

    private function getMatchingRules(string $action, Parameters $parameters): array
    {
        $rules = $parameters->attributes['row_action_permission'] ?? [];

        return array_values(array_filter(
            $rules,
            static fn(array $rule): bool => in_array($action, $rule['actions'], true)
        ));
    }
}
