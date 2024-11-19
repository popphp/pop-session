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

/**
 * Session interface
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
interface SessionInterface
{

    /**
     * Destroy the session
     *
     * @return void
     */
    public function kill(): void;

    /**
     * Set a time-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire
     * @return SessionInterface
     */
    public function setTimedValue(string $key, mixed $value, int $expire = 300): SessionInterface;

    /**
     * Set a request-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $hops
     * @return SessionInterface
     */
    public function setRequestValue(string $key, mixed $value, int $hops = 1): SessionInterface;

}
