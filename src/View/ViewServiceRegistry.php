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

namespace F0ska\AutoGridBundle\View;

use F0ska\AutoGridBundle\Exception\ViewServiceNotFoundException;

class ViewServiceRegistry
{
    /** @var array<string, ViewServiceInterface> */
    private array $services = [];

    /**
     * @param iterable<ViewServiceInterface> $services
     */
    public function __construct(iterable $services)
    {
        foreach ($services as $service) {
            $this->services[get_class($service)] = $service;
        }
    }

    public function get(string $id): ViewServiceInterface
    {
        return $this->services[$id] ?? throw new ViewServiceNotFoundException(
            sprintf('View service "%s" not found.', $id)
        );
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
