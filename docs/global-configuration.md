[Home](../README.md) | [Installation](./installation.md) | **Configuration** | [Attributes](./attributes.md) | [Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)

# Global Configuration

AutoGrid is highly configurable via a standard Symfony YAML configuration. To change default settings, create `config/packages/f0ska_auto_grid.yaml`.

## Default Configuration Reference

```yaml
# config/packages/f0ska_auto_grid.yaml
f0ska_auto_grid:

    # --- Template Configuration ---
    template:
        theme: '@@F0skaAutoGrid/bootstrap_5' # Main theme for the grid.
        base: 'base.html.twig'               # Your app's base template.
        icons: '@F0skaAutoGrid/_icons.html.twig' # Icon set template.
        form_themes:
            - 'bootstrap_5_layout.html.twig'

        # Templates for core actions
        action:
            grid: 'action/grid.html.twig'
            form: 'action/form.html.twig'
            view: 'action/view.html.twig'
            error: 'action/error.html.twig'

        # Templates for different field types in "view" mode
        field:
            debug: 'field/debug.html.twig'
            text: 'field/text.html.twig'
            date: 'field/date.html.twig'
            choice: 'field/choice.html.twig'
            boolean: 'field/boolean.html.twig'
            simple_array: 'field/array.html.twig'
            json: 'field/json.html.twig'
            ascii_string: 'field/ascii.html.twig'
            binary: 'field/binary.html.twig'

        # Templates for fieldsets
        fieldset:
            view: 'fieldset/view.html.twig'
            form: 'fieldset/form.html.twig'

        # Templates for individual grid components
        grid:
            advanced_filter: 'grid/advanced_filter.html.twig'
            colgroup: 'grid/colgroup.html.twig'
            column_header: 'grid/column_header.html.twig'
            column_header_action: 'grid/column_header_action.html.twig'
            column_header_filter: 'grid/column_header_filter.html.twig'
            column_header_massaction: 'grid/column_header_massaction.html.twig'
            column_header_sort: 'grid/column_header_sort.html.twig'
            column_value: 'grid/column_value.html.twig'
            column_value_action: 'grid/column_value_action.html.twig'
            column_value_massaction: 'grid/column_value_massaction.html.twig'
            grid_number_per_page: 'grid/grid_number_per_page.html.twig'
            grid_pagination: 'grid/grid_pagination.html.twig'
            row_class: 'grid/row_class.html.twig'

    # --- View & Display Settings ---
    view:
        friendly_id: 'autogrid'       # Container ID, class, and link fragment.
        show_entity_title: true       # Display a default title in the AutoGrid header.
        field_formats:
            date: 'Y-m-d'
            time: 'H:i'
            datetime: 'Y-m-d H:i'
            interval: '%%Yy, %%mm, %%dd'

    # --- Grid Behavior ---
    grid:
        truncate_text: 55             # Max cell content length. Set to 0 to disable.
        pagination_limits: [10, 50, 100]

    # --- Default Button Visibility ---
    buttons:
        view: { display_in_grid: true, display_in_edit: true }
        edit: { display_in_grid: true, display_in_view: true }
        delete: { display_in_grid: true, display_in_view: true, display_in_edit: true }

    # --- Request Parameter Settings ---
    request:
        single_parameter_mode: false  # Encode all parameters into one URL param.
        single_parameter_code: '_ag'
        parameter_codes:
            id: 'agId'
            action: 'agAction'
            params: 'agParams'

    # --- Session Settings ---
    session:
        store_navigation: true        # Remember filters/sort when navigating away.

    # --- Form Behavior ---
    form:
        default_boolean_as_select: false # Use select box for booleans.
        default_date_filter_range: true  # Use from/to range for date filters.
        relation_label_candidates: [title, label, name, code, subject, model, sku]
```

## Route Parameters
Override global settings on a per-route basis in your routing config:

- `_autogrid_theme`: Overrides the global `template.theme`.
- `_autogrid_form_themes`: Overrides the global `template.form_themes`.

---

[Attributes](./attributes.md) | [Templates](./templates.md) | [Customization](./customization.md)
