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

use SessionHandlerInterface;

/**
 * Session class
 *
 * @category   Pop
 * @package    Pop\Session
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Session extends AbstractSession
{

    /**
     * Instance of the session
     * @var ?object
     */
    private static ?object $instance = null;

    /**
     * Custom session save handler
     * @var ?SessionHandlerInterface
     */
    private static ?SessionHandlerInterface $handler = null;

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
        // Start a session if one isn't already active.
        if (session_id() == '') {
            $sessionParams = session_get_cookie_params();
            $strictMode    = $options['strict_mode'] ?? true;

            session_set_cookie_params([
                'lifetime' => $options['lifetime'] ?? $sessionParams['lifetime'],
                'path'     => $options['path']     ?? $sessionParams['path'],
                'domain'   => $options['domain']   ?? $sessionParams['domain'],
                'secure'   => $options['secure']   ?? $sessionParams['secure'],
                'httponly' => $options['httponly'] ?? true,
                'samesite' => $options['samesite'] ?? (ini_get('session.cookie_samesite') ?: 'Lax')
            ]);

            if (self::$handler !== null) {
                session_set_save_handler(self::$handler, true);
            }

            session_start(['use_strict_mode' => $strictMode ? '1' : '0']);
        }

        $this->sessionId   = session_id();
        $this->sessionName = session_name();
        $this->init();
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
        } else {
            self::$instance->sweep();
        }

        return self::$instance;
    }

    /**
     * Set a custom session save handler
     *
     * Must be called before the first Session::getInstance() call, and before any
     * other code has called session_start() — a save handler has no effect once a
     * session is already active.
     *
     * @param  SessionHandlerInterface $handler
     * @throws Exception
     * @return void
     */
    public static function setHandler(SessionHandlerInterface $handler): void
    {
        if ((self::$instance !== null) || (session_status() === PHP_SESSION_ACTIVE)) {
            throw new Exception("Error: Cannot set the session handler after the session has already started.");
        }
        self::$handler = $handler;
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
            $this->sweep();
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

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = null;
        self::$instance    = null;
        $this->sessionId   = null;
        $this->sessionName = null;
    }

    /**
     * Close the session for writing, releasing the session lock without ending the session
     *
     * @return void
     */
    public function close(): void
    {
        session_write_close();
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
     * Manually check request-based and time-based values, removing any that have
     * expired or exceeded their hop limit
     *
     * @return Session
     */
    public function sweep(): Session
    {
        if (isset($_SESSION['_POP_SESSION_'])) {
            $this->checkRequestValues($_SESSION, $_SESSION['_POP_SESSION_']);
            $this->checkExpirationValues($_SESSION, $_SESSION['_POP_SESSION_']);
        }
        return $this;
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
