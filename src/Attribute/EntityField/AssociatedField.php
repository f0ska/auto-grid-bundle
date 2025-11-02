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

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class AssociatedField extends AbstractAttribute
{
    public function __construct(
        string $name,
        ?string $label = null,
        ?int $position = null,
        ?bool $canFilter = null,
        ?bool $canSort = null,
        array $options = []
    ) {
        $options['name'] = $name;
        if ($label !== null) {
            $options['label'] = $label;
        }
        if ($position !== null) {
            $options['position'] = $position;
        }
        if ($canFilter !== null) {
            $options['can_filter'] = $canFilter;
        }
        if ($canSort !== null) {
            $options['can_sort'] = $canSort;
        }
        $this->value = $options;
    }

    public function getCode(): string
    {
        return 'fields.' . $this->value['name'];
    }
}
