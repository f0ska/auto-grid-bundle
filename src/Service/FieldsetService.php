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

class FieldsetService
{
    public function build(Parameters $parameters): array
    {
        $fieldsets = $parameters->attributes['fieldset'] ?? [];
        $allowedFields = [];
        $fieldsWithoutFieldset = [];

        foreach ($parameters->fields as $field) {
            if (!empty($field->permissions[$parameters->action])) {
                $allowedFields[] = $field->name;
                $fieldsWithoutFieldset[$field->name] = null;
            }
        }

        foreach ($fieldsets as $key => &$fieldset) {
            $fieldset['fields'] = array_intersect($fieldset['fields'], $allowedFields);
            if (empty($fieldset['fields'])) {
                unset($fieldsets[$key]);
            } else {
                foreach ($fieldset['fields'] as $fieldName) {
                    unset($fieldsWithoutFieldset[$fieldName]);
                }
            }
        }

        return [
            'defined' => $fieldsets,
            'not_defined' => array_keys($fieldsWithoutFieldset),
        ];
    }
}
