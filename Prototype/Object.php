<?php
/**
 * This file is part of Prototype.php
 *
 * Copyright (c) 2013 Benjamin Delespierre
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Prototype;

use \ArrayAccess;
use \Iterator;
use \ReflectionClass;
use \InvalidArgumentException;
use \BadMethodCallException;
use \Closure;

/**
 * Prototyped objects class
 *
 * @package Prototype
 * @author Benjamin Delespierre
 */
class Object implements ArrayAccess, Iterator {

    protected $_ = array();

    /**
     * Instanciate a new prototyped object
     *
     * It takes as only parameter either an instance of Prototype\Object or an array.
     * In the first case the object will be used as a prototype for the new object,
     * in the second case the array will be treated as an object descriptor.
     *
     * @param Prototype\Object|array|null $prototype The prototype or object descriptor to use
     *
     * @throws InvalidArgumentException If the $prototype parameter type is not one of the above
     */
    public function __construct ($prototype = null) {
        if ($prototype instanceof static) {
            $this->_['prototype'] = $prototype;
            return;
        }

        $reflect = new ReflectionClass(get_class($this));
        $this->_['prototype']  = $reflect->newInstanceWithoutConstructor();

        if (is_array($prototype))
            foreach ($prototype as $name => $value)
                $this->$name = $value;
        elseif ($prototype)
            throw new InvalidArgumentException("Prototype\Object::__construct expects first parameter to be Prototype\Object or array, ".gettype($prototype)." given");
    }

    /**
     * Destructs a prototypable object
     *
     * Does nothing except call the 'destruct' dynamic method (if any)
     */
    public function __destruct () {
        try { $this->__call('destruct'); }
        catch (BadMethodCallException $e) { return; }
    }

    /**
     * Gets a dynamic member value
     *
     * If the member is not bound to current instance, will lookup trhough the prototype
     * chain until it finds a match and returns 'null' if none was found.
     *
     * @param string $name The dynamic member name
     *
     * @return mixed
     */
    public function __get ($name) {
        if (isset($this->_[$name])) return $this->_[$name];
        if (isset($this->_['prototype'])) return $this->_['prototype']->$name;
        return null;
    }

    /**
     * Sets a dynamic member
     *
     * If the value happens to be a callable, it will be wrapped into a
     * Prototype\FunctionObject in order to be usable by __call and will
     * then become a dynamic method.
     *
     * @param string $name  The dynamic member name
     * @param mixed  $value The value
     *
     * @return void
     */
    public function __set ($name, $value) {
        $this->_[$name] = $value instanceof Closure ? new FunctionObject($value) : $value;
    }

    /**
     * Tells if the dynamic member exists
     *
     * @param string $name The dynamic member name
     *
     * @return boolean
     */
    public function __isset ($name) {
        return isset($this->_[$name]);
    }

    /**
     * Removes a dynamic member
     *
     * @param string $name The dynamic member name
     *
     * @return void
     */
    public function __unset ($name) {
        unset($this->_[$name]);
    }

    /**
     * Call a dynamic method
     *
     * @param string $name   The dynamic member name
     * @param array  $args   The parameters for method
     *
     * @return mixed
     *
     * @throws BadMethodCallException If $name is not found or not a method
     */
    public function __call ($name, $args = array()) {
        if (!($fn = $this->__get($name)) || !$fn instanceof FunctionObject)
            throw new BadMethodCallException("Object has no method '$name'");
        return $fn->apply($this, $args);
    }

    /**
     * Clones a prototyped object
     *
     * Does nothing except call the 'clone' dynamic method (if any)
     *
     * @return void
     */
    public function __clone () {
        try { $this->__call('clone'); }
        catch (BadMethodCallException $e) { return; }
    }

    /**
     * Converts a prototyped object to string
     *
     * Does nothing except call the 'toString' dynamic method (if any).
     * Returns 'Prototype\Object' if no 'toString' dynmaic method is registered
     *
     * @return string
     */
    public function __toString () {
        try { return $this->__call('toString'); }
        catch (BadMethodCallException $e) { return get_class($this); }
    }

    /**
     * Check if a dynamic member is set
     *
     * @param string $name The dynamic member name
     *
     * @return boolean
     */
    public function offsetExists ($name) {
        return $this->__get($name) !== null;
    }

    /**
     * Gets a dynamic member value
     *
     * @see Prototype\Object::__get
     *
     * @param string $name The dynamic member name
     *
     * @return mixed
     */
    public function offsetGet ($name) {
        return $this->__get($name);
    }

    /**
     * Sets a dynamic member value
     *
     * @see Prototype\Object::__set
     *
     * @param string $name The dynamic member name
     * @param mixed  $value  The value
     *
     * @return void
     */
    public function offsetSet ($name, $value) {
        $this->__set($name, $value);
    }

    /**
     * Removes a dynamic member
     *
     * @see Prototype\Object::__unset
     *
     * @param string $name The dynamic member name
     *
     * @return void
     */
    public function offsetUnset ($name) {
        $this->__unset($name);
    }

    /**
     * Gets the current item in dynamic object
     *
     * @return mixed
     */
    public function current () {
        return current($this->_);
    }

    /**
     * Gets the key for current item in dynamic object
     *
     * @return string
     */
    public function key () {
        return key($this->_);
    }

    /**
     * Moves to next item in dynamic object and return its value
     *
     * @return mixed
     */
    public function next () {
        return next($this->_);
    }

    /**
     * Returns to the first item in dynamic object and return its value
     *
     * @return mixed
     */
    public function rewind () {
        return reset($this->_);
    }

    /**
     * Tells if current item position in dynamic object is valid
     *
     * @return boolean
     */
    public function valid () {
        return key($this->_) !== null;
    }
}