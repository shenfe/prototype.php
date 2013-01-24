<?php
require_once "prototype.php";

// Basics

$obj1 = new Object;

$obj1->a = 1;

$obj1->getA = function ($that) { return $that->a; };

echo $obj1->getA() . "\n";

$obj2 = new Object;

$obj2->prototype->getB = function ($that) { return $that->b; };

$obj1->b = 2;

$obj1->prototype = $obj2->prototype;

echo $obj1->getB() . "\n";

$obj3 = new Object;

$obj3->b = 3;

echo $obj1->getB->call($obj3) . "\n";

// Inheritance //

$obj1 = new Object;

$obj1->prototype->foo = function () { echo "foo\n"; };

$obj2 = new Object($obj1->prototype);

$obj2->foo(); // foo

$obj1->prototype->foo = function () { echo "bar\n"; };

$obj2->foo(); // bar (inheritance!)

$obj3 = new Object($obj2->prototype);

$obj1->prototype->foo = function () { echo "baz\n"; };

$obj3->foo(); // baz (transitivity!)

$obj4 = new Object;

$obj4->prototype->foo = function () { echo "pok\n"; };

$obj3->prototype = clone $obj4->prototype;

$obj3->foo(); // pok (prototype exchange!)

$obj4->prototype->foo = function () { echo "pom\n"; };

$obj3->foo(); // still pok (prototype was cloned!)

$obj5 = new Object;

$obj5->foo = function () { echo "sup\n"; };

$obj3->prototype = $obj5;

$obj3->foo(); // sup (any prototype is an object!)

// Factory //

$class = new Object;

$class->new = function ($that) { return new Object($that->prototype); };

$instance = $class->new();

$class->prototype->sayHello = function () { echo "hello!\n"; };

$instance->sayHello();

