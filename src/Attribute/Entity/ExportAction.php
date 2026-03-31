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

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ExportAction extends AbstractAttribute
{
    private readonly string $code;

    public function __construct(string $name, ?string $code = null, ?string $role = null)
    {
        $this->code = $this->normalizeCode($code ?? $name);
        $value = ['name' => $name, 'code' => $this->code, 'role' => $role];
        parent::__construct($value);
    }

    public function getCode(): string
    {
        return 'export_action.' . $this->code;
    }
}
