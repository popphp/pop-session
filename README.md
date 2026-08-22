pop-session
===========

[![Build Status](https://github.com/popphp/pop-session/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-session/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-session)](http://cc.popphp.org/pop-session/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Session ID and Regeneration](#session-id-and-regeneration)
* [Counting and Iterating](#counting-and-iterating)
* [Time-Based](#time-based)
* [Request-Based](#request-based)
* [Namespaces](#namespaces)
* [Advanced Usage](#advanced-usage)

Overview
--------
`pop-session` is a component used to manage sessions and session data in the PHP web environment.
It includes the ability to also manage namespaces within the session as well as timed-based and
request-based expirations.

`pop-session` is a component of the [Pop PHP Framework](https://www.popphp.org/).

[Top](#pop-session)

Install
-------

Install `pop-session` using Composer.

    composer require popphp/pop-session

Or, require it in your composer.json file

    "require": {
        "popphp/pop-session" : "^5.0.0"
    }

[Top](#pop-session)

Quickstart
----------

You can create a session and store and fetch data from it:

```php
use Pop\Session\Session;

$sess = Session::getInstance();

// Set session values
$sess->foo   = 'bar';
$sess['baz'] = 123;

// Access session values
echo $sess['foo'];
echo $sess->baz;
```

You can unset session data like this:

```php
unset($sess->foo);
unset($sess['baz']);
```

And finally, you can destroy the whole session like this:

```php
$sess->kill();
```

[Top](#pop-session)

Session ID and Regeneration
----------------------------

You can get the current session's ID and name, and regenerate the ID (for example, after a
user logs in, to guard against session fixation):

```php
use Pop\Session\Session;

$sess = Session::getInstance();

echo $sess->getId();
echo $sess->getName();

$sess->regenerateId(); // pass false to keep the old session's data instead of deleting it
```

[Top](#pop-session)

Counting and Iterating
-----------------------

`Session` and `SessionNamespace` are both `Countable` and `IteratorAggregate`, so you can
count and loop over session data directly, or get it as a plain array with `toArray()`:

```php
use Pop\Session\Session;

$sess = Session::getInstance();

echo count($sess);

foreach ($sess as $key => $value) {
    echo $key . ': ' . $value;
}

$data = $sess->toArray();
```

[Top](#pop-session)

Time-Based
----------

Session values can be made available based on time expiration:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->setTimedValue('foo', 'bar', 10); // # of seconds
```

Then, the next request will be successful if it's within the time
limit of that session data: 

```php
use Pop\Session\Session;

if (isset($sess->foo)) {
    echo $sess->foo;
} else {
    echo 'Nope!';
}
```

[Top](#pop-session)

Request-Based
-------------

Session values can be made available based on number of requests:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->setRequestValue('foo', 'bar', 1); // # of requests
```

Then, the next request will be successful if it's within the set limit
of number requests allowed before that session data is expired:

```php
if (isset($sess->foo)) {
    echo $sess->foo;
} else {
    echo 'Nope!';
}
```

[Top](#pop-session)

Namespaces
----------

You can store session data under a namespace to separate that data from the global
session data:

```php
use Pop\Session\SessionNamespace;

$sessMyApp = new SessionNamespace('MyApp');
$sessMyApp->foo = 'bar';

if (isset($sessMyApp->foo)) {
    echo $sessMyApp->foo;  // Only available under the namespace.
} else {
    echo 'Nope!';
}
```

Session namespaces can also store time-based and request-based session data:

```php
use Pop\Session\SessionNamespace;

$sessMyApp = new SessionNamespace('MyApp');
$sessMyApp->setTimedValue('foo', 'bar', 10); // # of seconds
$sessMyApp->setRequestValue('foo', 'bar', 1); // # of requests
```

You can destroy just the namespace's data, or pass `true` to destroy the entire underlying
session, not just this namespace:

```php
use Pop\Session\SessionNamespace;

$sessMyApp = new SessionNamespace('MyApp');

$sessMyApp->kill();       // only removes the 'MyApp' namespace's data
$sessMyApp->kill(true);   // destroys the whole session, same as Session::getInstance()->kill()
```

[Top](#pop-session)

Advanced Usage
--------------

`Session::getInstance()` accepts an optional array of options. These are only applied the
*first* time it's called in a given request - later calls in that same request ignore
`$options`, unless preceded by `kill()`:

* `lifetime` - cookie lifetime in seconds (default: your `php.ini` value)
* `path` - cookie path (default: your `php.ini` value)
* `domain` - cookie domain (default: your `php.ini` value)
* `secure` - cookie secure flag (default: your `php.ini` value)
* `httponly` - cookie HttpOnly flag (default: `true`)
* `samesite` - cookie SameSite attribute (default: your `php.ini` value, or `'Lax'` if unset)
* `strict_mode` - PHP's `session.use_strict_mode` fixation defense (default: `true`)

```php
use Pop\Session\Session;

$sess = Session::getInstance([
    'lifetime'    => 3600,
    'path'        => '/',
    'domain'      => 'example.com',
    'secure'      => true,
    'httponly'    => false,
    'samesite'    => 'None',
    'strict_mode' => false,
]);
```

You can manually trigger a check of time-based and request-based session values (removing
any that have expired or exceeded their hop limit) at any point by calling `sweep()`. This is
also available on `SessionNamespace`:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->sweep();
```

If you need to release the session's write lock early, without ending the session, call
`close()`:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->close();
```

You can plug in a custom `\SessionHandlerInterface` (for example, to store sessions in Redis
or a database) with `setHandler()`. It must be called before the first `Session::getInstance()`
call:

```php
use Pop\Session\Session;

Session::setHandler(new MyCustomSessionHandler());
$sess = Session::getInstance();
```

`Session` and `SessionNamespace` also implement `JsonSerializable`, so you can pass either
directly to `json_encode()`:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->foo = 'bar';

echo json_encode($sess); // {"foo":"bar"}
```

[Top](#pop-session)
