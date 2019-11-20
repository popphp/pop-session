<?php

namespace {
    ob_start();
    $_SESSION = [];
}

namespace Pop\Validator\Test {

    use Pop\Session\Session;
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

            unset($sess->foo);
            unset($sess['baz']);

            $this->assertFalse(isset($sess->foo));
            $this->assertFalse(isset($sess->baz));
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
        }

        public function testRegenerateId()
        {
            $sess = Session::getInstance();
            $sess->regenerateId();
            $this->assertNotEmpty($sess->getId());
        }

        public function testSetTimedValue1()
        {
            $sess = Session::getInstance();
            $sess->setTimedValue('timed', 'value', 1);
            $this->assertEquals('value', $sess->timed);
        }

        /**
         * @runInSeparateProcess
         */
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
            $sess = Session::getInstance();
            $this->assertEquals('value', $sess->request);
        }

        /**
         * @runInSeparateProcess
         */
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