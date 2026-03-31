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

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ActionFormType extends AbstractAttribute
{
    /**
     * @param string $formType
     * @param string[] $actions
     */
    public function __construct(string $formType, array $actions = ['create', 'edit'])
    {
        $value = [];
        foreach ($actions as $action) {
            $value[$action] = $formType;
        }
        parent::__construct($value);
    }

    public function getCode(): string
    {
        return 'form_type';
    }
}
