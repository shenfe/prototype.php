# prototype.php

**Prototypal inheritance in PHP**

## Tired of classical inheritance ?

prototype.php is a proof-of-concept that demonstrate that the prototypal inheritance, which is the strength of many modern languages like JavaScript, is also applicable to PHP.

Simply download it, require Object & FunctionObject and _voilà_. Theses classes are namespaced so you may want to use `use` statement to ease your developments:

```PHP
<?php
require_once "Prototype/PrototypicalTrait.php";
require_once "Prototype/PrototypicalObject.php";
require_once "Prototype/FunctionObject.php";

use Prototype\PrototypicalObject as Object,
    Prototype\FunctionObject;

// really cool code here
```

## Create an object

Very simple, instanciate Prototype\Object class and treat it like a JavaScript object.

```PHP
$object = new Object;

$object->aProperty = 'hello';

$object->aMethod = function () {
    return $this->aProperty . 'world!';
};

echo $object->aMethod(); // 'helloworld!'

$another_object = new Object;

$another_object->aProperty = 'strange ';

// aply is also available
echo $object->aMethod->call($another_object); // 'strange world!'
```

Note that `call` and `apply` are available for `aMethod` since we added it to `$object` because it was wrapped into a `Prototype\FunctionObject`. We could get the same result using directly an instance of `Prototype\FunctionObject`.

```PHP
$function = new FunctionObject(function () {
    return $this->aProperty . 'world!';
});

// another syntax to create objects
$object = new Object([
    'aProperty' => 'hello',
]);

echo $function->call($object); // 'helloworld!'
```

As you may have noticed, we use PHP 5.4 Closures.

## Inherit objects

The first parameter of `Prototype\Object::__construct` can be either a structure (array) or a prototype for the new object (instance of `Prototype\Object`). In the later, the new object will then inherit all the members from his parent unless it redefines them.

```PHP
$parent = new Object;
$parent->a = 1;
$parent->getA = function () {
    return $this->a;
};

$child = new Object($parent);
echo $child->getA(); // '1'

$child->a = 2;
echo $child->getA(); // '2'
echo $parent->getA(); // '1'
```

Obviously, you can use transitivity.

```PHP
$object_A = new Object;
$object_B = new Object($object_A);
$object_C = new Object($object_B);

$object_A->a = 1;
$object_B->b = 2;
$object_C->c = 3;

echo $object_C->a, $object_C->b, $object_C->c; // '1 2 3'
```

If you want to prevent a value change in the parent to occur in the children, simply clone them !

```PHP
$object_A = new Object;
$object_A->method = function () {
    return 'foo';
};

$object_B = new Object;
$object_B->prototype = clone $object_A; // another way to define the prototype

echo $object_B->method(); // 'foo'

$object_A->foo = function () {
    return 'bar';
};

echo $object->foo(); // still 'foo'
```

## More cool stuff

Prototyped objects are also traversables structures...

```PHP
$object = new Object([1,2,3]);

foreach ($object as $value)
    echo $value; // '1 2 3'
```

... as well as arrays.

```PHP
$object = new Object;

$object['a'] = 1;
$object['getA'] = function () {
    return $this['a'];
};

echo $object->getA();
```

You can even turn a function into a factory.

```PHP
$function = new FunctionObject(function ($a, $b, $c) {
    $this->a = $a;
    $this->b = $b;
    $this->c = $c;
    // yep, you can override toString
    $this->toString = function () {
        return "{$this->a} {$this->b} {$this->c}";
    };
    return clone $this;
});

$object = $function(1,2,3);
echo $object; // '1 2 3'
```

Now you can also use the `Prototype\PrototypicalTrait` in order to give more power to existing classes.

```PHP
class ObjectSorage extends SplObjectStorage {
    use Prototype\PrototypicalTrait ;
}

$warehouse = new ObjectStorage;

$warehouse->attachScalar = function ($scalar) {
    return $this->attach((object)compact('scalar'));
}

$warehouse->attachScalar(1);
$warehouse->attachScalar(2);
$warehouse->attachScalar(3);

foreach ($warehouse as $object)
    var_dump( $object );
```
