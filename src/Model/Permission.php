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

class Permission
{
    private string $action;
    private bool $allowed;
    private mixed $role = null;

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): self
    {
        $this->allowed = $allowed;
        return $this;
    }

    public function getRole(): mixed
    {
        return $this->role;
    }

    public function setRole(mixed $role): self
    {
        $this->role = $role;
        return $this;
    }
}
