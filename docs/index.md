**Index** | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)

# Quick Start Guide

AutoGrid is designed to be **plug-and-play**. Here’s how you get a full CRUD for any Doctrine entity.

### 1. In your Controller
Inject the `AutoGridFactory` and create a grid for your entity.

```php
use F0ska\AutoGridBundle\Factory\AutoGridFactory;
use App\Entity\YourEntity;

#[Route('/admin/your-entity')]
public function list(AutoGridFactory $factory): Response
{
    $grid = $factory->create(YourEntity::class);
    
    // Auto-handles form processing, redirects, and rendering logic
    return $grid->getResponse() ?? $this->render('admin/your_entity.html.twig', [
        'grid' => $grid
    ]);
}
```

### 2. In your Twig template
Render the grid with a single line.

```html
{% extends 'base.html.twig' %}

{% block body %}
    {{ agRender(grid) }}
{% endblock %}
```

### 3. That's it!
AutoGrid will automatically:
- **Guess** property types from Doctrine metadata.
- **Generate** table headers and data rows.
- **Create** action buttons (Add, Edit, Delete).
- **Setup** pagination, sorting, and filters.

---

## 🛠 Next Steps

- [**Attributes**](./attributes.md): Fine-tune your UI with PHP 8 Attributes.
- [**Installation**](./installation.md): Set up the bundle in your project.
- [**Customization**](./customization.md): Override templates or extend core logic.
- [**Configuration**](./global-configuration.md): Set global defaults for all your grids.
- [**Demo Bundle**](https://github.com/f0ska/auto-grid-test-bundle): See advanced examples in a real project.

---

## 🔍 Troubleshooting & FAQ

<details>
<summary><strong>Why is my field not showing up?</strong></summary>

AutoGrid only displays fields that are:
1.  **Mapped** in Doctrine metadata (Entity property or via `AssociatedField`).
2.  **Accessible** via public properties or standard getters (e.g., `getName()`, `isPublished()`).
</details>

<details>
<summary><strong>How do I hide a field?</strong></summary>

Use the `#[Permission(allow: false)]` attribute on the property.

```php
#[Attribute\Permission(allow: false)]
private ?string $internalCode = null;
```
</details>

<details>
<summary><strong>Can I have multiple grids on one page?</strong></summary>

Yes! AutoGrid handles multiple grids on the same page automatically. Unique internal IDs are generated to prevent URL parameter collisions. You can still provide your own `gridId` if you want more control over the URL appearance:

```php
$grid = $factory->create(User::class, gridId: 'my-custom-id');
```
</details>
