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

namespace F0ska\AutoGridBundle\Action;

use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;

class CreateAction extends EditAction
{
    public function isIdRequired(): bool
    {
        return false;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->entityBuilder->getNewEntity($parameters);
        $this->processForm($entity, $autoGrid, $parameters);
    }
}
