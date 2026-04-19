[Home](../README.md) | **Installation** | [Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Optional Factory Arguments](./optional-factory-arguments.md) | [Templates](./templates.md) | [Customization](./customization.md)

# Installation

Install the bundle using Composer:

```bash
composer require f0ska/auto-grid-bundle
```

## Basic Setup

### 1. Bundle Registration
If you are using Symfony Flex, the bundle will be registered automatically.
If not, add it to your `config/bundles.php`:

```php
return [
    // ...
    F0ska\AutoGridBundle\F0skaAutoGridBundle::class => ['all' => true],
];
```

### 2. Assets (Bootstrap 5)
AutoGrid uses Bootstrap 5 and Bootstrap Icons for its default UI. 
If your project doesn't already include them, you can add them via CDN or Webpack Encore.

```html
<!-- In your base.html.twig -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
```

### 3. Usage
You're ready! Start creating grids in your controllers.

```php
public function list(AutoGridFactory $factory): Response
{
    $grid = $factory->create(User::class);
    return $grid->getResponse() ?? $this->render('admin/user.html.twig', ['grid' => $grid]);
}
```

---

[Configuration](./global-configuration.md) | [Attributes](./attributes.md) | [Customization](./customization.md)
