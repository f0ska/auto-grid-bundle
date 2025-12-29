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
use F0ska\AutoGridBundle\Attribute\Abstract\AbstractAttribute;

#[Attribute]
class HtmlClass extends AbstractAttribute
{
    public function __construct(
        ?string $table = null,
        ?string $thead = null,
        ?string $tfoot = null,
        ?string $tbody = null,
        ?string $theadBtn = null,
        ?string $tbodyBtn = null,
        ?string $tfootBtn = null,
        ?string $theadLink = null,
        ?string $theadLinkActive = null,
        ?string $theadBtnActive = null,
        ?string $actionColumn = null,
        ?string $massActionColumn = null,
        ?string $actionColumnHeader = null,
        ?string $massActionColumnHeader = null
    ) {
        $this->value = array_filter(
            [
                'table' => $table,
                'thead' => $thead,
                'tfoot' => $tfoot,
                'tbody' => $tbody,
                'thead_btn' => $theadBtn,
                'tbody_btn' => $tbodyBtn,
                'tfoot_btn' => $tfootBtn,
                'thead_link' => $theadLink,
                'thead_link_active' => $theadLinkActive,
                'thead_btn_active' => $theadBtnActive,
                'action_column' => $actionColumn,
                'mass_action_column' => $massActionColumn,
                'action_column_header' => $actionColumnHeader,
                'mass_action_column_header' => $massActionColumnHeader,
            ]
        );
    }
}
