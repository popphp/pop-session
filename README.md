pop-session
===========

[![Build Status](https://travis-ci.org/popphp/pop-session.svg?branch=master)](https://travis-ci.org/popphp/pop-session)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-session)](http://cc.popphp.org/pop-session/)

OVERVIEW
--------
`pop-session` is a component used to manage and manipulate sessions in the PHP
web environment.

`pop-session` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-session` using Composer.

    composer require popphp/pop-session

BASIC USAGE
-----------

```php
use Pop\Session\Session;

$sess = Session::getInstance();

// Set session values
$sess->foo   = 'bar';
$sess['baz'] = 123;

// Access session values
echo $sess['foo'];
echo $sess->baz;

// Unset session values
unset($sess->foo);
unset($sess['baz']);

// Kill/clear out the session
$sess->kill();
```

ADVANCED USAGE
--------------

##### Session values available based on time expiration:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->setTimedValue('foo', 'bar', 10); // # of seconds

if (isset($sess->foo)) {
    echo $sess->foo;
} else {
    echo 'Nope!';
}
```

##### Session values available based on number of requests:

```php
use Pop\Session\Session;

$sess = Session::getInstance();
$sess->setRequestValue('foo', 'bar', 1); // # of requests

if (isset($sess->foo)) {
    echo $sess->foo;
} else {
    echo 'Nope!';
}
```

##### Session values available based on namespace:

```php
use Pop\Session\SessionNamespace;

$sess = new SessionNamespace('MyApp');
$sess->foo = 'bar'

if (isset($sess->foo)) {
    echo $sess->foo;  // Only available under the namespace passed.
} else {
    echo 'Nope!';
}
```
