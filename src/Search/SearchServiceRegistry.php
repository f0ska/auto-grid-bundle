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

namespace F0ska\AutoGridBundle\Search;

use F0ska\AutoGridBundle\Exception\InvalidGridParameterException;

class SearchServiceRegistry
{
    /**
     * @var array<string, SearchServiceInterface>
     */
    private array $services = [];

    public function __construct(iterable $services)
    {
        /** @var SearchServiceInterface $service */
        foreach ($services as $service) {
            $this->services[$service::class] = $service;
        }
    }

    public function get(string $class): SearchServiceInterface
    {
        return $this->services[$class] ?? throw new InvalidGridParameterException();
    }
}
