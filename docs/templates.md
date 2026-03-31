[Index](./index.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | **Templates** | [Customization](./customization.md)

Templates
==========

AutoGrid's UI is built with Twig and is designed to be highly customizable. You can override any template, from the main grid layout to a single cell, to fit your project's needs.

## Theme Structure

The default theme is **Bootstrap 5**, located in `templates/bootstrap_5`. The structure inside this directory mirrors the configuration keys found in `DependencyInjection/Configuration.php`.

```
templates/
└── bootstrap_5/
    ├── action/
    ├── field/
    ├── fieldset/
    ├── grid/
    └── base.html.twig
```

To create your own theme, you can copy the `bootstrap_5` directory, give it a new name, and then update the `theme` parameter in your `f0ska_auto_grid.yaml` configuration.

## Overriding Templates

You can override any template using the `#[Template]` attribute on your entity.

```php
use F0ska\AutoGridBundle\Attribute\Entity\Template;
use F0ska\AutoGridBundle\ValueObject\TemplateArea;

#[Template(area: TemplateArea::ACTION_GRID, templatePath: 'my_custom_templates/grid.html.twig')]
class MyEntity
{
    // ...
}
```

## Available Twig Blocks

For more granular control, you can extend the default templates and override specific blocks. The following is a list of all the available blocks for customization.

### Main Grid (`action/grid.html.twig`)

| Block Name                      | Description                                         |
|---------------------------------|-----------------------------------------------------|
| `autogrid`                      | The main block containing the entire grid component.|
| `autogrid_grid_caption`         | The caption (title) of the grid table.              |
| `autogrid_grid_header`          | The header section (`<thead>`) of the grid table.   |
| `autogrid_grid_footer`          | The footer section (`<tfoot>`) of the grid table.   |
| `autogrid_grid_body`            | The body section (`<tbody>`) of the grid table.     |
| `autogrid_grid_body_empty`      | The content shown when there are no rows to display.|
| `autogrid_grid_row`             | A single row (`<tr>`) in the grid body.             |

### Column Header (`grid/column_header.html.twig`)

| Block Name                          | Description                                         |
|-------------------------------------|-----------------------------------------------------|
| `autogrid_grid_column_header_cell`  | The entire header cell (`<th>`).                    |
| `autogrid_grid_column_header_sort`  | The sorting controls for the column.                |
| `autogrid_grid_column_header_label` | The label (title) of the column.                    |
| `autogrid_grid_column_header_filter`| The filter controls for the column.                 |

### Column Value (`grid/column_value.html.twig`)

| Block Name                           | Description                                         |
|--------------------------------------|-----------------------------------------------------|
| `autogrid_grid_column_value_cell`    | The entire value cell (`<td>`).                     |
| `autogrid_grid_column_value_content` | The content inside the value cell.                  |

### Action Column Header (`grid/column_header_action.html.twig`)

| Block Name                               | Description                                         |
|------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_header_action_clear_filter`| The "Clear Filter" button.                        |
| `autogrid_grid_header_action_advanced_filter`| The "Advanced Filter" button.                   |
| `autogrid_grid_header_action_export`     | The "Export" button and dropdown.                 |
| `autogrid_grid_header_action_create`     | The "Create" (plus) button.                       |
| `autogrid_grid_header_custom_action`     | A placeholder for additional custom action buttons. |

### Action Column Value (`grid/column_value_action.html.twig`)

| Block Name                         | Description                                         |
|------------------------------------|-----------------------------------------------------|
| `autogrid_grid_row_action_view`    | The "View" (eye) button for a row.                  |
| `autogrid_grid_row_action_edit`    | The "Edit" (pencil) button for a row.               |
| `autogrid_grid_row_action_delete`  | The "Delete" (trash) button for a row.              |
| `autogrid_grid_row_custom_action`  | A placeholder for additional custom action buttons. |

### Advanced Filter (`grid/advanced_filter.html.twig`)

| Block Name                                   | Description                                         |
|----------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_advanced_filter_modal`        | The entire modal component.                         |
| `autogrid_grid_advanced_filter_dialog`       | The modal dialog.                                   |
| `autogrid_grid_advanced_filter_content`      | The modal content.                                  |
| `autogrid_grid_advanced_filter_header`       | The modal header.                                   |
| `autogrid_grid_advanced_filter_body`         | The modal body.                                     |
| `autogrid_grid_advanced_filter_form`         | The form inside the modal body.                     |
| `autogrid_grid_advanced_filter_field`        | A single field in the advanced filter form.         |
| `autogrid_grid_advanced_filter_footer`       | The modal footer.                                   |
| `autogrid_grid_advanced_filter_submit_button`| The submit button in the footer.                    |
| `autogrid_grid_advanced_filter_reset_button` | The reset button in the footer.                     |

### Pagination (`grid/grid_pagination.html.twig`)

| Block Name                               | Description                                         |
|------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_pagination`               | The entire pagination container.                    |
| `autogrid_grid_pagination_page_active`   | The active page indicator.                          |
| `autogrid_grid_pagination_page`          | A clickable page link.                              |
| `autogrid_grid_pagination_gap`           | The gap indicator ("...") between page ranges.      |

### Page Limits (`grid/grid_number_per_page.html.twig`)

| Block Name                         | Description                                         |
|------------------------------------|-----------------------------------------------------|
| `autogrid_grid_limit`              | The entire page limit selector container.           |
| `autogrid_grid_limit_active`       | The active page limit indicator.                    |
| `autogrid_grid_limit_button`       | A clickable page limit link.                        |

### Filter Header (`grid/column_header_filter.html.twig`)

| Block Name                                     | Description                                         |
|------------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_column_header_filter`           | The entire filter dropdown component.               |
| `autogrid_grid_column_header_filter_icon`      | The filter icon that toggles the dropdown.          |
| `autogrid_grid_column_header_filter_dropdown`  | The dropdown menu containing the filter form.       |
| `autogrid_grid_column_header_filter_form_fields`| The fields inside the filter form.                 |
| `autogrid_grid_column_header_filter_buttons`   | The submit/reset buttons in the filter dropdown.    |

### Sort Header (`grid/column_header_sort.html.twig`)

| Block Name                               | Description                                         |
|------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_column_header_sort`       | The entire sorting control component.               |
| `autogrid_grid_column_header_sort_none`  | The control when no sorting is applied.             |
| `autogrid_grid_column_header_sort_asc`   | The control when sorting is ascending.               |
| `autogrid_grid_column_header_sort_desc`  | The control when sorting is descending.              |

### Mass Action Header (`grid/column_header_massaction.html.twig`)

| Block Name                                      | Description                                         |
|-------------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_column_header_massaction`        | The entire mass action header component (`<th>`).   |
| `autogrid_grid_column_header_massaction_dropdown`| The mass action dropdown component.                 |
| `autogrid_grid_column_header_massaction_choices` | The list of available mass actions.                |
| `autogrid_grid_column_header_massaction_select_all`| The "Select All" checkbox in the dropdown.        |

### Mass Action Column (`grid/column_value_massaction.html.twig`)

| Block Name                                     | Description                                         |
|------------------------------------------------|-----------------------------------------------------|
| `autogrid_grid_column_value_massaction`        | The mass action selection cell (`<td>`).           |
| `autogrid_grid_column_value_massaction_checkbox`| The checkbox for selecting a single row.           |

### Fieldset Form (`fieldset/form.html.twig`)

| Block Name                         | Description                                         |
|------------------------------------|-----------------------------------------------------|
| `autogrid_fieldset_card`           | The card container for a fieldset.                  |
| `autogrid_fieldset_header`         | The header of the fieldset card.                    |
| `autogrid_fieldset_body`           | The body of the fieldset card containing fields.     |
| `autogrid_fieldset_fields_without_fieldset`| The section for fields not assigned to any fieldset.|

### Fieldset View (`fieldset/view.html.twig`)

| Block Name                         | Description                                         |
|------------------------------------|-----------------------------------------------------|
| `autogrid_fieldset_card`           | The card container for a fieldset.                  |
| `autogrid_fieldset_header`         | The header of the fieldset card.                    |
| `autogrid_fieldset_table`          | The table inside the fieldset card containing values.|
| `autogrid_fieldset_fields_without_fieldset`| The section for fields not assigned to any fieldset.|

### View Action (`action/view.html.twig`)

| Block Name                            | Description                                         |
|---------------------------------------|-----------------------------------------------------|
| `autogrid`                            | The main block containing the entire view component.|
| `autogrid_view_caption`               | The caption (title) of the view table.              |
| `autogrid_view_header`                | The header section of the view table.               |
| `autogrid_view_header_back_button`    | The "Back" button in the header.                    |
| `autogrid_view_header_title`           | The title/badge in the header.                      |
| `autogrid_view_header_edit_button`    | The "Edit" button in the header.                    |
| `autogrid_view_header_delete_button`  | The "Delete" button in the header.                  |
| `autogrid_view_footer`                | The footer section of the view table.               |
| `autogrid_view_body`                  | The body section of the view table.                 |
| `autogrid_view_body_row`              | A single row in the view body.                      |
| `autogrid_view_body_row_label`        | The label cell of a view row.                       |
| `autogrid_view_body_row_value`        | The value cell of a view row.                       |

### Form Action (`action/form.html.twig`)

| Block Name                            | Description                                         |
|---------------------------------------|-----------------------------------------------------|
| `autogrid`                            | The main block containing the entire form component.|
| `autogrid_form_caption`               | The caption (title) of the form table.              |
| `autogrid_form_header`                | The header section of the form table.               |
| `autogrid_form_header_back_button`    | The "Back" button in the header.                    |
| `autogrid_form_header_view_button`    | The "View" button in the header.                    |
| `autogrid_form_header_title`           | The title/badge in the header.                      |
| `autogrid_form_header_submit_button`  | The "Submit" button in the header.                  |
| `autogrid_form_header_delete_button`  | The "Delete" button in the header.                  |
| `autogrid_form_footer`                | The footer section of the form table.               |
| `autogrid_form_body`                  | The body section of the form table.                 |
| `autogrid_form_form`                  | The form rendering block.                           |

### Error Action (`action/error.html.twig`)

| Block Name                | Description                                         |
|---------------------------|-----------------------------------------------------|
| `autogrid`                | The main block containing the error message.        |
| `autogrid_error_content`  | The content of the error alert.                     |
