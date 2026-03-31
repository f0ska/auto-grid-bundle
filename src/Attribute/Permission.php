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

namespace F0ska\AutoGridBundle\Attribute;

use Attribute;
use F0ska\AutoGridBundle\Model\Permission as PermissionModel;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Permission extends AbstractAttribute
{
    private ?string $action;
    private ?string $gridId;

    public function __construct(
        ?string $action = null, // If null, it's a global permission (AllowAll/ForbidAll)
        bool $allow = true,
        mixed $role = null,
        ?string $gridId = null
    ) {
        $this->action = $action;
        $this->gridId = $gridId;

        parent::__construct(
            (new PermissionModel())
                ->setAllowed($allow)
                ->setRole($role)
        );
    }

    public function getCode(): string
    {
        $code = 'permission';
        if ($this->gridId !== null) {
            $code .= '.grid.' . $this->gridId;
        }

        if ($this->action === null) {
            $code .= '.all';
        } else {
            $code .= '.action.' . $this->normalizeCode($this->action);
        }

        return $code;
    }
}
