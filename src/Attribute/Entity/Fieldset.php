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

use function Symfony\Component\String\u;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Fieldset extends AbstractAttribute
{
    private string $code;

    public function __construct(string $name, string $class = '', ?string $code = null)
    {
        $this->code = u($code ?? $name)->ascii()->snake()->toString();
        $value = [
            'name' => $name,
            'class' => $class,
            'fields' => [], // Fields are added by the parser from AddToFieldset attributes
        ];

        parent::__construct($value);
    }

    public function getCode(): string
    {
        return parent::getCode() . '.' . $this->code;
    }
}
