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

use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;

interface ActionInterface
{
    public function getCode(): string;

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void;

    public function isRestrictable(): bool;

    public function isIdRequired(): bool;
}
