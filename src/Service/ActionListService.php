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
use F0ska\AutoGridBundle\Exception\ActionException;

class ActionListService
{
    /**
     * @var ActionInterface[]
     */
    private array $actions = [];

    public function __construct(iterable $actions)
    {
        /** @var ActionInterface $action */
        foreach ($actions as $action) {
            $this->actions[$action->getCode()] = $action;
        }
    }

    public function hasAction(string $action): bool
    {
        return isset($this->actions[$action]);
    }

    public function getErrorAction(): ActionInterface
    {
        return $this->actions['error'];
    }

    public function getAction(string $action): ActionInterface
    {
        return $this->actions[$action] ?? throw new ActionException('Undefined action');
    }
}
