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
class DefaultSort extends AbstractAttribute
{
    private string $direction;

    public function __construct(string|array $value, string $direction = 'asc')
    {
        $this->value = $value;
        $this->direction = $direction;
    }

    public function getValue(): array
    {
        if (is_string($this->value)) {
            return [$this->value => $this->direction];
        }
        return $this->value;
    }
}
