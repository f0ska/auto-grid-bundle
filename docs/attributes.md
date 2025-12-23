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

| Attribute                                                                                                                                                                                                                           | Description                                                                                                                                                                                                                                                                                                                                                                                                               |
|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [AdvancedFilter](../src/Attribute/Entity/AdvancedFilter.php)                                                                                                                                                                        | Enables the advanced filter feature.                                                                                                                                                                                                                                                                                                                                                                                      |
| [DefaultSort](../src/Attribute/Entity/DefaultSort.php)                                                                                                                                                                              | Allows you to define the default sort order for your data in the grid.                                                                                                                                                                                                                                                                                                                                                    |
| [DeleteButtonIn](../src/Attribute/Entity/DeleteButtonIn.php)                                                                                                                                                                        | Enables or disables the default delete button.                                                                                                                                                                                                                                                                                                                                                                            |
| [EditButtonIn](../src/Attribute/Entity/EditButtonIn.php)                                                                                                                                                                            | Enables or disables the default edit button.                                                                                                                                                                                                                                                                                                                                                                              |
| [ErrorTemplate](../src/Attribute/Entity/ErrorTemplate.php)                                                                                                                                                                          | Overrides the error page template.                                                                                                                                                                                                                                                                                                                                                                                        |
| [Fieldset.php](../src/Attribute/Entity/Fieldset.php)                                                                                                                                                                                | Groups fields on default form and view pages.                                                                                                                                                                                                                                                                                                                                                                             |
| [FormTemplate](../src/Attribute/Entity/FormTemplate.php)                                                                                                                                                                            | Overrides the form page template.                                                                                                                                                                                                                                                                                                                                                                                         |
| [FormThemes](../src/Attribute/Entity/FormThemes.php)                                                                                                                                                                                | Overrides form themes.                                                                                                                                                                                                                                                                                                                                                                                                    |
| [FormType](../src/Attribute/Entity/FormType.php)                                                                                                                                                                                    | Provides your own form definition. You can can provide different form definition for diferent action (`create`, `edit`, `filter`, `advanced_filter`).                                                                                                                                                                                                                                                                     |
| [GridTemplate](../src/Attribute/Entity/GridTemplate.php)                                                                                                                                                                            | Overrides the grid page template.                                                                                                                                                                                                                                                                                                                                                                                         |
| [HeaderActionTemplate](../src/Attribute/Entity/HeaderActionTemplate.php)                                                                                                                                                            | Adds your custom header action template.                                                                                                                                                                                                                                                                                                                                                                                  |
| [HtmlClasses](../src/Attribute/Entity/HtmlClasses.php)                                                                                                                                                                              | Sets the HTML classes for the grid table.                                                                                                                                                                                                                                                                                                                                                                                 |
| [MassAction](../src/Attribute/Entity/MassAction.php)                                                                                                                                                                                | Provides a simple mass-action feature. Triggers [MassEvent](../src/Event/MassEvent.php). Does nothing by default.                                                                                                                                                                                                                                                                                                         |
| [PageLimits](../src/Attribute/Entity/PageLimits.php)                                                                                                                                                                                | Customizes the page limits select buttons.                                                                                                                                                                                                                                                                                                                                                                                |
| [RedirectOnSubmit](../src/Attribute/Entity/RedirectOnSubmit.php)                                                                                                                                                                    | Defines the redirect action name after creating or updating a form.                                                                                                                                                                                                                                                                                                                                                       |
| [RouteCreate](../src/Attribute/Entity/RouteCreate.php)<br/>[RouteDelete](../src/Attribute/Entity/RouteDelete.php)<br/>[RouteEdit](../src/Attribute/Entity/RouteEdit.php)<br/>[RouteView.php](../src/Attribute/Entity/RouteView.php) | These attributes allow the specification of custom routes for various actions.<ul><li>The `route` argument defines the custom route name. If skipped, the action name uses instead.</li><li>The `parameters` argument specifies the parameter names used in the route, with values taken from the factory's route parameters (see [Optional Factory Arguments](optional-factory-arguments.md)) or from the current route. |
| [Title](../src/Attribute/Entity/Title.php)                                                                                                                                                                                          | Sets the title for your entity.                                                                                                                                                                                                                                                                                                                                                                                           |
| [ViewButtonIn](../src/Attribute/Entity/ViewButtonIn.php)                                                                                                                                                                            | Enables or disables the default view button.                                                                                                                                                                                                                                                                                                                                                                              |
| [ViewTemplate](../src/Attribute/Entity/ViewTemplate.php)                                                                                                                                                                            | Overrides the view page template.                                                                                                                                                                                                                                                                                                                                                                                         |

### Entity access attributes

| Attribute                                                                            | Description                                                                        |
|--------------------------------------------------------------------------------------|------------------------------------------------------------------------------------|
| [DisallowActionsByDefault](../src/Attribute/Permission/DisallowActionsByDefault.php) | Inverts permissions for all AutoGrid actions. By default, all actions are allowed. |
| [DisallowFieldsByDefault](../src/Attribute/Permission/DisallowFieldsByDefault.php)   | Inverts permissions for all AutoGrid fields. By default, all fields are allowed.   |
| [Allow](../src/Attribute/Permission/Allow.php)                                       | Allows specific actions, with an optional role.                                    |
| [Forbid](../src/Attribute/Permission/Forbid.php)                                     | Disallows specific actions, with an optional role.                                 |
| [AllowAll](../src/Attribute/Permission/Allow.php)                                    | Allows all actions, with an optional role.                                         |
| [ForbidAll](../src/Attribute/Permission/Forbid.php)                                  | Disallows all actions, with an optional role.                                      |

## Entity property attributes

| Attribute                                                               | Description                                                                                          |
|-------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------|
| [ActionTemplate](../src/Attribute/EntityField/ActionTemplate.php)       | Adds a custom template to the action column for every row.                                           |
| [AddToFieldset](../src/Attribute/EntityField/AddToFieldset.php)         | Adds a field to a group defined in [Fieldset](../src/Attribute/Entity/Fieldset.php)                  |
| [AssociatedField](../src/Attribute/EntityField/AssociatedField.php)     | Creates "virtual" fields from associated entities.                                                   |
| [CanFilter](../src/Attribute/EntityField/CanFilter.php)                 | Controls the column filter feature.                                                                  |
| [CanSort](../src/Attribute/EntityField/CanSort.php)                     | Controls the column sort feature.                                                                    |
| [ColumnHeaderClass](../src/Attribute/EntityField/ColumnHeaderClass.php) | Adds a class to the grid table column header.                                                        |
| [ColumnValueClass](../src/Attribute/EntityField/ColumnValueClass.php)   | Adds a class to the grid table column value cell.                                                    |
| [FormOptions](../src/Attribute/EntityField/FormOptions.php)             | Overrides form options for the form field.                                                           |
| [FormType](../src/Attribute/EntityField/FormType.php)                   | Overrides the form type for the form field.                                                          |
| [GridTruncate](../src/Attribute/EntityField/GridTruncate.php)           | Sets the maximum number of characters displayed in the grid cell.                                    |
| [Label](../src/Attribute/EntityField/Label.php)                         | Overrides the field label.                                                                           |
| [MultipleFilter](../src/Attribute/EntityField/MultipleFilter.php)       | Allows multiple filters on forms with choices. Does not work for OneToMany and ManyToMany relations. |
| [Position](../src/Attribute/EntityField/Position.php)                   | Sets the field position, which can be positive or negative. Default is `0` for all fields.           |
| [RangeFilter](../src/Attribute/EntityField/RangeFilter.php)             | Creates a range filter instead of a single one. Does not work for all field types.                   |
| [ValuePrefix](../src/Attribute/EntityField/ValuePrefix.php)             | Adds a prefix to the displayed value.                                                                |
| [ValueSuffix](../src/Attribute/EntityField/ValueSuffix.php)             | Adds a suffix to the displayed value.                                                                |

### Entity property access attributes

| Attribute                                              | Description                                                                             |
|--------------------------------------------------------|-----------------------------------------------------------------------------------------|
| [Allow](../src/Attribute/Permission/Allow.php)         | Allows access to this field for specific actions. A role can be provided optionally.    |
| [Forbid](../src/Attribute/Permission/Forbid.php)       | Disallows access to this field for specific actions. A role can be provided optionally. |
| [AllowAll](../src/Attribute/Permission/AllowAll.php)   | Allows access to this field for all actions. A role can be provided optionally.         |
| [ForbidAll](../src/Attribute/Permission/ForbidAll.php) | Disallows access to this field for all actions. A role can be provided optionally.      |

Check documentation for more possibilities
------------------------------------------

- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Global Configuration](./global-configuration.md)
- [Customization](./customization.md)
