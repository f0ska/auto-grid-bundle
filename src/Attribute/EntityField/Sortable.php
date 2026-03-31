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
use F0ska\AutoGridBundle\Attribute\AttributeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Sortable implements AttributeInterface
{
    private array $value;

    public function __construct(
        ?string $direction = null,
        int $priority = 0,
        bool $allowed = true
    ) {
        $this->value = [
            'direction' => $direction,
            'priority'  => $priority,
            'can_sort'  => $allowed,
        ];
    }

    public function getCode(): string
    {
        return 'sortable';
    }

    public function getValue(): array
    {
        return $this->value;
    }
}
