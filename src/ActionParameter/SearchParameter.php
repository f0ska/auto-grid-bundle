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

namespace F0ska\AutoGridBundle\ActionParameter;

use F0ska\AutoGridBundle\Exception\InvalidGridParameterException;
use F0ska\AutoGridBundle\Model\Parameters;

class SearchParameter implements ActionParameterInterface
{
    public function getCode(): string
    {
        return 'search';
    }

    public function normalize(mixed $value, Parameters $parameters): ?array
    {
        if (!$this->isSearchAllowed($parameters)) {
            throw new InvalidGridParameterException();
        }

        if ($value === null) {
            return null;
        }

        if (!is_array($value) || !array_key_exists('term', $value) || count($value) > 1) {
            throw new InvalidGridParameterException();
        }

        if (!is_scalar($value['term']) && $value['term'] !== null) {
            throw new InvalidGridParameterException();
        }

        $term = trim((string) $value['term']);
        if ($term === '') {
            return null;
        }

        $minLength = (int) ($parameters->attributes['searchable']['min_length'] ?? 1);
        $maxLength = (int) ($parameters->attributes['searchable']['max_length'] ?? 255);
        $length = strlen($term);

        if ($length < $minLength || $length > $maxLength) {
            throw new InvalidGridParameterException();
        }

        return ['term' => $term];
    }

    private function isSearchAllowed(Parameters $parameters): bool
    {
        return !empty($parameters->attributes['searchable']['fields'])
            && !empty($parameters->permissions['search']);
    }
}
