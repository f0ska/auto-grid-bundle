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
use F0ska\AutoGridBundle\Event\GridEvent;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GridAction extends AbstractAction
{
    private GridQueryBuilder $gridQueryBuilder;
    private EventDispatcherInterface $dispatcher;

    public function __construct(GridQueryBuilder $gridQueryBuilder, EventDispatcherInterface $dispatcher)
    {
        $this->gridQueryBuilder = $gridQueryBuilder;
        $this->dispatcher = $dispatcher;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $count = (int) $this->gridQueryBuilder->buildGridCountQuery($parameters)->getSingleScalarResult();
        $parameters->initPagination($count);
        $entities = $this->gridQueryBuilder->buildGridQuery($parameters)->getResult();
        $event = new GridEvent($entities, $count, $parameters);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME);
        $this->dispatcher->dispatch($event, $event::EVENT_NAME . '.' . $autoGrid->getId());
        $autoGrid->setTemplate($parameters->getActionTemplate('grid'));
        $autoGrid->setContext(
            $parameters->render(['rows' => $entities])
        );
    }
}
