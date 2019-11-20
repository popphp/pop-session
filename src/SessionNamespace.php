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
 * Session namespace class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
class SessionNamespace extends AbstractSession
{

    /**
     * Session namespace
     * @var string
     */
    private $namespace = null;

    /**
     * Constructor
     *
     * Private method to instantiate the session object
     *
     * @param  string $namespace
     * @throws Exception
     */
    public function __construct($namespace)
    {
        if ($namespace == '_POP_SESSION_') {
            throw new Exception("Error: Cannot use the reserved namespace '_POP_SESSION_'.");
        }
        $this->setNamespace($namespace);
        $sess = Session::getInstance();
        if (!isset($sess[$namespace])) {
            $sess[$namespace] = [];
        }
        $this->init();
    }

    /**
     * Set current namespace
     *
     * @param  string $namespace
     * @return SessionNamespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get current namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set a time-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire
     * @return SessionNamespace
     */
    public function setTimedValue($key, $value, $expire = 300)
    {
        $_SESSION[$this->namespace][$key] = $value;
        $_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key] = time() + (int)$expire;
        return $this;
    }

    /**
     * Set a request-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $hops
     * @return SessionNamespace
     */
    public function setRequestValue($key, $value, $hops = 1)
    {
        $_SESSION[$this->namespace][$key] = $value;
        $_SESSION['_POP_SESSION_'][$this->namespace]['requests'][$key] = [
            'current' => 0,
            'limit'   => (int)$hops
        ];
        return $this;
    }

    /**
     * Init the session
     *
     * @return void
     */
    private function init()
    {
        if (!isset($_SESSION['_POP_SESSION_'])) {
            $_SESSION['_POP_SESSION_'] = [
                $this->namespace => [
                    'requests'    => [],
                    'expirations' => []
                ]
            ];
        } else if (isset($_SESSION['_POP_SESSION_']) && !isset($_SESSION['_POP_SESSION_'][$this->namespace])) {
            $_SESSION['_POP_SESSION_'][$this->namespace] = [
                'requests'    => [],
                'expirations' => []
            ];
        } else {
            $this->checkRequests();
            $this->checkExpirations();
        }
    }

    /**
     * Kill the session namespace
     *
     * @return void
     */
    public function kill()
    {
        if (isset($_SESSION[$this->namespace])) {
            unset($_SESSION[$this->namespace]);
        }
    }

    /**
     * Check the request-based session values
     *
     * @return void
     */
    private function checkRequests()
    {
        foreach ($_SESSION[$this->namespace] as $key => $value) {
            if (isset($_SESSION['_POP_SESSION_'][$this->namespace]['requests'][$key])) {
                $_SESSION['_POP_SESSION_'][$this->namespace]['requests'][$key]['current']++;
                $current = $_SESSION['_POP_SESSION_'][$this->namespace]['requests'][$key]['current'];
                $limit   = $_SESSION['_POP_SESSION_'][$this->namespace]['requests'][$key]['limit'];
                if ($current > $limit) {
                    unset($_SESSION[$this->namespace][$key]);
                    unset($_SESSION['_POP_SESSION_'][$this->namespace]['requests'][$key]);
                }
            }
        }
    }

    /**
     * Check the time-based session values
     *
     * @return void
     */
    private function checkExpirations()
    {
        foreach ($_SESSION[$this->namespace] as $key => $value) {
            if (isset($_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key]) &&
                (time() > $_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key])) {
                unset($_SESSION[$this->namespace][$key]);
                unset($_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key]);
            }
        }
    }

    /**
     * Get the session values as an array
     *
     * @return array
     */
    public function toArray()
    {
        return (isset($_SESSION[$this->namespace])) ? $_SESSION[$this->namespace] : null;
    }

    /**
     * Set a property in the session object that is linked to the $_SESSION global variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $_SESSION[$this->namespace][$name] = $value;
    }

    /**
     * Get method to return the value of the $_SESSION global variable
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($_SESSION[$this->namespace][$name])) ? $_SESSION[$this->namespace][$name] : null;
    }

    /**
     * Return the isset value of the $_SESSION global variable
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($_SESSION[$this->namespace][$name]);
    }

    /**
     * Unset the $_SESSION global variable
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $_SESSION[$this->namespace][$name] = null;
        unset($_SESSION[$this->namespace][$name]);
    }

}
