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

class PrototypicalObject implements ArrayAccess, Iterator {
    use PrototypicalTrait;

    /**
     * Instanciate a new prototypical object
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

        $reflect = new ReflectionClass(__CLASS__);
        $this->_['prototype']  = $reflect->newInstanceWithoutConstructor();

        if (is_array($prototype))
            foreach ($prototype as $name => $value)
                $this->$name = $value;
        elseif ($prototype)
            throw new InvalidArgumentException("Prototype\Object::__construct expects first parameter to be Prototype\Object or array, " . gettype($prototype) . " given");
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