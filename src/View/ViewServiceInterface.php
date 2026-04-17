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

use F0ska\AutoGridBundle\Model\FieldParameter;

interface ViewServiceInterface
{
    /**
     * Prepares data for the rendering template.
     *
     * @return array Variables to be merged into the Twig context.
     */
    public function prepare(object $entity, FieldParameter $field): array;
}
