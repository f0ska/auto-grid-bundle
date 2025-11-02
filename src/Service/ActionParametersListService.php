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

use F0ska\AutoGridBundle\ActionParameter\ActionParameterInterface;
use F0ska\AutoGridBundle\Exception\ActionParameterException;
use F0ska\AutoGridBundle\Model\Parameters;

class ActionParametersListService
{
    /**
     * @var ActionParameterInterface[]
     */
    private array $actionParameters = [];

    public function __construct(iterable $actionParameters)
    {
        /** @var ActionParameterInterface $actionParameter */
        foreach ($actionParameters as $actionParameter) {
            $this->actionParameters[$actionParameter->getCode()] = $actionParameter;
        }
    }

    public function hasParameter(string $key): bool
    {
        return isset($this->actionParameters[$key]);
    }

    public function getParameter(string $key): ActionParameterInterface
    {
        return $this->actionParameters[$key] ?? throw new ActionParameterException();
    }

    public function validateParameter(mixed $key, mixed $value, string $action, Parameters $parameters): bool
    {
        if (!is_string($key)) {
            return false;
        }
        if (!$this->hasParameter($key)) {
            return false;
        }
        return $this->getParameter($key)->validate($action, $value, $parameters);
    }

    public function normalizeParameter(string $key, mixed $value): mixed
    {
        return $this->getParameter($key)->normalize($value);
    }
}
