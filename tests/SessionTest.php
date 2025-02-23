<?php

namespace {
    ob_start();
    $_SESSION = [];
}

namespace Pop\Session\Test {

    use Pop\Session\Session;
    use Pop\Session\SessionNamespace;
    use PHPUnit\Framework\TestCase;

    class SessionTest extends TestCase
    {

        public function testSession()
        {
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

        public function testOptions()
        {
            Session::getInstance()->kill();

            $sess         = Session::getInstance(['domain' => 'localhost']);
            $cookieParams = session_get_cookie_params();

            $this->assertEquals('localhost', $cookieParams['domain']);
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

        public function testKill()
        {
            $sess = Session::getInstance();
            $sess->kill();
            $this->assertNull($_SESSION);
        }

    }

}
