<?php
require "Prototype/PrototypicalTrait.php";
require "Prototype/PrototypicalObject.php";
require "Prototype/FunctionObject.php";

use Prototype\PrototypicalObject as Object,
    Prototype\FunctionObject;

//
// Basics
//

echo "> Basics\n";

$obj_1 = new Object;
$obj_1->a = 1;
$obj_1->getA = function () { return $this->a; };
echo "Expected : 1\tObtained : " . $obj_1->getA() . "\n";

$obj_2 = new Object;
$obj_2->prototype->getB = function () { return $this->b; };
$obj_1->prototype = $obj_2->prototype;
$obj_1->b = 2;
echo "Expected : 2\tObtained : " . $obj_1->getB() . "\n";

$obj_3 = new Object;
$obj_3->b = 3;
echo "Expected : 3\tObtained : " . $obj_1->getB->call($obj_3) . "\n";

//
// Inheritance
//

echo "\n> Inheritance\n";

$obj_1 = new Object;
$obj_1->foo = function () { return "foo"; };
$obj_2 = new Object($obj_1);
echo "Expected : foo\tObtained : " . $obj_2->foo() . "\n"; // foo (inheritance)

$obj_1->foo = function () { return "bar"; };
echo "Expected : bar\tObtained : " . $obj_2->foo() . "\n"; // bar (prototype changed)

$obj_3 = new Object($obj_2);
echo "Expected : bar\tObtained : " . $obj_3->foo() . "\n"; // bar (transitivity)

$obj_4 = new Object;
$obj_4->foo = function () { return "pok"; };
$obj_3->prototype = clone $obj_4;
echo "Expected : pok\tObtained : " . $obj_3->foo() . "\n"; // pok (prototype replacement)

$obj_4->foo = $obj_1->foo;
echo "Expected : pok\tObtained : " . $obj_3->foo() . "\n"; // pok (prototype was cloned)

//
// Factory
//

echo "\n> Classes\n";

$class = new FunctionObject(function ($a, $b, $c) {
    $this->a = $a;
    $this->b = $b;
    $this->c = $c;
    return clone $this;
});
$obj_1 = $class(1,2,3); // opa JS style!
echo "Expected : 123\tObtained : {$obj_1->a}{$obj_1->b}{$obj_1->c}\n";

$obj_2 = new Object;
$class->call($obj_2, 4,5,6);
echo "Expected : 456\tObtained : {$obj_2->a}{$obj_2->b}{$obj_2->c}\n";

//
// Object declaration
//

echo "\n> Object declaration\n";

$obj_1 = new Object([
    'a' => 1,
    'getA' => function () {
        return $this->a;
    }
]);
echo "Expected : 1\tObtained : " . $obj_1->getA() . "\n";

$obj_2 = new Object([
    'b' => new Object([
        'a' => 1,
        'getA' => function () {
            return $this->a;
        }
    ])
]);
echo "Expected : 1\tObtained : " . $obj_2->b->getA() . "\n";

//
// Methods overriding
//

echo "\n> Methods overriding\n";

$obj_1 = new Object;
$obj_1->str = "baz";
$obj_1->toString = function () {
    return $this->str;
};
echo "Expected : baz\tObtained : $obj_1\n";

$obj_1->clone = function () use (& $is_cloned) {
    $is_cloned = 1;
};
$obj_2 = clone $obj_1;
echo "Expected : 1\tObtained : $is_cloned\n";

$obj_1->destruct = function () use (& $is_deleted) {
    $is_deleted = 1;
};
unset($obj_1);
echo "Expected : 1\tObtained : $is_deleted\n";