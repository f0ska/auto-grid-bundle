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

namespace F0ska\AutoGridBundle\Attribute\EntityField;

use Attribute;
use F0ska\AutoGridBundle\Attribute\Abstract\AbstractAttribute;

#[Attribute]
class ColumnHeaderClass extends AbstractAttribute
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
