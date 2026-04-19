# AutoGridBundle

AutoGrid automatically generates CRUD interfaces for Doctrine entities.

[**Demo**](https://github.com/f0ska/auto-grid-test-bundle) | [**Installation**](./docs/installation.md) | [**Configuration**](./docs/global-configuration.md) | [**Attributes**](./docs/attributes.md) | [**Optional Factory Arguments**](./docs/optional-factory-arguments.md) | [**Templates**](./docs/templates.md) | [**Customization**](./docs/customization.md)

---

## Core Features

*   **CRUD Generation**: Full List, Create, Edit, and Delete functionality.
*   **Metadata-driven**: Automatically detects field types from Doctrine metadata.
*   **Built-in Filtering/Sorting**: Integrated search based on database schema.
*   **PHP 8 Attributes**: Configure UI behavior directly in Entity classes.
*   **Multi-theme Support**: Includes Bootstrap 5, Bootstrap 4, Bulma, Flowbite, and Foundation.

---

## Quick Start

1.  **In your Controller**
    Inject `AutoGridFactory` to create a grid:

    ```php
    public function list(AutoGridFactory $factory): Response
    {
        $grid = $factory->create(User::class);
        
        return $grid->getResponse() ?? $this->render('admin/user.html.twig', [
            'grid' => $grid
        ]);
    }
    ```

2.  **In Twig**
    Render the grid:

    ```twig
    {{ ag_render(grid) }}
    ```

---

## Requirements
*   **PHP 8.1+**
*   **Symfony 6.4+**
*   **Frameworks**: Default templates use Bootstrap 5. See [templates](./docs/templates.md) for custom framework configuration.
