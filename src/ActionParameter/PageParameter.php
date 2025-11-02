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

namespace F0ska\AutoGridBundle\ActionParameter;

use F0ska\AutoGridBundle\Model\Parameters;

class PageParameter implements ActionParameterInterface
{

    public function getCode(): string
    {
        return 'page';
    }

    public function validate(string $action, mixed $value, Parameters $parameters): bool
    {
        return is_numeric($value) && (int) $value > 0;
    }

    public function normalize(mixed $value): int
    {
        return (int) $value;
    }
}
