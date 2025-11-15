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

interface ActionParameterInterface
{
    public function getCode(): string;

    public function normalize(mixed $value, Parameters $parameters): mixed;
}
