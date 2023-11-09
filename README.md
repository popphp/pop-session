pop-session
===========

[![Build Status](https://github.com/popphp/pop-session/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-session/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-session)](http://cc.popphp.org/pop-session/)

[![Join the chat at https://popphp.slack.com](https://media.popphp.org/img/slack.svg)](https://popphp.slack.com)
[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Time-Based](#time-based)
* [Request-Based](#request-based)
* [Namespaces](#namespaces)

Overview
--------
`pop-session` is a component used to manage sessions and session data in the PHP web environment.
It includes the ability to also manage namespaces within the session as well as timed-based and
request-based expirations.

`pop-session` is a component of the [Pop PHP Framework](http://www.popphp.org/).

[Top](#pop-session)

Install
-------

Install `pop-session` using Composer.

    composer require popphp/pop-session

Or, require it in your composer.json file

    "require": {
        "popphp/pop-session" : "^4.0.0"
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
$sessMyApp->foo = 'bar'

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

[Top](#pop-session)
