#prototype.php#

**Prototypal inheritance in PHP**

##Tired of classical inheritance ?##

prototype.php is a proof-of-concept that demonstrate that the prototypal inheritance, which is the strenght of many modern languages like JavaScript, is also applicable to PHP.

##How it works ?##

It strongly rely on PHP's magical method to allow any object to be used like a JavaScript object and on closures to define our objects' behavior. It uses the references between prototypes to create a chain and then use it to locate a member. Unfortunately, like in JavaScript, the cost to pay is to take encapsulation off the table for we won't be able to define members' visibility anymore.

You can have a look into the _Object_ class to get a grasp on how it's built, it's no dark magic (except for the _Method_ serialization maybe...)

##How to use it ?##

Everything starts with a blank object, then you add some members to its and prototype and you can start chaining. Because it's technically impossible to import the _$this_ keyword inside an anonymous function scope, we're just going to use a _$that_ parameter on every method we want to attach to our objects.

```PHP
<?php
require_once "prototype.php";

$obj = new Object;

$obj->a = 1;

$obj->getA = function ($that) { return $that->a; };

echo $obj->getA(); // will print 1

$obj2 = new Object;

$obj2->a = 2;

echo $obj->getA->call($obj2); // will print 2 (that's right, we got call & apply!)
```

We are able to add as many properties & members to our object as the PHP memory limit can handle and that's pretty neat because our object is now morphic.

What's so great about that you may ask. When it comes handy is when we turn this morphic capability into a way to define classes. But this time, contrary to classical oriented object programming, the class can change and its children will be affected by those changes.

```PHP
<?php
require_once "prototype.php";

$obj1 = new Object;

$obj1->prototype->foo = function () { echo "foo\n"; };

$obj2 = new Object($obj1->prototype);

$obj2->foo(); // foo

$obj1->prototype->foo = function () { echo "bar\n"; };

$obj2->foo(); // bar (inheritance!)

$obj3 = new Object($obj2->prototype);

$obj1->prototype->foo = function () { echo "baz\n"; };

$obj3->foo(); // baz (transitivity!)
```

The above example shows the property of inheritance & transitivity of our prototype. But our object is not class if you can't make objects with it, right ?

So we're going to add a factory method, ingeniously called _new_, to our object and Voilà, we have a weird-shaped class.

```PHP
<?php
require_once "prototype.php";

$class = new Object;

$class->new = function ($that) { return new Object($that->prototype); };

$instance = $class->new();

$class->prototype->sayHello = function () { echo "hello!\n"; };

$instance->sayHello(); // hello!
```

Now you can do pretty much everything with your objects & prototypes, the only limit is your imagination. Since we cannot reproduce exactly the JavaScript object oriented behavior (and, to be honnest, we're just doing a vague emulation here) it's pretty fun and flexible.

Oh, and did I mention you can override any object __clone, __toString, __destruct, __sleep & __wakeup (that's right, these objects are serializable!) methods using but closures ?


