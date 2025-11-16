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

namespace F0ska\AutoGridBundle\Attribute;

use function Symfony\Component\String\u;

abstract class AbstractAttribute implements AttributeInterface
{
    protected mixed $value;

    public function getCode(): string
    {
        return $this->normalizeCode(trim((string) strrchr(static::class, '\\'), '\\'));
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    protected function normalizeCode(string $code): string
    {
        return u($code)->snake()->toString();
    }
}
