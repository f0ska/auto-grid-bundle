[Index](./index.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | **Customization**

# Customization

AutoGrid is built to be extended. You can override everything from a single icon to the entire rendering logic.

## Overriding Templates

There are five ways to customize the UI, from global defaults to specific fields:

### 1. Global Configuration
Set the default theme and base templates for all grids in `f0ska_auto_grid.yaml`.

```yaml
f0ska_auto_grid:
    template:
        theme: '@@F0skaAutoGrid/bootstrap_5'
        base: 'base.html.twig'
        form_themes: ['bootstrap_5_layout.html.twig']
```

### 2. File-based Overrides
Override bundle templates by creating files in `templates/bundles/F0skaAutoGridBundle/`.
For example, to change the grid layout, create `templates/bundles/F0skaAutoGridBundle/grid/grid.html.twig`.

### 3. Route Parameters
Override the theme for a specific route in your routing configuration.

```yaml
# config/routes.yaml
admin_users:
    path: /admin/users
    controller: App\Controller\AdminController::users
    defaults:
        _autogrid_theme: '@@App/autogrid/custom_theme'
        _autogrid_form_themes: ['custom_form_layout.html.twig']
```

### 4. Entity Level Attributes
Use the `#[Template]` attribute on your Entity class to override specific template areas for that entity only.

```php
use F0ska\AutoGridBundle\ValueObject\TemplateArea;

#[Attribute\Entity\Template([
    TemplateArea::ACTION_GRID => 'admin/user/custom_grid.html.twig'
])]
class User { ... }
```

### 5. Field Level Attributes
Use the `#[ViewTemplate]` attribute to change how a single property is rendered in the grid.

```php
#[ViewTemplate('admin/user/_avatar_cell.html.twig')]
private ?string $avatarPath = null;
```

---

## Advanced Customization

<details>
<summary><strong>Custom Filter Conditions</strong>: Control how filters apply to the QueryBuilder.</summary>

1. Implement `FilterConditionInterface`.
2. Register as a service.
3. Use in `#[Filterable(condition: MyCondition::class)]`.

```php
class MyCustomCondition implements FilterConditionInterface
{
    public function apply(QueryBuilder $qb, string $column, FieldParameter $field, mixed $value): void
    {
        $alias = uniqid('p');
        $qb->andWhere("$column > :$alias")->setParameter($alias, $value);
    }
}
```
</details>

<details>
<summary><strong>Custom View Services</strong>: Encapsulate complex rendering logic in a Symfony service.</summary>

1.  Implement `F0ska\AutoGridBundle\View\ViewServiceInterface`.
2.  (Optional) Leverage Logic Providers (`FieldValueProvider`, `ChoiceProvider`, etc.).
3.  Assign to a property using `#[ViewService(MyService::class)]`.

```php
namespace App\Service;

use F0ska\AutoGridBundle\View\ViewServiceInterface;
use F0ska\AutoGridBundle\Service\Provider\FieldValueProvider;

class MyCustomViewService implements ViewServiceInterface
{
    public function __construct(private FieldValueProvider $valueProvider) {}

    public function prepare(array $context): array
    {
        $value = $this->valueProvider->getValue($context['entity'], $context['field']);
        
        return [
            'value' => $value,
            'extra_info' => $this->getSomeExternalData($value)
        ];
    }
}
```
</details>

<details>
<summary><strong>Services & Tags</strong>: Extend the core logic.</summary>

You can register custom services using these tags:
- `autogrid.action`: New grid actions.
- `autogrid.action.parameter`: Custom parameters for actions.
- `autogrid.filter_condition`: Custom search logic.
- `autogrid.customization`: Global grid logic modifiers.

Refer to [services.yaml](../config/services.yaml) for core implementations.
</details>

<details>
<summary><strong>Handling Events</strong>: Hook into the CRUD lifecycle.</summary>

AutoGrid dispatches events for key lifecycle moments. Register a listener or subscriber to handle them.

### Dynamic Event Naming
All events are dispatched with an optional `.{gridId}` suffix, allowing you to target a specific grid instance.
Additionally, `MassEvent` and `ExportEvent` include a `.{code}` suffix to target specific action codes.

**Example: Specific Save Action**
```php
// Listen only to 'save' on the 'article_grid'
#[AsEventListener(event: 'f0ska.autogrid.entity.save.article_grid')]
public function onArticleSave(SaveEvent $event): void { ... }
```

**Example: Specific Export Code**
```php
// Listen only to the 'csv_export' action
#[AsEventListener(event: 'f0ska.autogrid.export_action.csv_export')]
public function onCsvExport(ExportEvent $event): void { ... }
```

### Event Lifecycle Table

| Event Name | Dispatched When... |
| :--- | :--- |
| `f0ska.autogrid.entity.save` | **Before** an entity is persisted/updated in the database. |
| `f0ska.autogrid.entity.delete` | **Before** an entity is removed from the database. |
| `f0ska.autogrid.entity.view` | When an entity is loaded for the view action. |
| `f0ska.autogrid.mass_action` | When a bulk action is triggered. |
| `f0ska.autogrid.export_action` | When an export action is triggered. |
| `f0ska.autogrid.error.show` | When an error occurs during processing. |

</details>

---

[Index](./index.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Templates](./templates.md)
