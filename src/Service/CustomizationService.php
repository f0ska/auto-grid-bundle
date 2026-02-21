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

namespace F0ska\AutoGridBundle\Service;

use F0ska\AutoGridBundle\Customization\CustomizationInterface;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;

class CustomizationService
{
    /**
     * @var CustomizationInterface[]
     */
    private iterable $customizations;

    public function __construct(iterable $customizations)
    {
        $this->customizations = $customizations;
    }

    public function executeCustomizations(AutoGrid $autoGrid, Parameters $parameters): void
    {
        foreach ($this->customizations as $customization) {
            $customization->execute($autoGrid, $parameters);
        }
    }
}
