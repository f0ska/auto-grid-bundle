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

namespace F0ska\AutoGridBundle\Builder;

use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ChoiceBuilder
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Parameters $parameters
     * @return array<string, string>
     */
    public function buildMassChoices(Parameters $parameters): array
    {
        $choices = [];
        $actions = $parameters->attributes['mass_action'] ?? [];
        foreach ($actions as $action) {
            if ($action['role'] !== null && !$this->authorizationChecker->isGranted($action['role'])) {
                continue;
            }
            $choices[$action['name']] = $action['code'];
        }
        return $choices;
    }

    /**
     * @param Parameters $parameters
     * @return array<string, string>
     */
    public function buildExportChoices(Parameters $parameters): array
    {
        $choices = [];
        $actions = $parameters->attributes['export_action'] ?? [];
        foreach ($actions as $action) {
            if ($action['role'] !== null && !$this->authorizationChecker->isGranted($action['role'])) {
                continue;
            }
            $choices[$action['name']] = $action['code'];
        }
        return $choices;
    }

    /**
     * @param array<string|int, ChoiceView> $choices
     * @return array<string, string>
     */
    public function buildChoicesFromChoices(array $choices): array
    {
        $result = [];
        foreach ($choices as $choice) {
            if (is_string($choice->label)) {
                $result[$choice->label] = $choice->value;
            }
        }
        return $result;
    }
}
