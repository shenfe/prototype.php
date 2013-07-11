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

use \Exception;
use \InvalidArgumentException;
use \BadMethodCallException;
use \Closure;

/**
 * Prototypical objects trait
 *
 * @package Prototype
 * @author Benjamin Delespierre
 */
trait PrototypicalTrait {

    /**
     * Obejct's members
     *
     * @internal
     * @var array
     */
    protected $_ = array();

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
     * Linearizing prototyped objects is not allowed
     *
     * @throws Exception
     */
    public function __sleep () {
        throw new Exception("Serialization of '" . __CLASS__ . "' is not allowed due to its Prototypical nature");
    }

    /**
     * Delinearizing prototyped objects is not allowed
     *
     * @throws Exception
     */
    public function __wakeup () {
        throw new Exception("Unserialize of '" . __CLASS__ . "' is not allowed due to its Prototypical nature");
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
        catch (BadMethodCallException $e) { return __CLASS__; }
    }
}