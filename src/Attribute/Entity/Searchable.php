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

namespace F0ska\AutoGridBundle\Attribute\Entity;

use Attribute;
use F0ska\AutoGridBundle\Attribute\AbstractAttribute;
use F0ska\AutoGridBundle\Search\DefaultSearchService;

#[Attribute(Attribute::TARGET_CLASS)]
class Searchable extends AbstractAttribute
{
    /**
     * @param string[] $fields
     */
    public function __construct(
        array $fields,
        string $service = DefaultSearchService::class,
        int $minLength = 1,
        int $maxLength = 255,
    ) {
        parent::__construct([
            'fields' => $fields,
            'service' => $service,
            'min_length' => $minLength,
            'max_length' => $maxLength,
        ]);
    }
}
