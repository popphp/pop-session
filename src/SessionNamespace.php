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

/**
 * Session namespace class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
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
     * Manually check request-based and time-based values, removing any that have
     * expired or exceeded their hop limit
     *
     * @return SessionNamespace
     */
    public function sweep(): SessionNamespace
    {
        if (isset($_SESSION[$this->namespace], $_SESSION['_POP_SESSION_'][$this->namespace])) {
            $this->checkRequestValues($_SESSION[$this->namespace], $_SESSION['_POP_SESSION_'][$this->namespace]);
            $this->checkExpirationValues($_SESSION[$this->namespace], $_SESSION['_POP_SESSION_'][$this->namespace]);
        }
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
        } else if (!isset($_SESSION['_POP_SESSION_'][$this->namespace])) {
            $_SESSION['_POP_SESSION_'][$this->namespace] = [
                'requests'    => [],
                'expirations' => []
            ];
        } else {
            $this->sweep();
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
     * Get the session values as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $_SESSION[$this->namespace] ?? [];
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
