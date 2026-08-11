<?php

namespace {
    ob_start();
    $_SESSION = [];
}

namespace Pop\Session\Test {

    use Pop\Session\Session;
    use Pop\Session\SessionNamespace;
    use PHPUnit\Framework\Attributes\RunInSeparateProcess;
    use PHPUnit\Framework\TestCase;
    use SessionHandlerInterface;

    class ArraySessionHandler implements SessionHandlerInterface
    {
        public array $store = [];

        public function open(string $path, string $name): bool
        {
            return true;
        }

        public function close(): bool
        {
            return true;
        }

        public function read(string $id): string|false
        {
            return $this->store[$id] ?? '';
        }

        public function write(string $id, string $data): bool
        {
            $this->store[$id] = $data;
            return true;
        }

        public function destroy(string $id): bool
        {
            unset($this->store[$id]);
            return true;
        }

        public function gc(int $max_lifetime): int|false
        {
            return 0;
        }
    }

    class SessionTest extends TestCase
    {

        public function testSession()
        {
            Session::getInstance()->kill();

            $sess = Session::getInstance();
            $sess->foo   = 'bar';
            $sess['baz'] = 123;
            $this->assertEquals('bar', $_SESSION['foo']);
            $this->assertEquals('bar', $sess->foo);
            $this->assertEquals('bar', $sess['foo']);
            $this->assertEquals(123, $_SESSION['baz']);
            $this->assertEquals(123, $sess->baz);
            $this->assertEquals(123, $sess['baz']);
            $this->assertTrue(isset($sess->foo));
            $this->assertTrue(isset($sess['foo']));
            $this->assertTrue(isset($sess->baz));
            $this->assertTrue(isset($sess['baz']));
            $nsSess = new SessionNamespace('MyApp');
            $this->assertEquals(3, count($sess->toArray()));
            $this->assertEquals(3, $sess->count());

            $i = 0;
            foreach ($sess as $s) {
                $i++;
            }
            $this->assertEquals(3, $i);

            unset($sess->foo);
            unset($sess['baz']);

            $this->assertFalse(isset($sess->foo));
            $this->assertFalse(isset($sess->baz));
        }

        public function testJsonSerialize()
        {
            $sess      = Session::getInstance();
            $sess->foo = 'bar';

            $this->assertEquals(json_encode($sess->toArray()), json_encode($sess));
        }

        public function testOptions()
        {
            Session::getInstance()->kill();

            $sess         = Session::getInstance(['domain' => 'localhost']);
            $cookieParams = session_get_cookie_params();

            $this->assertEquals('localhost', $cookieParams['domain']);
        }

        #[runInSeparateProcess]
        public function testOptionsPathFallback()
        {
            $defaultParams = session_get_cookie_params();
            $sess          = Session::getInstance(['domain' => 'localhost']);
            $cookieParams  = session_get_cookie_params();

            $this->assertEquals($defaultParams['path'], $cookieParams['path']);
        }

        #[runInSeparateProcess]
        public function testGetInstanceWithExternallyStartedSession()
        {
            if (session_id() === '') {
                session_start();
            }

            $sess = Session::getInstance();
            $this->assertNotEmpty($sess->getId());
            $this->assertNotEmpty($sess->getName());

            $sess->setRequestValue('extRequest', 'value', 1);
            $this->assertEquals('value', $sess->extRequest);

            $sess = Session::getInstance();
            $this->assertEquals('value', $sess->extRequest);

            $sess = Session::getInstance();
            $this->assertFalse(isset($sess->extRequest));
        }

        #[runInSeparateProcess]
        public function testSetHandler()
        {
            $handler = new ArraySessionHandler();
            Session::setHandler($handler);

            $sess      = Session::getInstance();
            $sess->foo = 'bar';
            $sess->close();

            $this->assertNotEmpty($handler->store);
            $this->assertStringContainsString('bar', reset($handler->store));
        }

        #[runInSeparateProcess]
        public function testSetHandlerAfterInstanceThrows()
        {
            $this->expectException('Pop\Session\Exception');
            Session::getInstance();
            Session::setHandler(new ArraySessionHandler());
        }

        #[runInSeparateProcess]
        public function testDefaultHardening()
        {
            Session::getInstance();
            $cookieParams = session_get_cookie_params();

            $this->assertTrue($cookieParams['httponly']);
            $this->assertEquals('Lax', $cookieParams['samesite']);
            $this->assertEquals('1', ini_get('session.use_strict_mode'));
        }

        #[runInSeparateProcess]
        public function testConstructorOptionsHonored()
        {
            Session::getInstance([
                'lifetime'    => 1234,
                'path'        => '/custom',
                'secure'      => true,
                'httponly'    => false,
                'samesite'    => 'Strict',
                'strict_mode' => false
            ]);
            $cookieParams = session_get_cookie_params();

            $this->assertEquals(1234, $cookieParams['lifetime']);
            $this->assertEquals('/custom', $cookieParams['path']);
            $this->assertTrue($cookieParams['secure']);
            $this->assertFalse($cookieParams['httponly']);
            $this->assertEquals('Strict', $cookieParams['samesite']);
            $this->assertEquals('0', ini_get('session.use_strict_mode'));
        }

        #[runInSeparateProcess]
        public function testDefaultHardeningRespectsIniSamesite()
        {
            ini_set('session.cookie_samesite', 'None');
            Session::getInstance();
            $cookieParams = session_get_cookie_params();

            $this->assertEquals('None', $cookieParams['samesite']);
        }

        #[runInSeparateProcess]
        public function testInitRepairsPartialBookkeeping()
        {
            session_start();
            $_SESSION['_POP_SESSION_'] = ['other' => 'value'];

            Session::getInstance();

            $this->assertEquals([], $_SESSION['_POP_SESSION_']['requests']);
            $this->assertEquals([], $_SESSION['_POP_SESSION_']['expirations']);
        }

        #[runInSeparateProcess]
        public function testInitSweepsExistingBookkeeping()
        {
            session_start();
            $_SESSION['stale'] = 'value';
            $_SESSION['_POP_SESSION_'] = [
                'requests'    => ['stale' => ['current' => 5, 'limit' => 1]],
                'expirations' => []
            ];

            $sess = Session::getInstance();

            $this->assertFalse(isset($sess->stale));
        }

        #[runInSeparateProcess]
        public function testKillExpiresCookie()
        {
            $sess = Session::getInstance();
            $_COOKIE[$sess->getName()] = $sess->getId();

            $sess->kill();

            $this->assertNull($_SESSION);
        }

        public function testSetException()
        {
            $this->expectException('Pop\Session\Exception');
            $sess = Session::getInstance();
            $sess['_POP_SESSION_'] = 'bad';
        }

        public function testUnsetException()
        {
            $this->expectException('Pop\Session\Exception');
            $sess = Session::getInstance();
            unset($sess['_POP_SESSION_']);
        }

        public function testGetId()
        {
            $sess = Session::getInstance();
            $this->assertNotEmpty($sess->getId());
            $this->assertNotEmpty($sess->getName());
        }

        public function testRegenerateId()
        {
            $sess = Session::getInstance();
            $sess->regenerateId();
            $this->assertNotEmpty($sess->getId());
            $this->assertNotEmpty($sess->getName());
        }

        public function testSetTimedValue1()
        {
            $sess = Session::getInstance();
            $sess->setTimedValue('timed', 'value', 1);
            $this->assertEquals('value', $sess->timed);
        }

        #[runInSeparateProcess]
        public function testSetTimedValue2()
        {
            sleep(3);
            $sess = Session::getInstance();
            $this->assertFalse(isset($sess->timed));
        }

        public function testRequestValue1()
        {
            $sess = Session::getInstance();
            $sess->setRequestValue('request', 'value', 1);
            $this->assertEquals('value', $sess->request);
            $sess = Session::getInstance();
            $this->assertEquals('value', $sess->request);
        }

        #[runInSeparateProcess]
        public function testRequestValue2()
        {
            $sess = Session::getInstance();
            $this->assertFalse(isset($sess->request));
        }

        public function testSweepRequestValue()
        {
            $sess = Session::getInstance();
            $sess->setRequestValue('sweepRequest', 'value', 1);
            $this->assertEquals('value', $sess->sweepRequest);

            $sess->sweep();
            $this->assertEquals('value', $sess->sweepRequest);

            $sess->sweep();
            $this->assertFalse(isset($sess->sweepRequest));
        }

        public function testSweepTimedValue()
        {
            $sess = Session::getInstance();
            $sess->setTimedValue('sweepTimed', 'value', 1);
            $this->assertEquals('value', $sess->sweepTimed);

            sleep(2);
            $sess->sweep();
            $this->assertFalse(isset($sess->sweepTimed));
        }

        public function testKill()
        {
            $sess = Session::getInstance();
            $sess->kill();
            $this->assertNull($_SESSION);
        }

        public function testSweepAfterKill()
        {
            $sess = Session::getInstance();
            $sess->kill();
            $sess->sweep();
            $this->assertInstanceOf('Pop\Session\Session', $sess);
        }

        #[runInSeparateProcess]
        public function testCloseThenKill()
        {
            $sess = Session::getInstance();
            $sess->close();
            $sess->kill();
            $this->assertNull($_SESSION);
        }

        #[runInSeparateProcess]
        public function testClose()
        {
            $sess = Session::getInstance();
            $sess->close();
            $this->assertEquals(PHP_SESSION_NONE, session_status());
        }

    }

}
