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
 * Session namespace class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class SessionNamespace extends AbstractSession
{

    /**
     * Session namespace
     * @var ?string
     */
    private ?string $namespace = null;

    /**
     * Constructor
     *
     * Private method to instantiate the session object
     *
     * @param  string $namespace
     * @throws Exception
     */
    public function __construct(string $namespace)
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
    public function setNamespace(string $namespace): SessionNamespace
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get current namespace
     *
     * @return string
     */
    public function getNamespace(): string
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
    public function setTimedValue(string $key, mixed $value, int $expire = 300): SessionNamespace
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
    public function setRequestValue(string $key, mixed $value, int $hops = 1): SessionNamespace
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
    private function init(): void
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
     * @param  bool $all
     * @return void
     */
    public function kill(bool $all = false): void
    {
        if ($all) {
            $sess = Session::getInstance();
            $sess->kill();
        } else if (isset($_SESSION[$this->namespace])) {
            if (isset($_SESSION['_POP_SESSION_'][$this->namespace])) {
                unset($_SESSION['_POP_SESSION_'][$this->namespace]);
            }
            if (isset($_SESSION[$this->namespace])) {
                unset($_SESSION[$this->namespace]);
            }
        }
    }

    /**
     * Check the request-based session value
     *
     * @return void
     */
    private function checkRequest($key): void
    {
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

    /**
     * Check the request-based session values
     *
     * @return void
     */
    private function checkRequests(): void
    {
        foreach ($_SESSION[$this->namespace] as $key => $value) {
            $this->checkRequest($key);
        }
    }

    /**
     * Check the time-based session value
     *
     * @return void
     */
    private function checkExpiration($key): void
    {
        if (isset($_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key]) &&
            (time() > $_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key])) {
            unset($_SESSION[$this->namespace][$key]);
            unset($_SESSION['_POP_SESSION_'][$this->namespace]['expirations'][$key]);
        }
    }

    /**
     * Check the time-based session values
     *
     * @return void
     */
    private function checkExpirations(): void
    {
        foreach ($_SESSION[$this->namespace] as $key => $value) {
            $this->checkExpiration($key);
        }
    }

    /**
     * Get the session values as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $session = $_SESSION;

        if (isset($session['_POP_SESSION_'])) {
            unset($session['_POP_SESSION_']);
        }

        return $session[$this->namespace] ?? [];
    }

    /**
     * Set a property in the session object that is linked to the $_SESSION global variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $_SESSION[$this->namespace][$name] = $value;
    }

    /**
     * Get method to return the value of the $_SESSION global variable
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return (isset($_SESSION[$this->namespace][$name])) ? $_SESSION[$this->namespace][$name] : null;
    }

    /**
     * Return the isset value of the $_SESSION global variable
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($_SESSION[$this->namespace][$name]);
    }

    /**
     * Unset the $_SESSION global variable
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $_SESSION[$this->namespace][$name] = null;
        unset($_SESSION[$this->namespace][$name]);
    }

}
