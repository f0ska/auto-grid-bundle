[Home](../README.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | **Attributes** | [Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)

# Attributes

Attributes configure AutoGrid at the class and property levels.

> **Note:** All user-facing strings (e.g., `Title`, `Label`) are processed through Symfony's Translator.

## Class Level Attributes

<details>
<summary><strong>ActionButtonDisplay</strong></summary>

Configures action button visibility.

```php
#[Attribute\Entity\ActionButtonDisplay(showDelete: false)]
class User { ... }
```
</details>

<details>
<summary><strong>ActionFormType</strong></summary>

Defines custom Form classes for CRUD actions.

```php
#[Attribute\Entity\ActionFormType(create: UserCreateType::class, edit: UserEditType::class)]
class User { ... }
```
</details>

<details>
<summary><strong>ActionRoute</strong></summary>

Maps custom routes or parameters to CRUD actions.

```php
#[Attribute\Entity\ActionRoute(action: 'view', route: 'app_custom_view')]
class User { ... }
```
</details>

<details>
<summary><strong>AdvancedFilter</strong></summary>

Enables complex filtering UI.

```php
#[Attribute\Entity\AdvancedFilter(true)]
class User { ... }
```
</details>

<details>
<summary><strong>ExportAction</strong></summary>

Adds an export button. Dispatches `ExportEvent` on click.

```php
#[Attribute\Entity\ExportAction]
class User { ... }
```
</details>

<details>
<summary><strong>Fieldset</strong></summary>

Groups fields in form and view pages. Use `#[AddToFieldset]` on properties to assign them.

```php
#[Fieldset(name: 'General', class: 'col-md-6')]
#[Fieldset(name: 'Settings', class: 'col-md-6')]
class User { ... }
```
</details>

<details>
<summary><strong>FormThemes</strong></summary>

Applies custom Twig themes to generated forms.

```php
#[Attribute\Entity\FormThemes(['@App/form/custom_theme.html.twig'])]
class User { ... }
```
</details>

<details>
<summary><strong>HasCustomAction</strong></summary>

Indicates the grid contains non-standard actions, ensuring correct rendering.

```php
#[Attribute\Entity\HasCustomAction(true)]
class User { ... }
```
</details>

<details>
<summary><strong>HtmlClass</strong></summary>

Sets CSS classes for the grid table and specific columns.

```php
#[Attribute\Entity\HtmlClass(
    actionColumn: "col-2",
    massActionColumn: "col-1",
)]
class User { ... }
```
</details>

<details>
<summary><strong>MassAction</strong></summary>

Enables bulk actions on selected rows; dispatches `MassEvent`.

```php
#[Attribute\Entity\MassAction]
class User { ... }
```
</details>

<details>
<summary><strong>PageLimits</strong></summary>

Defines items-per-page options.

```php
#[Attribute\Entity\PageLimits([25, 50, 100, 500])]
class User { ... }
```
</details>

<details>
<summary><strong>RedirectOnSubmit</strong></summary>

Target internal action name (e.g., `grid`, `view`) after successful save.

```php
#[Attribute\Entity\RedirectOnSubmit('grid')]
class User { ... }
```
</details>

<details>
<summary><strong>Template</strong></summary>

Overrides specific template areas (see `TemplateArea.php`).

```php
use F0ska\AutoGridBundle\ValueObject\TemplateArea;

#[Attribute\Entity\Template([
    TemplateArea::ACTION_GRID => 'admin/user/custom_grid.html.twig'
])]
class User { ... }
```
</details>

<details>
<summary><strong>Title</strong></summary>

Sets the entity display title.

```php
#[Attribute\Entity\Title("User Management")]
class User { ... }
```
</details>

## Access Control

<details>
<summary><strong>DisallowActionsByDefault</strong></summary>

Restricts all actions unless explicitly granted via `#[Permission]`.

```php
#[DisallowActionsByDefault]
#[Permission(action: 'grid')]
class User { ... }
```
</details>

<details>
<summary><strong>DisallowFieldsByDefault</strong></summary>

Hides all fields unless explicitly granted via `#[Permission]`.

```php
#[DisallowFieldsByDefault]
class User {
    #[Permission]
    private ?string $name = null;
}
```
</details>

<details>
<summary><strong>Permission</strong></summary>

Controls Action (class level) or Field (property level) visibility.

**Parameters:**
- `action`: Specific action (e.g., `grid`, `edit`).
- `allow`: Boolean (default: true).
- `role`: Optional Symfony role.
- `gridId`: Optional restriction to a specific grid.

```php
#[Permission(action: 'delete', role: 'ROLE_ADMIN', allow: true)]
class User { ... }
```
</details>

## Property Level Attributes

<details>
<summary><strong>AddToFieldset</strong></summary>

Assigns a property to a group defined by `#[Fieldset]`.

```php
#[AddToFieldset('Settings')]
private ?bool $notificationsEnabled = null;
```
</details>

<details>
<summary><strong>AssociatedField</strong></summary>

Displays a property from a related entity.

```php
#[ORM\ManyToOne]
#[AssociatedField(name: 'username', label: 'Author')]
#[Permission(allow: false)] // Hides the original entity object
private ?User $author = null;
```
</details>

<details>
<summary><strong>ColumnHtmlClass</strong></summary>

Sets CSS classes for headers and data cells.

```php
#[ColumnHtmlClass(columnClass: "w-25", headerClass: "text-center", valueClass: "fw-bold")]
private ?string $status = null;
```
</details>

<details>
<summary><strong>Filterable</strong></summary>

Enables grid searching/filtering.

**Parameters:**
- `enabled`: Enable or disable filtering for this field.
- `condition`: The condition class name. Available conditions:
  - [`AssociationCondition`](../src/Condition/AssociationCondition.php): For filtering entity relations.
  - [`ContainsCondition`](../src/Condition/ContainsCondition.php): SQL `LIKE %value%`.
  - [`ExactCondition`](../src/Condition/ExactCondition.php): SQL `=` comparison.
  - [`InCondition`](../src/Condition/InCondition.php): SQL `IN (...)` comparison.
  - [`RangeCondition`](../src/Condition/RangeCondition.php): For numerical or date ranges (`from`/`to` array keys).
  - [`StartsWithCondition`](../src/Condition/StartsWithCondition.php): SQL `LIKE value%`.
- `formType`: Override the guessed filter form type.
- `formOptions`: Override the guessed filter form options.

```php
#[Filterable(condition: ContainsCondition::class)]
private ?string $description = null;
```
</details>

<details>
<summary><strong>FormType / FormOptions</strong></summary>

Customizes the generated form field.

```php
#[FormType(PasswordType::class)]
#[FormOptions(['help' => 'Min 8 chars'])]
private ?string $plainPassword = null;
```
</details>

<details>
<summary><strong>GridTruncate</strong></summary>

Limits text length in the grid.

```php
#[GridTruncate(100)]
private ?string $content = null;
```
</details>

<details>
<summary><strong>Label</strong></summary>

Sets the field label.

```php
#[Label("E-mail")]
private ?string $email = null;
```
</details>

<details>
<summary><strong>Position</strong></summary>

Defines display order (lower value = earlier position).

```php
#[Position(-10)]
private ?int $id = null;
```
</details>

<details>
<summary><strong>Sortable</strong></summary>

Enables column sorting.

```php
#[Sortable(direction: 'desc')]
private ?int $id = null;
```
</details>

<details>
<summary><strong>ValuePrefix / ValueSuffix</strong></summary>

Prepends or appends text to the value.

```php
#[ValuePrefix("$ ")]
private ?float $price = null;
```
</details>

<details>
<summary><strong>ViewService</strong></summary>

Assigns a `ViewServiceInterface` service to format field data.

```php
#[ViewService(App\Service\StatusBadgeService::class)]
private ?string $status = null;
```
</details>

<details>
<summary><strong>ViewTemplate</strong></summary>

Specifies a custom Twig template for rendering.

```php
#[ViewTemplate('admin/user/_avatar.html.twig')]
private ?string $avatarPath = null;
```
</details>

<details>
<summary><strong>VirtualColumn</strong></summary>

Marks a property as non-Doctrine-mapped, for computed read-only data.

**Parameters:**
- `dql`: (Optional) A DQL subquery string to automatically populate the field. Use `{this}` for current record alias and `{root}` for main entity root alias.

**Notes:**
- DQL-backed virtual columns are read-only.
- They can be made sortable with `#[Sortable]`.
- Filtering is not supported for DQL-backed virtual columns. If you need filtering, use a regular mapped field.

```php
// Computed data from DQL subquery
#[VirtualColumn(dql: "SELECT COUNT(c.id) FROM App\Entity\Comment c WHERE c.post = {this}")]
public ?int $commentCount = null;
```
</details>

---

[Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)
