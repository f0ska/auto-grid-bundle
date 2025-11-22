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

namespace F0ska\AutoGridBundle\Attribute\Abstract;

class AbstractCustomRoute extends AbstractAttribute
{
    /**
     * @param string|null $route It will use action name if empty.
     * @param array<string> $parameters Flat list of parameters to use (from request or provided by factory).
     */
    public function __construct(?string $route = null, array $parameters = [])
    {
        $this->value = [
            'route' => $route,
            'parameters' => $parameters,
        ];
    }
}
