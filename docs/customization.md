[Home](../README.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Templates](./templates.md) | **Customization**

# Customization

AutoGrid is extendable at multiple levels, from template overrides to core service logic.

<details>
<summary><strong>Overriding Templates</strong></summary>

### 1. Global Configuration
Configure default themes in `f0ska_auto_grid.yaml`.

```yaml
f0ska_auto_grid:
    template:
        theme: '@@F0skaAutoGrid/bootstrap_5'
        base: 'base.html.twig'
        form_themes: ['bootstrap_5_layout.html.twig']
```

### 2. File-based Overrides
Override templates by creating files in `templates/bundles/F0skaAutoGridBundle/`.

### 3. Route Parameters
Override the theme for a specific route in your routing configuration.

```yaml
admin_users:
    path: /admin/users
    controller: App\Controller\AdminController::users
    defaults:
        _autogrid_theme: '@@App/autogrid/custom_theme'
        _autogrid_form_themes: ['custom_form_layout.html.twig']
```

### 4. Entity Level Attributes
Override template areas for specific entities using `#[Template]`.

```php
use F0ska\AutoGridBundle\ValueObject\TemplateArea;

#[Attribute\Entity\Template([
    TemplateArea::ACTION_GRID => 'admin/user/custom_grid.html.twig'
])]
class User { ... }
```

### 5. Field Level Attributes
Use `#[ViewTemplate]` to render a specific property.

```php
#[ViewTemplate('admin/user/_avatar_cell.html.twig')]
private ?string $avatarPath = null;
```
</details>

<details>
<summary><strong>Custom Filter Conditions</strong></summary>

1. Implement [`FilterConditionInterface`](../src/Condition/FilterConditionInterface.php).
2. Register as a service with the `autogrid.filter_condition` tag and set `public: true`.
3. Use in `#[Filterable(condition: MyCondition::class)]`.

```php
// config/services.yaml
App\Filter\MyCustomCondition:
    tags: ['autogrid.filter_condition']
    public: true
```
</details>

<details>
<summary><strong>Custom View Services</strong></summary>

1. Implement [`ViewServiceInterface`](../src/View/ViewServiceInterface.php).
2. Register as a service with the `autogrid.view_service` tag and set `public: true`.
3. Use `#[ViewService(MyService::class)]`.

```php
// config/services.yaml
App\Service\MyCustomViewService:
    tags: ['autogrid.view_service']
    public: true
```
</details>

<details>
<summary><strong>Pure Virtual Columns</strong></summary>

Pure virtual columns display data not directly mapped to Doctrine properties.

**Important:**
*   Pure virtual columns are **read-only** in the grid (not filterable, sortable, or editable).
*   Avoid naming collisions with existing Doctrine-mapped properties.
*   If using `GridEvent` to populate data, ensure calculations are efficient. `ViewService` is preferred for per-row rendering logic.

**Implementation:**
1. Declare a public property in your entity.
2. Mark with `#[VirtualColumn]`.
3. Provide data via `#[ViewService]` (recommended) or `GridEvent` listener.

**Example (ViewService):**
```php
#[VirtualColumn]
#[ViewService(MyFullNameViewService::class)]
public ?string $fullName = null;
```

**Example (GridEvent):**
```php
class UserGridSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [GridEvent::EVENT_NAME => 'onGridEvent'];
    }

    public function onGridEvent(GridEvent $event): void
    {
        foreach ($event->getEntities() as $user) {
            $user->fullName = $user->getFirstName() . ' ' . $user->getLastName();
        }
    }
}
```
</details>

<details>
<summary><strong>Service Tags</strong></summary>

You can extend core functionality by registering services with these tags:

| Tag | Interface |
| :--- | :--- |
| `autogrid.action` | [`ActionInterface`](../src/Action/ActionInterface.php) |
| `autogrid.action.parameter` | [`ActionParameterInterface`](../src/ActionParameter/ActionParameterInterface.php) |
| `autogrid.customization` | [`CustomizationInterface`](../src/Customization/CustomizationInterface.php) |

All such services must be registered with the tag and set to `public: true`:

```yaml
# config/services.yaml
App\Service\MyCustomAction:
    tags: ['autogrid.action']
    public: true
```
</details>

<details>
<summary><strong>Custom Data Exchange</strong></summary>

The `customization` array is available throughout the AutoGrid lifecycle to store and retrieve arbitrary data. This is useful for passing information from your controller to `ViewServices`, Twig templates, or other customizations.

### Where to use it:
- **`AutoGridFactory`**: Pass data during grid creation:
  ```php
  $grid = $factory->create(User::class, customization: ['my_key' => 'my_value']);
  ```
- **`Parameters` Model**: Stores global grid-level custom data.
- **`FieldParameter` Model**: Stores field-level custom data.

### Accessing the data:
Inside a `ViewService` or `FieldTemplate`, you can access this data via the `FieldParameter` object:

```php
// In a ViewService
public function prepare(object $entity, FieldParameter $field): array
{
    $myValue = $field->parameters->customization['my_key'] ?? null;
    
    return ['value' => '...'];
}
```
</details>

<details>
<summary><strong>Handling Events</strong></summary>

AutoGrid dispatches events during the CRUD lifecycle, providing integration points without modifying core logic. 

### Event Names
All event names can optionally include a `.{gridId}` suffix to target specific grid instances. Additionally, `MassEvent` and `ExportEvent` support a `.{code}` suffix to target specific action codes.

### Available Events

| Event Name | Dispatched When | Event Class |
| :--- | :--- | :--- |
| `f0ska.autogrid.entity.save` | Before entity is persisted. | [`SaveEvent`](../src/Event/SaveEvent.php) |
| `f0ska.autogrid.entity.delete` | Before entity removal. | [`DeleteEvent`](../src/Event/DeleteEvent.php) |
| `f0ska.autogrid.entity.view` | When entity is loaded for view/edit. | [`ViewEvent`](../src/Event/ViewEvent.php) |
| `f0ska.autogrid.mass_action` | When a bulk action is triggered. | [`MassEvent`](../src/Event/MassEvent.php) |
| `f0ska.autogrid.export_action` | When an export action is triggered. | [`ExportEvent`](../src/Event/ExportEvent.php) |
| `f0ska.autogrid.grid.load` | When grid data is being prepared. | [`GridEvent`](../src/Event/GridEvent.php) |
| `f0ska.autogrid.error.show` | When an error occurs. | [`ErrorEvent`](../src/Event/ErrorEvent.php) |

**Example: Specific Action Listener**
```php
// Listen only to 'save' on the 'article_grid'
#[AsEventListener(event: 'f0ska.autogrid.entity.save.article_grid')]
public function onArticleSave(SaveEvent $event): void { ... }
```
</details>
