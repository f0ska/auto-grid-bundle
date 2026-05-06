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

namespace F0ska\AutoGridBundle\RowActionPermission;

use LogicException;

class RowActionPermissionRegistry
{
    /** @var array<class-string, RowActionPermissionInterface> */
    private array $services = [];

    /**
     * @param iterable<RowActionPermissionInterface> $services
     */
    public function __construct(iterable $services)
    {
        foreach ($services as $service) {
            $this->services[$service::class] = $service;
        }
    }

    /**
     * @param class-string $id
     */
    public function get(string $id): RowActionPermissionInterface
    {
        return $this->services[$id] ?? throw new LogicException(sprintf(
            'Row action permission service "%s" is not registered.',
            $id
        ));
    }
}
