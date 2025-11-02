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
class HtmlClasses extends AbstractAttribute
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
        ?string $theadBtnActive = null
    ) {
        $this->value = array_filter(
            [
                'table' => $table,
                'thead' => $thead,
                'tfoot' => $tfoot,
                'tbody' => $tbody,
                'theadBtn' => $theadBtn,
                'tbodyBtn' => $tbodyBtn,
                'tfootBtn' => $tfootBtn,
                'theadLink' => $theadLink,
                'theadLinkActive' => $theadLinkActive,
                'theadBtnActive' => $theadBtnActive,
            ]
        );
    }
}
