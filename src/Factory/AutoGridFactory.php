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

namespace F0ska\AutoGridBundle\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Service\MetaDataService;
use F0ska\AutoGridBundle\Service\RequestService;

class AutoGridFactory
{
    private MetaDataService $metaDataService;
    private RequestService $requestService;

    public function __construct(
        MetaDataService $metaDataService,
        RequestService $requestService
    ) {
        $this->metaDataService = $metaDataService;
        $this->requestService = $requestService;
    }

    /**
     * @param string $entityClass
     * @param string|null $gridId
     * @param Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string|null $queryExpression
     * @param ArrayCollection<string, mixed>|null $queryParameters
     * @param string|null $initialAction
     * @param array<string, int|string|array> $initialParameters
     * @return AutoGrid
     */
    public function create(
        string $entityClass,
        ?string $gridId = null,
        Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string|null $queryExpression = null,
        ?ArrayCollection $queryParameters = null,
        ?string $initialAction = null,
        array $initialParameters = []
    ): AutoGrid {
        $agId = $this->metaDataService->add($entityClass, $gridId);
        $autoGrid = new AutoGrid($agId);
        $autoGrid->setQueryExpression($queryExpression);
        $autoGrid->setQueryParameters($queryParameters);
        $autoGrid->setInitialAction($initialAction);
        $autoGrid->setInitialParameters($initialParameters);
        $this->requestService->processRequest($autoGrid);
        return $autoGrid;
    }
}
