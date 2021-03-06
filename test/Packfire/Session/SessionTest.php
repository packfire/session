<?php

namespace Packfire\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected $stub;

    /**
     * @var Packfire\Session\Session
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        SessionState::reset();

        $storageStub = $this->getMock('Packfire\\Session\\StorageInterface');
        $storageStub->expects($this->once())
            ->method('load');
        $this->stub = $storageStub;

        $this->object = new Session($storageStub);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \Packfire\Session\Session::__construct
     */
    public function testStorage()
    {
        $property = new \ReflectionProperty($this->object, 'storage');
        $property->setAccessible(true);
        $this->assertEquals($this->stub, $property->getValue($this->object));
    }

    /**
     * @covers \Packfire\Session\Session::get
     */
    public function testGet()
    {
        $this->stub->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));
        $this->assertEquals('test', $this->object->get('test'));
    }

    /**
     * @covers \Packfire\Session\Session::set
     */
    public function testSet()
    {
        $this->stub->expects($this->once())
            ->method('set')
            ->with($this->equalTo('test'), $this->equalTo('value'));
        $this->object->set('test', 'value');
    }

    /**
     * @covers \Packfire\Session\Session::clear
     */
    public function testClear()
    {
        $this->stub->expects($this->once())
            ->method('clear')
            ->with();
        $this->object->clear();
    }

    /**
     * @covers \Packfire\Session\Session::invalidate
     */
    public function testInvalidate()
    {
        $this->stub->expects($this->once())
            ->method('clear')
            ->with();
        $this->stub->expects($this->once())
            ->method('regenerate')
            ->with($this->equalTo(true));

        $this->object->invalidate();
    }

    /**
     * @covers \Packfire\Session\Session::regenerate
     */
    public function testRegenerate()
    {
        $this->stub->expects($this->once())
            ->method('regenerate')
            ->with();

        $this->object->regenerate();
    }

    /**
     * @covers \Packfire\Session\Session::bucket
     */
    public function testBucket()
    {
        $this->stub->expects($this->once())
            ->method('bucket')
            ->with($this->equalTo('test'))
            ->will($this->returnValue(null));

        $this->stub->expects($this->once())
            ->method('register')
            ->with($this->isInstanceOf('Packfire\\Session\\BucketInterface'));

        $this->object->bucket('test');
    }

    /**
     * @covers \Packfire\Session\Session::bucket
     */
    public function testBucket2()
    {
        $this->stub->expects($this->once())
            ->method('bucket')
            ->with($this->equalTo('test'))
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->bucket('test'));
    }

    public function testRegister()
    {
        $this->assertFalse(SessionState::queryStart());
        $this->object->register();
        $this->assertTrue(SessionState::queryStart());
    }

    public function testUnregister()
    {
        $this->assertFalse(SessionState::queryStart());
        $this->object->register();
        $this->assertTrue(SessionState::queryStart());
        $this->object->unregister();
        $this->assertFalse(SessionState::queryStart());
    }

    public function testDetectCookie()
    {
        $this->assertFalse(Session::detectCookie());
        $_COOKIE[session_name()] = 'test';
        $this->assertTrue(Session::detectCookie());
    }
}
