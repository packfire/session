<?php

namespace Packfire\Session;

use Packfire\FuelBlade\Container;

/**
 * Test class for Messenger.
 * Generated by PHPUnit on 2012-07-13 at 12:14:49.
 */
class MessengerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Packfire\Session\Messenger
     */
    protected $object;
    private $ioc;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * @covers \Packfire\Session\Messenger::__invoke
     */
    protected function setUp()
    {
        $this->object = new Messenger;
        $this->ioc = new Container();
        $bucket = $this->ioc;
        $bucket['session.storage'] = $this->getMockForAbstractClass('Packfire\\Session\\MockStorage');

        $bucket['session'] = $bucket->share(
            function ($c) {
                return new Session($c['session.storage']);
            }
        );
        $bucket['messenger'] = $this->object;

        call_user_func($this->object, $this->ioc);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \Packfire\Session\Messenger::send
     */
    public function testSend()
    {
        $this->object->send('test', 'sofia');
        $this->assertEquals(array('Messenger' => array('$sofia/test' => true)), $this->ioc['session.storage']->data());
    }

    /**
     * @covers \Packfire\Session\Messenger::send
     */
    public function testSend2()
    {
        $this->object->send('test', 'sofia', 'test message');
        $this->assertEquals(array('Messenger' => array('$sofia/test' => 'test message')), $this->ioc['session.storage']->data());
    }

    /**
     * @covers \Packfire\Session\Messenger::send
     */
    public function testSend3()
    {
        $this->object->send('test', array('sofia', 'elenor'));
        $this->assertEquals(array('Messenger' => array('$sofia/test' => true, '$elenor/test' => true)), $this->ioc['session.storage']->data());
    }

    /**
     * @covers \Packfire\Session\Messenger::send
     */
    public function testSend4()
    {
        $this->object->send('msg');
        $this->assertEquals(array('Messenger' => array('${global}/msg' => true)), $this->ioc['session.storage']->data());
    }

    /**
     * @covers \Packfire\Session\Messenger::send
     */
    public function testSend5()
    {
        $this->object->send('msg', null, 10);
        $this->assertEquals(array('Messenger' => array('${global}/msg' => 10)), $this->ioc['session.storage']->data());
    }

    /**
     * @covers \Packfire\Session\Messenger::check
     */
    public function testCheck()
    {
        $this->assertFalse($this->object->check('note'));
        $this->assertFalse($this->object->check('msg', null));
        $this->assertFalse($this->object->check('test', 'sofia'));

        $this->object->send('msg', null, 'woah');
        $this->assertTrue($this->object->check('msg', null));

        $this->object->send('test', 'sofia', 'run');
        $this->assertTrue($this->object->check('test', 'sofia'));

        $this->object->send('note', __CLASS__ . ':' . __FUNCTION__);
        $this->assertTrue($this->object->check('note'));
    }

    /**
     * @covers \Packfire\Session\Messenger::read
     */
    public function testRead()
    {
        $this->assertNull($this->object->read('note'));
        $this->object->send('note', __CLASS__ . ':' . __FUNCTION__);
        $this->assertEquals(true, $this->object->read('note'));
        $this->assertNull($this->object->read('note'));

        $this->assertNull($this->object->read('note2'));
        $this->object->send('note2', __CLASS__ . ':' . __FUNCTION__, 'pretty please?');
        $this->assertEquals('pretty please?', $this->object->read('note2'));
        $this->assertNull($this->object->read('note2'));

        $this->assertNull($this->object->read('msg', 'elenor'));
        $this->object->send('msg', 'elenor', 'pretty!');
        $this->assertEquals('pretty!', $this->object->read('msg', 'elenor'));
        $this->assertNull($this->object->read('msg', 'elenor'));
    }

    /**
     * @covers \Packfire\Session\Messenger::clear
     */
    public function testClear()
    {
        $this->object->send('note', __CLASS__ . ':' . __FUNCTION__);
        $this->object->send('note2', __CLASS__ . ':' . __FUNCTION__, 'pretty please?');
        $data = $this->ioc['session.storage']->data();
        $this->assertCount(2, $data['Messenger']);
        $this->object->clear();
        $this->assertEquals(array('Messenger' => array()), $this->ioc['session.storage']->data());
    }
}
