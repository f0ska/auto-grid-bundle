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
Override template areas for specific entities using `#[Template]`. This attribute is repeatable, so add it multiple
times for the areas you want to replace.

```php
use F0ska\AutoGridBundle\ValueObject\TemplateArea;

#[Attribute\Entity\Template(
    area: TemplateArea::ACTION_GRID,
    templatePath: 'admin/user/custom_grid.html.twig'
)]
#[Attribute\Entity\Template(
    area: TemplateArea::BEFORE,
    templatePath: 'admin/user/grid_intro.html.twig'
)]
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
2. Register as a service with the `autogrid.filter_condition` tag.
3. Use in `#[Filterable(condition: MyCustomCondition::class)]`.

```yaml
# config/services.yaml
App\Filter\MyCustomCondition:
    tags: ['autogrid.filter_condition']
```
</details>

<details>
<summary><strong>Custom View Services</strong></summary>

1. Implement [`ViewServiceInterface`](../src/View/ViewServiceInterface.php).
2. Register as a service with the `autogrid.view_service` tag.
3. Use `#[ViewService(MyCustomViewService::class)]`.

```yaml
# config/services.yaml
App\Service\MyCustomViewService:
    tags: ['autogrid.view_service']
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
<summary><strong>Custom Actions</strong></summary>

1. Implement [`ActionInterface`](../src/Action/ActionInterface.php).
2. Register the service with the `autogrid.action` tag.

```yaml
# config/services.yaml
App\AutoGrid\MyAction:
    tags: ['autogrid.action']
```
</details>

<details>
<summary><strong>Custom Action Parameters</strong></summary>

1. Implement [`ActionParameterInterface`](../src/ActionParameter/ActionParameterInterface.php).
2. Register the service with the `autogrid.action.parameter` tag.

```yaml
# config/services.yaml
App\AutoGrid\MyActionParameter:
    tags: ['autogrid.action.parameter']
```
</details>

<details>
<summary><strong>Customizations</strong></summary>

1. Implement [`CustomizationInterface`](../src/Customization/CustomizationInterface.php).
2. Register the service with the `autogrid.customization` tag.

```php
use F0ska\AutoGridBundle\Customization\CustomizationInterface;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;

final class MyCustomization implements CustomizationInterface
{
    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
    }
}
```

```yaml
# config/services.yaml
App\AutoGrid\MyCustomization:
    tags: ['autogrid.customization']
```

Customizations run after AutoGrid has already built the full grid context and prepared the view state, but before anything is rendered or any action-specific database work is executed.

Every service tagged with `autogrid.customization` runs for every grid. The `customization` array is only input data for your extension code, not a selector for which customization should execute.

That means a customization receives:

- resolved metadata and attributes
- built field definitions and permissions
- prepared form views and pagination state
- request parameters and controller-provided `customization` data

That also means customizations are **not** part of the context-building phase itself.

Use this extension point when you want to work with an already prepared grid state.

Use other extension points for other stages:

- attributes, configuration, and optional factory arguments for earlier structural setup
- events, custom actions, and view services for later execution or rendering concerns
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
| `f0ska.autogrid.entity.create` | When a new entity instance is created before form processing. | [`EntityEvent`](../src/Event/EntityEvent.php) |
| `f0ska.autogrid.entity.edit` | When entity is loaded for edit before form processing. | [`EntityEvent`](../src/Event/EntityEvent.php) |
| `f0ska.autogrid.entity.view` | When entity is loaded for detail view. | [`EntityEvent`](../src/Event/EntityEvent.php) |
| `f0ska.autogrid.mass_action` | When a bulk action is triggered. | [`MassEvent`](../src/Event/MassEvent.php) |
| `f0ska.autogrid.export_action` | When an export action is triggered. | [`ExportEvent`](../src/Event/ExportEvent.php) |
| `f0ska.autogrid.entity.grid` | When grid data is being prepared. | [`GridEvent`](../src/Event/GridEvent.php) |
| `f0ska.autogrid.error.show` | When an error occurs. | [`ErrorEvent`](../src/Event/ErrorEvent.php) |

**Example: Specific Action Listener**
```php
// Listen only to 'save' on the 'article_grid'
#[AsEventListener(event: 'f0ska.autogrid.entity.save.article_grid')]
public function onArticleSave(SaveEvent $event): void { ... }
```
</details>
