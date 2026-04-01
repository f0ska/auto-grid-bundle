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
class DemoOne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Attribute\EntityField\Filterable]
    #[Attribute\EntityField\Sortable(direction: 'asc')]
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
| [Permission](../src/Attribute/Permission.php)                                       | Allows or disallows specific actions, with an optional role and grid context. Use `#[Permission(action: 'delete', allow: false)]` to forbid an action globally. Use `#[Permission(action: 'edit', gridId: 'my_grid')]` for grid-specific rules. |

## Entity property (field) attributes

| Attribute                                                           | Description                                                                                                                          |
|---------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| [AddToFieldset](../src/Attribute/EntityField/AddToFieldset.php)     | Adds a field to a group defined in [Fieldset](../src/Attribute/Entity/Fieldset.php), allowing better organization of related fields. |
| [AssociatedField](../src/Attribute/EntityField/AssociatedField.php) | Creates "virtual" fields from associated entities (relations).                                                                       |
| [Filterable](../src/Attribute/EntityField/Filterable.php)           | Enables filtering for this field. Accepts optional `condition`, `formType`, and `formOptions` to customize filter behavior. See [Filterable in depth](#filterable-in-depth). |
| [Sortable](../src/Attribute/EntityField/Sortable.php)               | Makes a column sortable. The `direction` (`asc` or `desc`) and `priority` arguments can be used to set the default sort order.       |
| [ColumnHtmlClass](../src/Attribute/EntityField/ColumnHtmlClass.php) | Adds HTML classes to the grid table column for styling purposes.                                                                     |
| [FieldTemplate](../src/Attribute/EntityField/FieldTemplate.php)     | Overrides the field template for custom rendering.                                                                                   |
| [FormOptions](../src/Attribute/EntityField/FormOptions.php)         | Overrides form options for the form field.                                                                                           |
| [FormType](../src/Attribute/EntityField/FormType.php)               | Overrides the form type for the form field.                                                                                          |
| [GridTruncate](../src/Attribute/EntityField/GridTruncate.php)       | Sets the maximum number of characters displayed in the grid cell.                                                                    |
| [Label](../src/Attribute/EntityField/Label.php)                     | Overrides the field label.                                                                                                           |
| [Position](../src/Attribute/EntityField/Position.php)               | Sets the field position, which can be positive or negative. Default is `0` for all fields.                                           |
| [ValuePrefix](../src/Attribute/EntityField/ValuePrefix.php)         | Adds a prefix to displayed values.                                                                                                   |
| [ValueSuffix](../src/Attribute/EntityField/ValueSuffix.php)         | Adds a suffix to displayed values.                                                                                                   |

## Filterable in depth

`#[Filterable]` enables filtering for a field and auto-guesses the filter form type and condition from Doctrine metadata.

```php
#[Filterable]
private ?string $name = null;
```

### Parameters

| Parameter     | Type      | Default | Description                                                                                              |
|---------------|-----------|---------|----------------------------------------------------------------------------------------------------------|
| `enabled`     | `bool`    | `true`  | Enable or disable filtering for this field.                                                              |
| `condition`   | `?string` | `null`  | Filter condition class (implements `FilterConditionInterface`). Auto-guessed from field type if `null`.  |
| `formType`    | `?string` | `null`  | Override the filter form type class. Auto-guessed if `null`.                                             |
| `formOptions` | `array`   | `[]`    | Override the filter form options. Merged on top of auto-guessed options when `formType` is also set.     |

### Built-in filter conditions

| Class                                                                      | Behavior                                                                               | Auto-guessed for                              |
|----------------------------------------------------------------------------|----------------------------------------------------------------------------------------|-----------------------------------------------|
| [ExactCondition](../src/Condition/ExactCondition.php)                      | `column = :value`                                                                      | integers, booleans, enums, and most types     |
| [StartsWithCondition](../src/Condition/StartsWithCondition.php)            | `column LIKE 'value%'`                                                                 | `string` columns                              |
| [ContainsCondition](../src/Condition/ContainsCondition.php)                | `column LIKE '%value%'` — supports arrays (OR of LIKE per value)                       | `text`, `json`, `array` columns               |
| [InCondition](../src/Condition/InCondition.php)                            | `column IN (:values)` — for multi-select on scalar/relation fields                     | not auto-guessed; use explicitly              |
| [RangeCondition](../src/Condition/RangeCondition.php)                      | `column >= :from AND column <= :to` — renders two inputs                               | date/time columns when `form_date_as_range` is enabled |
| [AssociationCondition](../src/Condition/AssociationCondition.php)          | `IN` with `innerJoin` for ToMany relations                                             | association fields                            |

### Examples

**Range filter on a date field:**
```php
use F0ska\AutoGridBundle\Condition\RangeCondition;

#[Filterable(condition: RangeCondition::class)]
private ?\DateTimeImmutable $publishAt = null;
```

**Multi-select filter using a choice field:**
```php
use F0ska\AutoGridBundle\Condition\InCondition;

#[Filterable(condition: InCondition::class, formOptions: ['multiple' => true])]
private ?string $status = null;
```

**Custom form type for the filter:**
```php
use App\Form\MyCustomFilterType;

#[Filterable(formType: MyCustomFilterType::class, formOptions: ['required' => false])]
private ?string $category = null;
```

**Custom filter condition (see [Customization](customization.md)):**
```php
use App\Filter\MyCustomCondition;

#[Filterable(condition: MyCustomCondition::class)]
private ?string $tags = null;
```

## Entity property access attributes

| Attribute                                              | Description                                                                             |
|--------------------------------------------------------|-----------------------------------------------------------------------------------------|
| [Permission](../src/Attribute/Permission.php)         | Allows or disallows access to this field for specific actions, with optional role and grid context. |

---

Check documentation for more possibilities
------------------------------------------

- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Global Configuration](./global-configuration.md)
- [Customization](./customization.md)
