[Index](./index.md) | [Installation](./installation.md) | **Configuration** | [Attributes](./attributes.md) | [Optional Factory Arguments](./optional-factory-arguments.md) | [Customization](./customization.md)

Global Configuration
====================

AutoGrid is highly configurable using a standard Symfony configuration file. To change the default settings, create a new file named `f0ska_auto_grid.yaml` inside your project's `config/packages/` directory.

Below is the complete default configuration. You can copy any part of this structure into your own `f0ska_auto_grid.yaml` file and modify the values to suit your needs.

## Default Configuration Reference

```yaml
# config/packages/f0ska_auto_grid.yaml
f0ska_auto_grid:

    # --- Template Configuration ---
    template:
        theme: '@@F0skaAutoGrid/bootstrap_5' # Main theme for the grid.
        base: 'base.html.twig'               # Your app's base template.
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
        friendly_id: 'autogrid'       # Used for frontend container id, class and link fragment generation.
        show_entity_title: true       # Display a default title for the grid.
        field_formats:
            date: 'Y-m-d'
            time: 'H:i'
            datetime: 'Y-m-d H:i'
            interval: '%%Yy, %%mm, %%dd' # Note: Use '%%' to escape the '%' character in YAML

    # --- Grid Behavior ---
    grid:
        truncate_text: 55             # Max content length in a grid cell. Set to 0 to disable.
        pagination_limits:
            - 10
            - 50
            - 100

    # --- Default Button Visibility ---
    buttons:
        view:
            display_in_grid: true
            display_in_edit: true
        edit:
            display_in_grid: true
            display_in_view: true
        delete:
            display_in_grid: true
            display_in_view: true
            display_in_edit: true

    # --- Request Parameter Settings ---
    request:
        single_parameter_mode: false  # If true, all grid parameters are encoded into a single URL parameter.
        single_parameter_code: '_ag'
        parameter_codes:
            id: 'agId'
            action: 'agAction'
            params: 'agParams'

    # --- Session Settings ---
    session:
        store_navigation: true        # Remember pagination, sorting, and filters when navigating away and back.

    # --- Form Behavior ---
    form:
        default_boolean_as_select: false # If true, boolean fields in forms default to a select box.
        default_date_filter_range: true  # If true, date filters default to a "from/to" range filter.
        relation_label_candidates:       # When guessing a label for an entity relation, check for these properties in order.
            - 'title'
            - 'label'
            - 'name'
            - 'code'
            - 'subject'
            - 'model'
            - 'sku'

```

## Route Parameters
You can also override the theme and form themes on a per-route basis by defining parameters in your route definition:

- `_autogrid_theme`: Overrides the global `template.theme`.
- `_autogrid_form_themes`: Overrides the global `template.form_themes`.

These parameters can be useful if you have a project with multiple design themes.

---

Check documentation for more possibilities
------------------------------------------
- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Attributes](./attributes.md)
- [Customization](./customization.md)
