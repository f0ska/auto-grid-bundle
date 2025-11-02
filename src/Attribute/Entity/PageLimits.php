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

namespace F0ska\AutoGridBundle\Attribute\Entity;

use Attribute;
use F0ska\AutoGridBundle\Attribute\AbstractAttribute;

#[Attribute]
class PageLimits extends AbstractAttribute
{
    public function __construct(array $value)
    {
        $this->value = array_values(
            array_filter(
                $value,
                function ($item) {
                    return is_int($item) && $item > 0;
                }
            )
        );
        if (empty($this->value)) {
            $this->value = null;
        }
    }
}
