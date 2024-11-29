<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Session;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Abstract session class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.1
 */
abstract class AbstractSession implements SessionInterface, ArrayAccess, Countable, IteratorAggregate
{

    /**
     * Destroy the session
     *
     * @return void
     */
    abstract public function kill(): void;

    /**
     * Set a time-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire
     * @return AbstractSession
     */
    abstract public function setTimedValue(string $key, mixed $value, int $expire = 300): AbstractSession;

    /**
     * Set a request-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $hops
     * @return AbstractSession
     */
    abstract public function setRequestValue(string $key, mixed $value, int $hops = 1): AbstractSession;

    /**
     * Method to get the count of data in the session
     *
     * @return int
     */
    public function count(): int
    {

        return count($this->toArray());
    }

    /**
     * Method to iterate over the session
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Get the session values as an array
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Magic get method to return the value of values[$name].
     *
     * @param  string $name
     * @return mixed
     */
    abstract public function __get(string $name): mixed;

    /**
     * Magic set method to set values[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    abstract public function __set(string $name, mixed $value): void;

    /**
     * Return the isset value of values[$name].
     *
     * @param  string $name
     * @return bool
     */
    abstract public function __isset(string $name): bool;

    /**
     * Unset values[$name].
     *
     * @param  string $name
     * @return void
     */
    abstract public function __unset(string $name): void;

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

}
