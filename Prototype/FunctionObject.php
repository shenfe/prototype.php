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

use \Closure;

/**
 * Prototyped functions class
 *
 * @package Prototype
 * @author Benjamin Delespierre
 */
class FunctionObject extends Object {

    /**
     * Instanciate an new prototyped function
     *
     * @param Closure $closure The native closure
     */
    public function __construct (Closure $closure) {
        parent::__construct();
        $this->_['closure'] = Closure::bind($closure, $this);
    }

    /**
     * Launches the prototyped function in its own scope
     *
     * This method may take as many parameters as the native closure
     * accepts. No integrity check will be made.
     *
     * @return mixed
     */
    public function __invoke () {
        return call_user_func_array($this->_['closure'], func_get_args());
    }

    /**
     * Lets the prototyped function to be used in the context of any prototyped object
     *
     * This method may take as many parameters after $object as the native
     * closuse accepts. No integrity check will be made.
     *
     * @param Prototype\Object $object The scope
     *
     * @return mixed
     */
    public function call (Object $object) {
        return $this->apply($object, array_slice(func_get_args(), 1));
    }

    /**
     * Lets the prototyped function to be used in the context of any prototyped object
     *
     * This method takes as second parameter an array consisting of parameters
     * for the native closure. No integrity check will be made.
     *
     * @param Prototype\Object $object The scope
     * @param array            $args   The function arguments
     *
     * @return mixed
     */
    public function apply (Object $object, array $args) {
        $closure = Closure::bind($this->_['closure'], $object);
        return call_user_func_array($closure, $args);
    }
}