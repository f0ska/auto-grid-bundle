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

namespace F0ska\AutoGridBundle\Model;

class RequestInfo
{
    private ?string $agId;
    private ?string $action;
    private array $parameters;
    private bool $isBadRequest;

    public function __construct(?string $agId, ?string $action, array $parameters, bool $isBadRequest)
    {
        $this->agId = $agId;
        $this->action = $action;
        $this->parameters = $parameters;
        $this->isBadRequest = $isBadRequest;
    }

    public function getAgId(): ?string
    {
        return $this->agId;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function isBadRequest(): bool
    {
        return $this->isBadRequest;
    }
}
