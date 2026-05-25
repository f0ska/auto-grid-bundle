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
            throw new InvalidGridParameterException('Invalid request parameter: search is not enabled or not allowed');
        }

        if ($value === null) {
            return null;
        }

        if (!is_array($value) || !array_key_exists('term', $value)) {
            throw new InvalidGridParameterException('Invalid request parameter: search must contain a term value');
        }

        $unknownKeys = array_diff(array_keys($value), ['term', 'fields']);
        if ($unknownKeys !== []) {
            throw new InvalidGridParameterException('Invalid request parameter: search contains an unsupported value');
        }

        if (!is_scalar($value['term']) && $value['term'] !== null) {
            throw new InvalidGridParameterException('Invalid request parameter: search term must be scalar');
        }

        $term = trim((string) $value['term']);
        if ($term === '') {
            return null;
        }

        $minLength = (int) $parameters->attributes['searchable']['min_length'];
        $maxLength = (int) $parameters->attributes['searchable']['max_length'];
        $length = mb_strlen($term, 'UTF-8');

        if ($length < $minLength || $length > $maxLength) {
            throw new InvalidGridParameterException('Invalid request parameter: search term length is outside allowed bounds');
        }

        $search = ['term' => $term];
        $fields = $this->normalizeFields($value['fields'] ?? null, $parameters);
        if ($fields !== []) {
            $search['fields'] = $fields;
        }

        return $search;
    }

    private function isSearchAllowed(Parameters $parameters): bool
    {
        return !empty($parameters->attributes['searchable']['fields'])
            && !empty($parameters->permissions['search']);
    }

    /**
     * @return string[]
     */
    private function normalizeFields(mixed $value, Parameters $parameters): array
    {
        if ($value === null || $value === []) {
            return [];
        }

        if (empty($parameters->attributes['searchable']['field_selector'])) {
            throw new InvalidGridParameterException('Invalid request parameter: search fields selector is not enabled');
        }

        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidGridParameterException('Invalid request parameter: search fields must be a list');
        }

        $configuredFields = $parameters->attributes['searchable']['fields'];
        $fields = [];
        foreach ($value as $field) {
            if (!is_scalar($field)) {
                throw new InvalidGridParameterException('Invalid request parameter: search field must be scalar');
            }

            $field = (string) $field;
            if (!in_array($field, $configuredFields, true)) {
                throw new InvalidGridParameterException(sprintf(
                    'Invalid request parameter: unknown search field "%s"',
                    $field
                ));
            }

            $fields[] = $field;
        }

        return array_values(array_unique($fields));
    }
}
