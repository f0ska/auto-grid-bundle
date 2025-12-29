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
class Template extends AbstractAttribute
{
    private string $code;

    public function __construct(string $code, string $templatePath)
    {
        $this->code = $code;
        $this->value = $templatePath;
    }

    public function getCode(): string
    {
        return 'template.' . $this->code;
    }
}
