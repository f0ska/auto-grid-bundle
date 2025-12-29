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

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class ActionButtonDisplay extends AbstractAttribute
{
    /**
     * @param string $buttonAction "view", "edit", "delete".
     * @param bool|null $displayOnGrid
     * @param bool|null $displayOnCreate
     * @param bool|null $displayOnEdit
     * @param bool|null $displayOnView
     */
    public function __construct(
        string $buttonAction,
        ?bool $displayOnGrid = null,
        ?bool $displayOnCreate = null,
        ?bool $displayOnEdit = null,
        ?bool $displayOnView = null
    ) {
        if ($displayOnGrid !== null) {
            $this->value[$buttonAction]['display_on_grid'] = $displayOnGrid;
        }
        if ($displayOnCreate !== null) {
            $this->value[$buttonAction]['display_on_create'] = $displayOnCreate;
        }
        if ($displayOnEdit !== null) {
            $this->value[$buttonAction]['display_on_edit'] = $displayOnEdit;
        }
        if ($displayOnView !== null) {
            $this->value[$buttonAction]['display_on_view'] = $displayOnView;
        }
    }

    public function getCode(): string
    {
        return 'button';
    }
}
