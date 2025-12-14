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
class MassAction extends AbstractAttribute
{
    public function __construct(string $name, ?string $code = null, ?string $role = null)
    {
        $this->value = ['name' => $name, 'code' => $this->normalizeCode($code ?? $name), 'role' => $role];
    }

    public function getCode(): string
    {
        return 'mass_action.' . $this->value['code'];
    }
}
