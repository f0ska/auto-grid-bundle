Customization
=============
Refer to the [Global Configuration](./global-configuration.md) and [Attributes](./attributes.md)
documentation if you are looking for templates and forms customization.

**This is an advanced section. Please review the bundle code and ensure you understand how it works.**

## Services

**AutoGrid** is designed to be extendable.
You can not only provide custom templates and form
but also add new attributes, actions and action parameters to AutoGrid.
See [services.yaml](../config/services.yaml) file to understand how to do this.

- **Attributes**: implement the [AttributeInterface](../src/Attribute/AttributeInterface.php)
- **Actions**: implement the [ActionInterface](../src/Action/ActionInterface.php)
  and add your actions to the `autogrid.action` tag, making them public.
- **Action parameters**: implement the [ActionParameterInterface](../src/ActionParameter/ActionParameterInterface.php)
  and add your actions to the `autogrid.action.parameter` tag, making them public.

## Events

| Event Name                     | Event Object                                |
|--------------------------------|---------------------------------------------|
| `f0ska.autogrid.entity.delete` | [DeleteEvent](../src/Event/DeleteEvent.php) |
| `f0ska.autogrid.error.show`    | [ErrorEvent](../src/Event/ErrorEvent.php)   |
| `f0ska.autogrid.entity.save`   | [SaveEvent](../src/Event/SaveEvent.php)     |
| `f0ska.autogrid.entity.view`   | [ViewEvent](../src/Event/ViewEvent.php)     |
| `f0ska.autogrid.mass_action`   | [MassEvent](../src/Event/MassEvent.php)     |

Check documentation for more possibilities
------------------------------------------

- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Attributes](./attributes.md)
- [Global Configuration](./global-configuration.md)
