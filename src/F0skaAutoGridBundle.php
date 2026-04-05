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

namespace F0ska\AutoGridBundle;

use F0ska\AutoGridBundle\DependencyInjection\F0skaAutoGridExtension;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class F0skaAutoGridBundle extends AbstractBundle
{
    public function getContainerExtension(): F0skaAutoGridExtension
    {
        return new F0skaAutoGridExtension();
    }
}
