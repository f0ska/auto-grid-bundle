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

namespace F0ska\AutoGridBundle\Attribute\Permission;

use Attribute;
use F0ska\AutoGridBundle\Attribute\AbstractAttribute;
use F0ska\AutoGridBundle\Model\Permission;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Allow extends AbstractAttribute
{
    private string $key;

    public function __construct(string $action, mixed $role = null)
    {
        $this->value = (new Permission())
            ->setAllowed(true)
            ->setRole($role);
        $this->key = $this->normalizeCode($action);
    }

    public function getCode(): string
    {
        return 'permission.action.' . $this->key;
    }
}
