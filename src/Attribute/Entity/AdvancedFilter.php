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
class AdvancedFilter extends AbstractAttribute
{
    public function __construct(
        bool $enabled = true,
        string $display = 'modal',
        bool $collapsed = true
    ) {
        parent::__construct($enabled ? [
            'enabled' => true,
            'display' => $display,
            'collapsed' => $collapsed,
        ] : false);
    }
}
