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

namespace F0ska\AutoGridBundle\Model;

class AttributeCollection
{
    private array $entityAttributes;
    private array $fieldAttributes;

    public function __construct(array $entityAttributes, array $fieldAttributes)
    {
        $this->entityAttributes = $entityAttributes;
        $this->fieldAttributes = $fieldAttributes;
    }

    public function getEntityAttributes(): array
    {
        return $this->entityAttributes;
    }

    public function getFieldAttributes(): array
    {
        return $this->fieldAttributes;
    }
}
