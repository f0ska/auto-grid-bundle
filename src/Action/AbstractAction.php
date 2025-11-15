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

use function Symfony\Component\String\u;

abstract class AbstractAction implements ActionInterface
{
    public function getCode(): string
    {
        return u(trim((string) strrchr(static::class, '\\'), '\\'))
            ->kebab()
            ->trimSuffix('-action')
            ->toString();
    }

    public function isRestrictable(): bool
    {
        return true;
    }

    public function isIdRequired(): bool
    {
        return true;
    }
}
