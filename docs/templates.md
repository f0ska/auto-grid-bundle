[Home](../README.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Optional Factory Arguments](./optional-factory-arguments.md) | **Templates** | [Customization](./customization.md)

# Twig Template Reference

AutoGrid templates are highly modular, allowing you to override specific blocks for granular control.

## Theme Structure

The default **Bootstrap 5** theme is located in `templates/bootstrap_5`.

```text
templates/bootstrap_5/
├── action/    # Main layouts (grid, form, view, error)
├── field/     # Cell rendering for different data types
├── fieldset/  # Grouping layouts
├── grid/      # Components (pagination, filters, headers)
└── base.html.twig
```

---

## Block Reference

Extend the default templates and override these blocks to customize your UI.

### Main Grid (`action/grid.html.twig`)
| Block | Description |
| :--- | :--- |
| `autogrid` | Wraps the entire grid component. |
| `autogrid_grid_caption` | Grid title/header area. |
| `autogrid_grid_colgroup` | The `<colgroup>` section for column styling. |
| `autogrid_grid_header` | The `<thead>` section. |
| `autogrid_grid_body` | The `<tbody>` section. |
| `autogrid_grid_body_empty` | Shown when no results are found. |
| `autogrid_grid_row` | A single `<tr>` row. |

### Column Headers (`grid/column_header.html.twig`)
| Block | Description |
| :--- | :--- |
| `autogrid_grid_column_header_cell` | The `<th>` cell. |
| `autogrid_grid_column_header_label` | The text label of the column. |
| `autogrid_grid_column_header_sort` | Sorting icons and links. |
| `autogrid_grid_column_header_filter` | Filter dropdown icon and form. |
### Column Values (`grid/column_value.html.twig`)
The rendered value inside each cell is prepared **lazily** using the `ag_prepare` filter. This filter resolves the correct `ViewService` and template path for each property.

| Block | Description                                                                                                                                    |
| :--- |:-----------------------------------------------------------------------------------------------------------------------------------------------|
| `autogrid_grid_column_value_cell` | The `<td>` cell.                                                                                                                               |
| `autogrid_grid_column_value_content` | Prepares field data and includes the template: `{% set agView = field\|ag_prepare(entity) %}{{ include(agView.template, agView.variables) }}`. |

---

### Action Buttons (`grid/column_value_action.html.twig`)
| Block | Description |
| :--- | :--- |
| `autogrid_grid_row_action_view` | The View (eye) button. |
| `autogrid_grid_row_action_edit` | The Edit (pencil) button. |
| `autogrid_grid_row_action_delete` | The Delete (trash) button. |
| `autogrid_grid_row_custom_action` | Placeholder for your custom actions. |

### Pagination & Limits
| Template | Block | Description |
| :--- | :--- | :--- |
| `grid_pagination.html.twig` | `autogrid_grid_pagination` | The pagination container. |
| `grid_number_per_page.html.twig` | `autogrid_grid_limit` | The items-per-page selector. |

### Form & View Actions
| Template | Block | Description |
| :--- | :--- | :--- |
| `action/view.html.twig` | `autogrid_view_body` | The detail view container. |
| `action/form.html.twig` | `autogrid_form_form` | The Symfony Form rendering area. |

---

For instructions on how to apply these overrides, see [Customization](./customization.md).
