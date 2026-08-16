<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
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
use JsonSerializable;

/**
 * Abstract session class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
abstract class AbstractSession implements SessionInterface, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
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
     * Get the session values for JSON serialization
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Check a single request-based value against its hop limit, removing it once the limit is exceeded
     *
     * @param  string $key
     * @param  array  $data
     * @param  array  $bookkeeping
     * @return void
     */
    protected function checkRequestValue(string $key, array &$data, array &$bookkeeping): void
    {
        if (isset($bookkeeping['requests'][$key])) {
            $bookkeeping['requests'][$key]['current']++;
            $current = $bookkeeping['requests'][$key]['current'];
            $limit   = $bookkeeping['requests'][$key]['limit'];
            if ($current > $limit) {
                unset($data[$key]);
                unset($bookkeeping['requests'][$key]);
            }
        }
    }

    /**
     * Check all request-based values against their hop limits
     *
     * @param  array $data
     * @param  array $bookkeeping
     * @return void
     */
    protected function checkRequestValues(array &$data, array &$bookkeeping): void
    {
        foreach (array_keys($bookkeeping['requests'] ?? []) as $key) {
            $this->checkRequestValue($key, $data, $bookkeeping);
        }
    }

    /**
     * Check a single time-based value against its expiration, removing it once expired
     *
     * @param  string $key
     * @param  array  $data
     * @param  array  $bookkeeping
     * @return void
     */
    protected function checkExpirationValue(string $key, array &$data, array &$bookkeeping): void
    {
        if (isset($bookkeeping['expirations'][$key]) && (time() > $bookkeeping['expirations'][$key])) {
            unset($data[$key]);
            unset($bookkeeping['expirations'][$key]);
        }
    }

    /**
     * Check all time-based values against their expirations
     *
     * @param  array $data
     * @param  array $bookkeeping
     * @return void
     */
    protected function checkExpirationValues(array &$data, array &$bookkeeping): void
    {
        foreach (array_keys($bookkeeping['expirations'] ?? []) as $key) {
            $this->checkExpirationValue($key, $data, $bookkeeping);
        }
    }

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
