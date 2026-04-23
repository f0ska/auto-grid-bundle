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

use F0ska\AutoGridBundle\Model\Parameters;

class PaginationBuilder
{
    public function __construct(private readonly ConfigurationService $configuration)
    {
    }

    public function build(Parameters $parameters): void
    {
        $pageLimits = $parameters->attributes['page_limits'] ?? $this->configuration->getPaginationLimits();
        $limit = $parameters->request['limit'] ?? null;
        if (!$limit || !in_array($limit, $pageLimits, true)) {
            $limit = reset($pageLimits);
        }

        $parameters->view->pagination = [
            'limits' => $pageLimits,
            'limit'  => $limit,
            'page'   => $parameters->request['page'] ?? 1,
            'count'  => 0,
        ];
    }
}
