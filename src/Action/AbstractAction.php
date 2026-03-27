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
    public static function getActionCode(): string
    {
        return u(trim((string) strrchr(static::class, '\\'), '\\'))
            ->snake()
            ->trimSuffix('_action')
            ->toString();
    }

    public function getCode(): string
    {
        return static::getActionCode();
    }

    public function isRestrictable(): bool
    {
        return true;
    }

    public function isIdRequired(): bool
    {
        return false;
    }
}
