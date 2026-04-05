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
use F0ska\AutoGridBundle\Attribute\AbstractAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Filterable extends AbstractAttribute
{
    /**
     * @param bool        $enabled     Enable or disable filtering for this field.
     * @param string|null $condition   Filter condition class (implements FilterConditionInterface). Auto-guessed if null.
     * @param string|null $formType    Override the filter form type. Auto-guessed if null.
     * @param array       $formOptions Override the filter form options.
     */
    public function __construct(
        bool $enabled = true,
        ?string $condition = null,
        ?string $formType = null,
        array $formOptions = [],
    ) {
        parent::__construct([
            'enabled' => $enabled,
            'condition' => $condition,
            'form_type' => $formType,
            'form_options' => $formOptions,
        ]);
    }

    public function getCode(): string
    {
        return 'filterable';
    }
}
