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

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Template extends AbstractAttribute
{
    private string $area;

    public function __construct(string $area, string $templatePath)
    {
        $this->area = $area;
        parent::__construct($templatePath);
    }

    public function getCode(): string
    {
        return 'template.' . $this->area;
    }
}
