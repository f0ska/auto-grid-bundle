[Index](./index.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | **Attributes** | [Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)

# Attributes

Attributes are the primary way to configure **AutoGrid**. Use them on your Entity class or its properties.

> **Note on Translation:** All user-facing strings (like `Title` and `Label`) are automatically passed through Symfony's Translator. You can provide translation keys or plain strings directly in these attributes.

## Class Level Attributes

<details>
<summary><strong>ActionButtonDisplay</strong>: Controls visibility of default action buttons.</summary>

```php
// Hide delete button for everyone
#[Attribute\Entity\ActionButtonDisplay(showDelete: false)]
class User { ... }
```
</details>

<details>
<summary><strong>ActionFormType</strong>: Use specific Symfony Forms for different actions.</summary>

```php
#[Attribute\Entity\ActionFormType(
    create: UserCreateType::class, 
    edit: UserEditType::class
)]
class User { ... }
```
</details>

<details>
<summary><strong>ActionRoute</strong>: Define custom routes or extra parameters for actions.</summary>

```php
#[Attribute\Entity\ActionRoute(action: 'view', route: 'app_custom_view')]
class User { ... }
```
</details>

<details>
<summary><strong>AdvancedFilter</strong>: Enables the complex/multi-criteria filter UI.</summary>

```php
#[Attribute\Entity\AdvancedFilter(true)]
class User { ... }
```
</details>

<details>
<summary><strong>ExportAction</strong>: Adds an export button to the grid.</summary>

Dispatches `ExportEvent` when clicked. You are responsible for the actual file generation (CSV, Excel, etc.) in an event listener.

```php
#[Attribute\Entity\ExportAction]
class User { ... }
```
</details>

<details>
<summary><strong>Fieldset</strong>: Groups fields into logical sections or tabs.</summary>

Used to organize fields in the form and view pages. Define them on the class and assign fields using `#[AddToFieldset]`.

```php
#[Fieldset(name: 'General', class: 'col-md-6')]
#[Fieldset(name: 'Settings', class: 'col-md-6')]
class User { ... }
```
</details>

<details>
<summary><strong>FormThemes</strong>: Apply custom Twig themes to AutoGrid-generated forms.</summary>

```php
#[Attribute\Entity\FormThemes(['@App/form/custom_theme.html.twig'])]
class User { ... }
```
</details>

<details>
<summary><strong>HasCustomAction</strong>: Tells AutoGrid that your grid has custom actions.</summary>

Use this if you have disabled all standard actions but added your own custom actions. It ensures the action column/logic is still rendered correctly.

```php
#[Attribute\Entity\HasCustomAction(true)]
class User { ... }
```
</details>

<details>
<summary><strong>HtmlClass</strong>: Add CSS classes to the grid table.</summary>

```php
#[Attribute\Entity\HtmlClass("table-striped table-hover")]
class User { ... }
```
</details>

<details>
<summary><strong>MassAction</strong>: Enables bulk actions on selected rows.</summary>

Dispatches `MassEvent`. Useful for bulk delete, status updates, etc.

```php
#[Attribute\Entity\MassAction]
class User { ... }
```
</details>

<details>
<summary><strong>PageLimits</strong>: Customize "items per page" options.</summary>

```php
#[Attribute\Entity\PageLimits([25, 50, 100, 500])]
class User { ... }
```
</details>

<details>
<summary><strong>RedirectOnSubmit</strong>: Target action after a successful form save.</summary>

You must provide the internal action name (e.g., `grid`, `view`, `edit`, `create`).

```php
#[Attribute\Entity\RedirectOnSubmit('grid')] // Redirect back to grid view
class User { ... }
```
</details>

<details>
<summary><strong>Template</strong>: Override specific AutoGrid template areas.</summary>

Available template areas are defined in [**TemplateArea.php**](../src/ValueObject/TemplateArea.php) (e.g., `action.grid`, `grid.row_class`, `fieldset.view`).

```php
use F0ska\AutoGridBundle\ValueObject\TemplateArea;

#[Attribute\Entity\Template([
    TemplateArea::ACTION_GRID => 'admin/user/custom_grid.html.twig'
])]
class User { ... }
```
</details>

<details>
<summary><strong>Title</strong>: Sets the entity display name in the UI (Translatable).</summary>

```php
#[Attribute\Entity\Title("User Management")]
class User { ... }
```
</details>

## Access Control

<details>
<summary><strong>DisallowActionsByDefault</strong>: Deny all actions unless explicitly allowed.</summary>

Use this for a "Secure by Default" approach. All actions (`grid`, `view`, `edit`, `delete`, etc.) will be hidden and restricted until you add explicit `#[Permission]` attributes.

```php
#[DisallowActionsByDefault]
#[Permission(action: 'grid')] // Only 'grid' is allowed now
class User { ... }
```
</details>

<details>
<summary><strong>DisallowFieldsByDefault</strong>: Hide all fields unless explicitly allowed.</summary>

```php
#[DisallowFieldsByDefault]
class User {
    #[Permission] // Explicitly show this field in all views
    private ?string $name = null;
}
```
</details>

<details>
<summary><strong>Permission</strong>: Granular access control for Actions and Fields.</summary>

This attribute is highly versatile and can be used on the **Class** (to control actions) or on **Properties** (to control field visibility). It is repeatable.

### Parameters:
- `action`: (string) The action to control (e.g., `grid`, `view`, `create`, `edit`, `delete`, `export`, `mass`). If null, it's a global rule.
- `allow`: (bool) Set to `false` to deny access. Default is `true`.
- `role`: (mixed) Restrict access to users with specific Symfony roles (string or array).
- `gridId`: (string) Apply the rule only when the grid has a specific ID.

### Understanding Permission Logic:
Permissions are resolved in order. If a `role` is specified:
1.  If the user **has** the role, the `allow` value is used directly.
2.  If the user **does NOT** have the role, the **inverse** of `allow` is used.

> **Tip:** To create a strict "Admins Only" rule, use two attributes: one to deny everyone, and one to allow admins.

### Common Use Cases:

#### 1. Role-Based Action Control (Admins Only)
```php
// 1. Deny 'delete' for everyone
#[Permission(action: 'delete', allow: false)]
// 2. Explicitly allow 'delete' for admins
#[Permission(action: 'delete', role: 'ROLE_ADMIN', allow: true)]
class User { ... }
```

#### 2. Simple Role Restriction
```php
// Only users with ROLE_MANAGER (or higher) can use the export action
#[Permission(action: 'export', role: 'ROLE_MANAGER', allow: true)]
class User { ... }
```

#### 3. Global Field Visibility
```php
// Hide a sensitive field from ALL AutoGrid views (grid, view, edit, etc.) for everyone
#[Permission(allow: false)]
private ?string $internalNote = null;
```

#### 4. Action-Specific Field Visibility
```php
// Show field in detailed view, but hide it in the main grid list
#[Permission(action: 'grid', allow: false)]
#[Permission(action: 'view', allow: true)]
private ?string $longDescription = null;
```

#### 5. Context-Aware (gridId) Visibility
```php
// Hide a field only when displayed in a specific parent grid
// Useful for virtual fields from [AssociatedField]
#[Permission(gridId: 'article_list', allow: false)]
private ?string $userEmail = null;
```
</details>

## Property Level Attributes

<details>
<summary><strong>AddToFieldset</strong>: Assign a field to a defined group.</summary>

```php
#[AddToFieldset('Settings')]
private ?bool $notificationsEnabled = null;
```
</details>

<details>
<summary><strong>AssociatedField</strong>: Pull specific fields from a related entity.</summary>

This attribute is repeatable, allowing you to show multiple fields from the same relation. 

### Parameters:
- `name`: (string) **Required**. The property name on the target entity.
- `label`: (string) Custom label for the column (Translatable).
- `position`: (int) Change the display order.
- `canFilter`: (bool) Enable/disable filtering for this virtual column.
- `canSort`: (bool) Enable/disable sorting for this virtual column.
- `options`: (array) Additional parameters (passed to template or metadata).

**Notes:**
- The main related entity is displayed by default; use `#[Permission(allow: false)]` on the property to hide it. 
- Permissions for associated fields are inherited from the target entity. To hide a specific associated field in a parent grid, use `#[Permission]` on the target entity's property with the parent's `gridId`.

```php
#[ORM\ManyToOne]
#[AssociatedField(name: 'username', label: 'Author Name', position: 10, canSort: true)]
#[AssociatedField(name: 'email', label: 'Author Email', position: 11, canFilter: false)]
#[Permission(allow: false)] // Hide the author ID/object itself
private ?User $author = null;
```
</details>

<details>
<summary><strong>ColumnHtmlClass</strong>: Add CSS classes to the table header and cell.</summary>

```php
#[ColumnHtmlClass(headerClass: "text-center", valueClass: "fw-bold")]
private ?string $status = null;
```
</details>

<details>
<summary><strong>FieldTemplate</strong>: Use a custom Twig template to render this field.</summary>

```php
#[FieldTemplate('admin/user/_avatar_cell.html.twig')]
private ?string $avatarPath = null;
```
</details>

<details>
<summary><strong>Filterable</strong>: Enables searching/filtering for this field.</summary>

Auto-detects logic from Doctrine, but can be overridden.

**Automatic Filter Configuration:** If you define `#[FormType]` and `#[FormOptions]` on the same property, the filter will automatically inherit these settings.

**Conditions:**
- `ExactCondition`: `column = :value` (IDs, Enums, Choices)
- `StartsWithCondition`: `LIKE 'val%'` (Strings)
- `ContainsCondition`: `LIKE '%val%'` (Text)
- `InCondition`: `column IN (...)` (Multi-select)
- `RangeCondition`: Between two values (Dates, Numbers)

You can also define your own **Custom Filter Conditions**. See the [**Customization Section**](./customization.md#custom-filter-conditions) for more details.

```php
// 1. Simple usage: Auto-guesses condition (e.g. StartsWith for strings)
#[Filterable]
private ?string $name = null;

// 2. Override default condition (e.g. use 'Contains' instead of 'StartsWith')
#[Filterable(condition: ContainsCondition::class)]
private ?string $description = null;

// 3. Choice inheritance: Inherits ChoiceType and options automatically
#[FormType(ChoiceType::class)]
#[FormOptions(['choices' => ['Active' => 'a', 'Pending' => 'p']])]
#[Filterable] 
private ?string $status = null;
```
</details>

<details>
<summary><strong>FormType / FormOptions</strong>: Customize the Symfony Form field used for this property.</summary>

```php
#[FormType(PasswordType::class)]
#[FormOptions(['help' => 'Minimum 8 characters'])]
private ?string $plainPassword = null;
```
</details>

<details>
<summary><strong>GridTruncate</strong>: Limits text length in the grid view.</summary>

```php
#[GridTruncate(100)]
private ?string $content = null;
```
</details>

<details>
<summary><strong>Label</strong>: Override the automatically generated field label (Translatable).</summary>

```php
#[Label("E-mail Address")]
private ?string $email = null;
```
</details>

<details>
<summary><strong>Position</strong>: Change the display order of fields (lower numbers appear first).</summary>

```php
#[Position(-10)] // Move to the very beginning
private ?int $id = null;
```
</details>

<details>
<summary><strong>Sortable</strong>: Enables column sorting.</summary>

If a direction is provided, this field becomes the *initial* sort for the grid.

```php
// Enable sorting for this column
#[Sortable]
private ?string $name = null;

// Set as the default initial sort (descending)
#[Sortable(direction: 'desc', priority: 1)]
private ?int $id = null;
```
</details>

<details>
<summary><strong>ValuePrefix / ValueSuffix</strong>: Add text before or after the value (Translatable).</summary>

```php
#[ValuePrefix("$ ")]
private ?float $price = null;

#[ValueSuffix(" kg")]
private ?float $weight = null;
```
</details>

---

[Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)
