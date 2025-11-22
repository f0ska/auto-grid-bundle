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
use function Symfony\Component\String\u;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Fieldset extends AbstractAttribute
{
    private string $code;

    public function __construct(string $name, string $class = '', ?string $code = null, array $fields = [])
    {
        $this->code = u($code ?? $name)->ascii()->snake()->toString();
        $this->value = [
            'name' => $name,
            'class' => $class,
            'fields' => $fields,
        ];
    }

    public function getCode(): string
    {
        return parent::getCode() . '.' . $this->code;
    }
}
