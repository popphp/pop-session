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
 * Session interface
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
interface SessionInterface
{

    /**
     * Destroy the session
     *
     * @return void
     */
    public function kill();

    /**
     * Set a time-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire
     * @return SessionInterface
     */
    public function setTimedValue($key, $value, $expire = 300);

    /**
     * Set a request-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $hops
     * @return SessionInterface
     */
    public function setRequestValue($key, $value, $hops = 1);

}