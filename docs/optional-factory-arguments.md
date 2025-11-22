Optional Factory arguments
==========================
There are several optional arguments you can provide to `AutoGridFactory::create`
method to modify AutoGrid behavior.

### `gridId`

This parameter changes the internal ID of the grid.
The value should be unique and will visually alter the grid ID parameter in the URL
(by default, this is an automatically generated hash).
It is not required if the default behavior meets your needs.

```php
$autoGrid = $autoGridFactory->create(DemoOne::class, gridId: 'my-grid');
```

### `queryExpression` and `queryParameters`

These parameters allow you to add additional conditions to the grid query.
You must provide the same arguments as you would for Doctrine’s `QueryBuilder::andWhere()`
and `QueryBuilder::setParameters()`. The alias is always the name of your entity in camel case.

```php
$autoGrid = $autoGridFactory->create(
    DemoOne::class, 
    queryExpression: 'demoOne.id = :user_id1 OR demoOne.id = :user_id2',
    queryParameters: new ArrayCollection(
        [
            new Parameter('user_id1', 1),
            new Parameter('user_id2', 2),
        ]
    )
);
```

### `initialAction` and `initialParameters`

These arguments can be useful if you want to change the initial state of a grid.
By default, AutoGrid will use the action `grid` with no parameters.
You can provide any available action to load initially, and for some actions,
you will need to provide an `id` in the parameters.

```php
$autoGrid1 = $autoGridFactory->create(DemoOne::class, initialAction: 'create');

$autoGrid2 = $autoGridFactory->create(
    DemoTwo::class, 
    initialAction: 'edit',
    initialParameters: ['id' => 5]
);
```

You can also predefine initial filters or order, which can be changed or reset by the user.
If you need persistent filters, use the`queryExpression` argument.
For persistent order, refer to the [Attributes documentation](./attributes.md).

```php
$autoGrid = $autoGridFactory->create(
    DemoOne::class, 
    initialParameters: [
        'filter' => ['id' => '11'],
        'order' => ['name' => 'asc'],
    ]
);
```

### `routePrefix` and `routeParameters`

These arguments can be particularly beneficial if you decide to use custom controllers for specific actions.
They work in conjunction with `Route*` [attributes](./attributes.md) to specify how your routes should be configured.
The `routePrefix` parameter adds a static prefix to the route name defined in the `Route*` attribute.
The `routeParameters` parameter allows you to include additional parameters as needed for your route.
Note that only the `id` parameter is automatically provided by the `Route*` attribute,
representing a unique identifier for an entity.

```php
$autoGrid = $autoGridFactory->create(
    DemoOne::class, 
    routePrefix: 'my_custom_route_',
    routeParameters: ['userId' => $this->getUser()->getId()]
);
```

Check documentation for more possibilities
------------------------------------------

- [Attributes](./attributes.md)
- [Global Configuration](./global-configuration.md)
- [Customization](./customization.md)
