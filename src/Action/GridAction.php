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

use F0ska\AutoGridBundle\Builder\GridQueryBuilder;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;

class GridAction extends AbstractAction
{
    private GridQueryBuilder $gridQueryBuilder;

    public function __construct(GridQueryBuilder $gridQueryBuilder)
    {
        $this->gridQueryBuilder = $gridQueryBuilder;
    }

    public function isIdRequired(): bool
    {
        return false;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $count = (int) $this->gridQueryBuilder->buildGridCountQuery($parameters)->getSingleScalarResult();
        $parameters->initPagination($count);
        $queryRows = $this->gridQueryBuilder->buildGridQuery($parameters);

        $autoGrid->setTemplate($parameters->getActionTemplate('grid'));
        $autoGrid->setContext(
            $parameters->render(['rows' => $queryRows->getResult()])
        );
    }
}
