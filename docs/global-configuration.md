Global configuration
====================
## Global parameters
AutoGrid provides a [parameters.yaml](../config/parameters.yaml) file, 
allowing you to override parameters in a standardized way.
You can override any of these parameters by creating `f0ska_auto_grid.yaml` in the `config/packages` 
directory of your project and copying any parameter you need.

## Route parameters
Several route parameters are available:
- `_autogrid_theme`: Overrides the global `f0ska.autogrid.template.theme`
- `_autogrid_form_themes`: Overrides the global `f0ska.autogrid.template.form_themes`

These parameters can be useful if you have a project with multiple design themes.

Check documentation for more possibilities
------------------------------------------
- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Attributes](./attributes.md)
- [Customization](./customization.md)
