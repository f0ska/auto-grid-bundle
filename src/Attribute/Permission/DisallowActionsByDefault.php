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

namespace F0ska\AutoGridBundle\Attribute\Permission;

use Attribute;
use F0ska\AutoGridBundle\Attribute\AbstractAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
class DisallowActionsByDefault extends AbstractAttribute
{
    public function __construct()
    {
        parent::__construct(true);
    }

    public function getCode(): string
    {
        return 'permission.' . parent::getCode();
    }
}
