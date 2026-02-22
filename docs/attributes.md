[Index](./index.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | **Attributes** | [Optional Factory Arguments](./optional-factory-arguments.md) | [Customization](./customization.md)

Attributes
==========
Using attributes is a primary way to customize **AutoGrid**.
You can use attributes on entity class and entity property.

```php
...

use F0ska\AutoGridBundle\Attribute;

...

#[ORM\Entity(repositoryClass: DemoOneRepository::class)]
#[Attribute\Entity\DefaultSort(['name' => 'asc'])]
class DemoOne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Attribute\EntityField\CanFilter(true)]
    private ?string $name = null;
    
    ...
}
```

## Entity class attributes

| Attribute                                                              | Description                                                                                                                                                                                                                                                                                                                                                                                    |
|------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [ActionButtonDisplay](../src/Attribute/Entity/ActionButtonDisplay.php) | Controls action button display. Overrides default configuration.                                                                                                                                                                                                                                                                                                                               |
| [ActionFormType](../src/Attribute/Entity/ActionFormType.php)           | Provides your own form definition. You can provide different form definitions for the actions such as `create`, `edit`, `filter`, and `advanced_filter`.                                                                                                                                                                                                                                       |
| [ActionRoute](../src/Attribute/Entity/ActionRoute.php)                 | Allows to define custom routes for various actions.<ul><li>The `route` argument defines the custom route name. If skipped, the action name uses instead.</li><li>The `parameters` argument specifies the parameter names used in the route, with values taken from the factory's route parameters (see [Optional Factory Arguments](optional-factory-arguments.md)) or from the current route. |
| [AdvancedFilter](../src/Attribute/Entity/AdvancedFilter.php)           | Enables the advanced filter feature.                                                                                                                                                                                                                                                                                                                                                           |
| [DefaultSort](../src/Attribute/Entity/DefaultSort.php)                 | Allows you to define the default sort order for your data in the grid.                                                                                                                                                                                                                                                                                                                         |
| [ExportAction](../src/Attribute/Entity/ExportAction.php)               | Provides a simple export feature. Takes applied filters into account. Triggers [ExportEvent](../src/Event/ExportEvent.php). Does nothing by default.                                                                                                                                                                                                                                           |
| [Fieldset](../src/Attribute/Entity/Fieldset.php)                       | Groups fields on default form and view pages.                                                                                                                                                                                                                                                                                                                                                  |
| [FormThemes](../src/Attribute/Entity/FormThemes.php)                   | Overrides form themes.                                                                                                                                                                                                                                                                                                                                                                         |
| [HasCustomAction](../src/Attribute/Entity/HasCustomAction.php)         | This is needed when you added custom actions in grid without standard actions                                                                                                                                                                                                                                                                                                                  |
| [HtmlClass](../src/Attribute/Entity/HtmlClass.php)                     | Sets HTML classes for the grid table.                                                                                                                                                                                                                                                                                                                                                          |
| [MassAction](../src/Attribute/Entity/MassAction.php)                   | Provides a simple mass-action feature. Triggers [MassEvent](../src/Event/MassEvent.php). Does nothing by default.                                                                                                                                                                                                                                                                              |
| [PageLimits](../src/Attribute/Entity/PageLimits.php)                   | Customizes the page limits select buttons.                                                                                                                                                                                                                                                                                                                                                     |
| [RedirectOnSubmit](../src/Attribute/Entity/RedirectOnSubmit.php)       | Defines the redirect action name after creating or updating a form.                                                                                                                                                                                                                                                                                                                            |
| [Template](../src/Attribute/Entity/Template.php)                       | Allows overriding any default template.                                                                                                                                                                                                                                                                                                                                                        |
| [Title](../src/Attribute/Entity/Title.php)                             | Sets the title for your entity.                                                                                                                                                                                                                                                                                                                                                                |

## Entity access attributes

| Attribute                                                                            | Description                                                                        |
|--------------------------------------------------------------------------------------|------------------------------------------------------------------------------------|
| [DisallowActionsByDefault](../src/Attribute/Permission/DisallowActionsByDefault.php) | Inverts permissions for all AutoGrid actions. By default, all actions are allowed. |
| [DisallowFieldsByDefault](../src/Attribute/Permission/DisallowFieldsByDefault.php)   | Inverts permissions for all AutoGrid fields. By default, all fields are allowed.   |
| [Allow](../src/Attribute/Permission/Allow.php)                                       | Allows specific actions, with an optional role.                                    |
| [Forbid](../src/Attribute/Permission/Forbid.php)                                     | Disallows specific actions, with an optional role.                                 |
| [AllowAll](../src/Attribute/Permission/Allow.php)                                    | Allows all actions, with an optional role.                                         |
| [ForbidAll](../src/Attribute/Permission/Forbid.php)                                  | Disallows all actions, with an optional role.                                      |

## Entity property (field) attributes

| Attribute                                                           | Description                                                                                                                          |
|---------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| [AddToFieldset](../src/Attribute/EntityField/AddToFieldset.php)     | Adds a field to a group defined in [Fieldset](../src/Attribute/Entity/Fieldset.php), allowing better organization of related fields. |
| [AssociatedField](../src/Attribute/EntityField/AssociatedField.php) | Creates "virtual" fields from associated entities (relations).                                                                       |
| [CanFilter](../src/Attribute/EntityField/CanFilter.php)             | Controls the column filter feature, allowing to filter data by this column.                                                          |
| [CanSort](../src/Attribute/EntityField/CanSort.php)                 | Controls the column sort feature, enabling to sort data by this column.                                                              |
| [ColumnHtmlClass](../src/Attribute/EntityField/ColumnHtmlClass.php) | Adds HTML classes to the grid table column for styling purposes.                                                                     |
| [FieldTemplate](../src/Attribute/EntityField/FieldTemplate.php)     | Overrides the field template for custom rendering.                                                                                   |
| [FormOptions](../src/Attribute/EntityField/FormOptions.php)         | Overrides form options for the form field.                                                                                           |
| [FormType](../src/Attribute/EntityField/FormType.php)               | Overrides the form type for the form field.                                                                                          |
| [GridTruncate](../src/Attribute/EntityField/GridTruncate.php)       | Sets the maximum number of characters displayed in the grid cell.                                                                    |
| [Label](../src/Attribute/EntityField/Label.php)                     | Overrides the field label.                                                                                                           |
| [MultipleFilter](../src/Attribute/EntityField/MultipleFilter.php)   | Allows multiple filters on forms with choices. Does not work for OneToMany and ManyToMany relations.                                 |
| [Position](../src/Attribute/EntityField/Position.php)               | Sets the field position, which can be positive or negative. Default is `0` for all fields.                                           |
| [RangeFilter](../src/Attribute/EntityField/RangeFilter.php)         | Creates a range filter instead of a single one. It does not work for all field types.                                                |
| [ValuePrefix](../src/Attribute/EntityField/ValuePrefix.php)         | Adds a prefix to displayed values.                                                                                                   |
| [ValueSuffix](../src/Attribute/EntityField/ValueSuffix.php)         | Adds a suffix to displayed values.                                                                                                   |

## Entity property access attributes

| Attribute                                              | Description                                                                             |
|--------------------------------------------------------|-----------------------------------------------------------------------------------------|
| [Allow](../src/Attribute/Permission/Allow.php)         | Allows access to this field for specific actions. A role can be provided optionally.    |
| [Forbid](../src/Attribute/Permission/Forbid.php)       | Disallows access to this field for specific actions. A role can be provided optionally. |
| [AllowAll](../src/Attribute/Permission/AllowAll.php)   | Allows access to this field for all actions. A role can be provided optionally.         |
| [ForbidAll](../src/Attribute/Permission/ForbidAll.php) | Disallows access to this field for all actions. A role can be provided optionally.      |

---

Check documentation for more possibilities
------------------------------------------

- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Global Configuration](./global-configuration.md)
- [Customization](./customization.md)
