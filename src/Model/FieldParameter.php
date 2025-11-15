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

use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\FieldMapping;

class FieldParameter
{
    public string $name;
    public string $mappingType;
    public ?string $agId = null;
    public ?string $agSubId = null;
    public ?string $subName = null;
    public ?string $subObject = null;
    public ?FieldMapping $fieldMapping = null;
    public ?AssociationMapping $associationMapping = null;
    public bool $canFilter = false;
    public bool $canSort = false;
    public array $permissions = [];
    public array $view = [];
    public array $attributes = [];

    public function __construct(array $initialData)
    {
        foreach ($initialData as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
