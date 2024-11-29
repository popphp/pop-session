<?php

namespace {
    ob_start();
    $_SESSION = [];
}

namespace Pop\Session\Test {

    use Pop\Session\SessionNamespace;
    use PHPUnit\Framework\TestCase;

    class SessionNamespaceTest extends TestCase
    {

        public function testSessionNamespace()
        {
            $sess = new SessionNamespace('MyApp');
            $this->assertInstanceOf('Pop\Session\SessionNamespace', $sess);
            $this->assertTrue(isset($_SESSION['MyApp']));
            $this->assertEquals('MyApp', $sess->getNamespace());

            $sess->foo   = 'bar';
            $sess['baz'] = 123;
            $this->assertEquals('bar', $_SESSION['MyApp']['foo']);
            $this->assertEquals('bar', $sess->foo);
            $this->assertEquals('bar', $sess['foo']);
            $this->assertEquals(123, $_SESSION['MyApp']['baz']);
            $this->assertEquals(123, $sess->baz);
            $this->assertEquals(123, $sess['baz']);
            $this->assertTrue(isset($sess->foo));
            $this->assertTrue(isset($sess['foo']));
            $this->assertTrue(isset($sess->baz));
            $this->assertTrue(isset($sess['baz']));

            $this->assertEquals(2, count($sess->toArray()));
            $this->assertEquals(2, $sess->count());

            unset($sess->foo);
            unset($sess['baz']);

            $this->assertFalse(isset($sess->foo));
            $this->assertFalse(isset($sess->baz));
        }

        public function testSessionNamespaceException()
        {
            $this->expectException('Pop\Session\Exception');
            $sess = new SessionNamespace('_POP_SESSION_');
        }

        public function testSetTimedValue1()
        {
            $sess = new SessionNamespace('MyApp');
            $sess->setTimedValue('timed', 'value', 1);
            $this->assertEquals('value', $sess->timed);
        }

        #[runInSeparateProcess]
        public function testSetTimedValue2()
        {
            sleep(3);
            $sess = new SessionNamespace('MyApp');
            $this->assertFalse(isset($sess->timed));
        }

        public function testRequestValue1()
        {
            $sess = new SessionNamespace('MyApp');
            $sess->setRequestValue('request', 'value', 1);
            $this->assertEquals('value', $sess->request);
            $sess = new SessionNamespace('MyApp');
            $this->assertEquals('value', $sess->request);
            $sess = new SessionNamespace('MyApp');
            $this->assertNull($sess->request);
        }

        #[runInSeparateProcess]
        public function testRequestValue2()
        {
            $sess = new SessionNamespace('MyApp');
            $this->assertFalse(isset($sess->request));
        }

        public function testKill()
        {
            $sess = new SessionNamespace('MyApp');
            $sess->kill();
            $this->assertFalse(isset($_SESSION['MyApp']));
        }

    }

}
