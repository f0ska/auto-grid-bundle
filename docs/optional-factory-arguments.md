[Home](../README.md) | [Installation](./installation.md) | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | **Optional Factory Arguments** | [Templates](./templates.md) | [Customization](./customization.md)

# Optional Factory Arguments

These optional arguments can be passed to `AutoGridFactory::create()` to modify the grid's behavior.

<details>
<summary><strong>gridId</strong>: Changes the internal ID of the grid.</summary>

The value must be unique. It replaces the automatically generated hash in the URL.

```php
$autoGrid = $autoGridFactory->create(User::class, gridId: 'user-management-grid');
```
</details>

<details>
<summary><strong>queryExpression</strong> & <strong>queryParameters</strong>: Add custom DQL conditions to the grid query.</summary>

Use these to filter the data source (e.g., show only items belonging to the current user). The entity alias is always the entity name in camelCase (e.g., `user`).

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

$autoGrid = $autoGridFactory->create(
    User::class, 
    queryExpression: 'user.owner = :current_user',
    queryParameters: new ArrayCollection([
        new Parameter('current_user', $this->getUser()),
    ])
);
```
</details>

<details>
<summary><strong>initialAction</strong> & <strong>initialParameters</strong>: Change the starting state of the grid.</summary>

By default, the grid loads the `grid` action. You can force it to start in `create`, `edit` (requires `id`), or with predefined filters/sorting.

```php
// Start directly on the create form
$autoGrid = $autoGridFactory->create(User::class, initialAction: 'create');

// Start with a specific filter applied
$autoGrid = $autoGridFactory->create(
    User::class, 
    initialParameters: [
        'filter' => ['status' => 'active'],
        'order' => ['createdAt' => 'desc'],
    ]
);
```
</details>

<details>
<summary><strong>routePrefix</strong> & <strong>routeParameters</strong>: Configure routing for custom controllers.</summary>

Used alongside `ActionRoute` attributes. `routePrefix` is prepended to the route name, and `routeParameters` are passed to the route generator.

```php
$autoGrid = $autoGridFactory->create(
    User::class, 
    routePrefix: 'admin_',
    routeParameters: ['section' => 'users']
);
```
</details>

<details>
<summary><strong>customization</strong>: Pass arbitrary data to your custom extensions.</summary>

This parameter is not used by AutoGrid natively but is available in your [customizations](./customization.md), view services, and templates through the prepared grid context.

It is meant for extension code, not for core grid structure. If you need to change grid structure itself, prefer attributes or other earlier configuration points.

It does not decide which customization service runs. All registered customizations still execute.

```php
$autoGrid = $autoGridFactory->create(
    User::class, 
    customization: ['theme_color' => 'blue']
);
```
</details>

<details>
<summary><strong>context</strong>: Scope a grid to fixed field values.</summary>

Use this when a grid is rendered inside a parent context, for example articles that belong to a profile user.
AutoGrid applies simple equality conditions for these values, applies them to newly-created entities, and prevents users
from editing or filtering the contextualized fields.

Context is the scope of the grid. Use `queryExpression`/`queryParameters` only for additional conditions that narrow the
context further.

```php
use F0ska\AutoGridBundle\ValueObject\AutoGridMode;

$articles = $autoGridFactory->create(
    Article::class,
    context: ['author' => $user]
);
```
</details>

<details>
<summary><strong>mode</strong>: Render the grid as a full page component or embedded content.</summary>

Use `AutoGridMode::Embedded` when your controller template owns the surrounding page, for example tabs, page title,
back button, edit button, or external submit button.

Embedded mode hides AutoGrid-owned page chrome:

- instance title
- grid create button
- form and view header bars
- scroll-up buttons

It keeps grid behavior and row actions in place. Routing stays hybrid: `#[ActionRoute]` is used for actions that define
custom routes, and other actions keep the normal AutoGrid URL parameters.

```php
use F0ska\AutoGridBundle\ValueObject\AutoGridMode;

$autoGrid = $autoGridFactory->create(
    User::class,
    gridId: 'user-profile-edit',
    initialAction: 'edit',
    initialParameters: ['id' => $user->getId()],
    mode: AutoGridMode::Embedded
);
```

Entity forms use the stable id `form-{gridId}`, so an embedded shell can render its own submit button:

```twig
<button type="submit" form="form-user-profile-edit">Save</button>
```
</details>

---

[Attributes](./attributes.md) | [Templates](./templates.md) | [Customization](./customization.md)
