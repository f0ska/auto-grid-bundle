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
        $fieldSet = $parameters->attributes['fieldset'] ?? [];
        $allowedFields = [];
        $noFieldset = [];

        foreach ($parameters->fields as $field) {
            if (!empty($field->permissions[$parameters->action])) {
                $allowedFields[] = $field->name;
                $noFieldset[$field->name] = null;
            }
        }

        foreach ($fieldSet as &$set) {
            $set['fields'] = array_intersect($set['fields'], $allowedFields);
            $noFieldset = array_diff_key($noFieldset, array_flip($set['fields']));
        }
        unset($set); // Unset reference

        foreach ($allowedFields as $fieldName) {
            $key = $parameters->fields[$fieldName]->attributes['add_to_fieldset'] ?? null;
            if ($key === null || !isset($fieldSet[$key])) {
                continue;
            }
            if (!in_array($fieldName, $fieldSet[$key]['fields'], true)) {
                $fieldSet[$key]['fields'][] = $fieldName;
                unset($noFieldset[$fieldName]);
            }
        }

        foreach ($fieldSet as $key => $set) {
            if (empty($set['fields'])) {
                unset($fieldSet[$key]);
            }
        }

        return [
            'defined' => $fieldSet,
            'not_defined' => array_keys($noFieldset),
        ];
    }
}
