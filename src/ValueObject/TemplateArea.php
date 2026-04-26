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

namespace F0ska\AutoGridBundle\ValueObject;

final class TemplateArea
{
    // Action Templates
    public const ACTION_GRID = 'action.grid';
    public const ACTION_FORM = 'action.form';
    public const ACTION_VIEW = 'action.view';
    public const ACTION_ERROR = 'action.error';

    // Field Templates
    public const FIELD_DEBUG = 'field.debug';
    public const FIELD_TEXT = 'field.text';
    public const FIELD_DATE = 'field.date';
    public const FIELD_CHOICE = 'field.choice';
    public const FIELD_BOOLEAN = 'field.boolean';
    public const FIELD_SIMPLE_ARRAY = 'field.simple_array';
    public const FIELD_JSON = 'field.json';
    public const FIELD_ASCII_STRING = 'field.ascii_string';
    public const FIELD_BINARY = 'field.binary';

    // Fieldset Templates
    public const FIELDSET_VIEW = 'fieldset.view';
    public const FIELDSET_FORM = 'fieldset.form';

    // Grid Templates
    public const GRID_ADVANCED_FILTER = 'grid.advanced_filter';
    public const GRID_COLGROUP = 'grid.colgroup';
    public const GRID_COLUMN_HEADER = 'grid.column_header';
    public const GRID_COLUMN_HEADER_ACTION = 'grid.column_header_action';
    public const GRID_COLUMN_HEADER_FILTER = 'grid.column_header_filter';
    public const GRID_COLUMN_HEADER_MASSACTION = 'grid.column_header_massaction';
    public const GRID_COLUMN_HEADER_SORT = 'grid.column_header_sort';
    public const GRID_COLUMN_VALUE = 'grid.column_value';
    public const GRID_COLUMN_VALUE_ACTION = 'grid.column_value_action';
    public const GRID_COLUMN_VALUE_MASSACTION = 'grid.column_value_massaction';
    public const GRID_NUMBER_PER_PAGE = 'grid.grid_number_per_page';
    public const GRID_PAGINATION = 'grid.grid_pagination';
    public const GRID_ROW_CLASS = 'grid.row_class';

    // Other Templates (from top-level 'template' node)
    public const BASE = 'base';
    public const BEFORE = 'before';
    public const AFTER = 'after';
}
