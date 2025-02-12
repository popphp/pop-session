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
 * Session class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.2
 */
class Session extends AbstractSession
{

    /**
     * Instance of the session
     * @var ?object
     */
    private static ?object $instance = null;

    /**
     * Session Name
     * @var ?string
     */
    private ?string $sessionName = null;

    /**
     * Session ID
     * @var ?string
     */
    private ?string $sessionId = null;

    /**
     * Constructor
     *
     * @param array $options
     *
     * Private method to instantiate the session object
     */
    private function __construct(array $options = [])
    {
        // Start a session and set the session id.
        if (session_id() == '') {
            if (!empty($options)) {
                $sessionParams = session_get_cookie_params();
                $lifetime      = $options['lifetime'] ?? $sessionParams['lifetime'];
                $path          = $options['path']     ?? $sessionParams['lifetime'];
                $domain        = $options['domain']   ?? $sessionParams['domain'];
                $secure        = $options['secure']   ??  $sessionParams['secure'];
                $httponly      = $options['httponly'] ??  $sessionParams['httponly'];
                $sameSite      = $options['samesite'] ??  $sessionParams['samesite'];

                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path'     => $path,
                    'domain'   => $domain,
                    'secure'   => $secure,
                    'httponly' => $httponly,
                    'samesite' => $sameSite
                ]);
            }
            session_start();
            $this->sessionId   = session_id();
            $this->sessionName = session_name();
            $this->init();
        }
    }

    /**
     * Determine whether or not an instance of the session object exists already,
     * and instantiate the object if it does not exist.
     *
     * @param  array $options
     * @return Session
     */
    public static function getInstance(array $options = []): Session
    {
        if (null === self::$instance) {
            self::$instance = new Session($options);
        }

        self::$instance->checkRequests();
        self::$instance->checkExpirations();

        return self::$instance;
    }

    /**
     * Return the current the session name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->sessionName;
    }

    /**
     * Return the current the session id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->sessionId;
    }

    /**
     * Regenerate the session id
     *
     * @param  bool $deleteOldSession
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
        $this->sessionId   = session_id();
        $this->sessionName = session_name();
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
                'requests'    => [],
                'expirations' => []
            ];
        } else if (!isset($_SESSION['_POP_SESSION_']['requests'])) {
            $_SESSION['_POP_SESSION_']['requests']    = [];
            $_SESSION['_POP_SESSION_']['expirations'] = [];
        } else {
            $this->checkRequests();
            $this->checkExpirations();
        }
    }

    /**
     * Destroy the session
     *
     * @return void
     */
    public function kill(): void
    {
        if (!empty($this->sessionName) && !empty($this->sessionId) &&
            isset($_COOKIE[$this->sessionName]) && ($_COOKIE[$this->sessionName] == $this->sessionId)) {
            setcookie($this->sessionName, $this->sessionId, time() - 3600);
        }

        $_SESSION = null;
        session_unset();
        session_destroy();
        self::$instance    = null;
        $this->sessionId   = null;
        $this->sessionName = null;
    }

    /**
     * Set a time-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $expire
     * @return Session
     */
    public function setTimedValue(string $key, mixed $value, int $expire = 300): Session
    {
        $_SESSION[$key] = $value;
        $_SESSION['_POP_SESSION_']['expirations'][$key] = time() + (int)$expire;
        return $this;
    }

    /**
     * Set a request-based value
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $hops
     * @return Session
     */
    public function setRequestValue(string $key, mixed $value, int $hops = 1): Session
    {
        $_SESSION[$key] = $value;
        $_SESSION['_POP_SESSION_']['requests'][$key] = [
            'current' => 0,
            'limit'   => (int)$hops
        ];
        return $this;
    }

    /**
     * Check the request-based session value
     *
     * @return void
     */
    private function checkRequest($key): void
    {
        if (isset($_SESSION['_POP_SESSION_']['requests'][$key])) {
            $_SESSION['_POP_SESSION_']['requests'][$key]['current']++;
            $current = $_SESSION['_POP_SESSION_']['requests'][$key]['current'];
            $limit   = $_SESSION['_POP_SESSION_']['requests'][$key]['limit'];
            if ($current > $limit) {
                unset($_SESSION[$key]);
                unset($_SESSION['_POP_SESSION_']['requests'][$key]);
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
        foreach ($_SESSION as $key => $value) {
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
        if (isset($_SESSION['_POP_SESSION_']['expirations'][$key]) &&
            (time() > $_SESSION['_POP_SESSION_']['expirations'][$key])) {
            unset($_SESSION[$key]);
            unset($_SESSION['_POP_SESSION_']['expirations'][$key]);
        }
    }

    /**
     * Check the time-based session values
     *
     * @return void
     */
    private function checkExpirations(): void
    {
        foreach ($_SESSION as $key => $value) {
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

        return $session;
    }

    /**
     * Set a property in the session object that is linked to the $_SESSION global variable
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Exception
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if ($name == '_POP_SESSION_') {
            throw new Exception("Error: Cannot use the reserved name '_POP_SESSION_'.");
        }
        $_SESSION[$name] = $value;
    }

    /**
     * Get method to return the value of the $_SESSION global variable
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return (($name !== '_POP_SESSION_') && isset($_SESSION[$name])) ? $_SESSION[$name] : null;
    }

    /**
     * Return the isset value of the $_SESSION global variable
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return (($name !== '_POP_SESSION_') && isset($_SESSION[$name]));
    }

    /**
     * Unset the $_SESSION global variable
     *
     * @param  string $name
     * @throws Exception
     * @return void
     */
    public function __unset(string $name): void
    {
        if ($name == '_POP_SESSION_') {
            throw new Exception("Error: Cannot use the reserved name '_POP_SESSION_'.");
        }

        $_SESSION[$name] = null;
        unset($_SESSION[$name]);
    }

}
