# Factory Method Design Pattern

```
         +--------------------------+                    +-----------------------+
         |        <interface>       |                    |      <interface>      | 
         |       FactoryMethod      |                    |         Model         |
         +--------------------------+                    +-----------------------+
         | +create(param) : Model   |                    | +someMethod()         | 
         +--------------------------+                    +-----------------------+         
                      ^                                               ^
                      |                                               |
         +--------------------------+                    +-----------------------+
         |          <class>         |                    |        <class>        | 
         |      ConcreteFactory     |                    |     ConcreteModel     |
         +--------------------------+                    +-----------------------+
         | +create(param) : Model   |                    | +__construct(param)   | 
         +--------------------------+                    | +someMethod()         |
                                                         +-----------------------+
```

## The key feature is : 

```
ConcreteFactory::create($param) : Model {
    return new ConcreteModel($param);
}
```

## Methodology
You provide a concrete model class. 
This command generate those 4 files above.
I recommand to replace your concrete class with the interface Model.

Feel free to remove unecessary methods & constants in Model interface.