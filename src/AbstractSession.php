<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Session;

/**
 * Abstract session class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
abstract class AbstractSession implements SessionInterface, \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Destroy the session
     *
     * @return void
     */
    abstract public function kill();

    /**
     * Set a time-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire
     * @return AbstractSession
     */
    abstract public function setTimedValue($key, $value, $expire = 300);

    /**
     * Set a request-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $hops
     * @return AbstractSession
     */
    abstract public function setRequestValue($key, $value, $hops = 1);

    /**
     * Method to get the count of data in the session
     *
     * @return int
     */
    public function count()
    {

        return count($this->toArray());
    }

    /**
     * Method to iterate over the session
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * Get the session values as an array
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Magic get method to return the value of values[$name].
     *
     * @param  string $name
     * @return mixed
     */
    abstract public function __get($name);

    /**
     * Magic set method to set values[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    abstract public function __set($name, $value);

    /**
     * Return the isset value of values[$name].
     *
     * @param  string $name
     * @return boolean
     */
    abstract public function __isset($name);

    /**
     * Unset values[$name].
     *
     * @param  string $name
     * @return void
     */
    abstract public function __unset($name);

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

}