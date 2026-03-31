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

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class AssociatedField extends AbstractAttribute
{
    private string $key;

    /**
     * @param string $name
     * @param string|null $label
     * @param int|null $position
     * @param bool|null $canFilter
     * @param bool|null $canSort
     * @param array<string, string|int|bool|array> $options
     */
    public function __construct(
        string $name,
        ?string $label = null,
        ?int $position = null,
        ?bool $canFilter = null,
        ?bool $canSort = null,
        array $options = []
    ) {
        $this->key = $name;

        $value = array_merge($options, array_filter([
            'name' => $name,
            'label' => $label,
            'position' => $position,
            'can_filter' => $canFilter,
            'can_sort' => $canSort,
        ], fn ($v) => $v !== null));

        parent::__construct($value);
    }

    public function getCode(): string
    {
        return 'fields.' . $this->key;
    }
}
