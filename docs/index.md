Basic Usage
-----------
Controller example:

```php
use F0ska\AutoGridBundle\Factory\AutoGridFactory;

...

#[Route('/basic-example')]
public function myAction(AutoGridFactory $factory): Response
{
    $grid = $factory->create(MyEntity::class);
    return $grid->getResponse() ?? $this->render('my-template.html.twig', ['grid' => $grid]);
}
```
Twig template example:
```html
{{ agRender(grid) }}
```
This is all that is needed to create a fully functional grid.

**You can find more examples in the [demo bundle](https://github.com/f0ska/auto-grid-test-bundle).**

![Default Bootstrap5 AutoGrid look](./media/grid.png)

What AutoGrid does by default
-----------------------------
In addition to generating the grid itself, **AutoGrid** will:
- Generate a table name and table headers based on your entity name and properties.
- Create action buttons, pagination controls, and sort/filter buttons (if your fields have indexes in the database).
- Allow you to remove entities, add new ones, and edit existing ones.
- Support multiple grids on the same page with no additional configuration.


Check documentation for more possibilities
------------------------------------------
- [Optional Factory Arguments](./optional-factory-arguments.md)
- [Attributes](./attributes.md)
- [Global Configuration](./global-configuration.md)
- [Customization](./customization.md)
