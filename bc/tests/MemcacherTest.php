<?php
/**
 * User: inpu
 * Date: 20.09.13 11:53
 */

namespace bc\tests\model;

use bc\memcacher\MemcacheObject;
use bc\memcacher\Memcacher;

/**
 * Class MMCTest
 * @package bc\tests\model
 * @requires extension memcache
 */
class MemcacherTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var MemcacheObject
     */
    private $simpleValue;

    public function testSetValue() {
        $this->assertTrue(Memcacher::set($this->simpleValue));
    }

    /**
     * @depends testSetValue
     */
    public function testGetValue() {
        $this->assertEquals($this->simpleValue->value, Memcacher::get($this->simpleValue->key));
    }

    /**
     * @depends testSetValue
     */
    public function testDeleteValue() {
        $this->assertNotNull(Memcacher::get($this->simpleValue->key));
        Memcacher::del($this->simpleValue->key);
        $this->assertNull(Memcacher::get($this->simpleValue->key));
    }

    public function testExpire() {
        $expireVal = new MemcacheObject('expire_test', 'value', 1);
        Memcacher::set($expireVal);
        sleep(1);
        $this->assertFalse(Memcacher::exists($expireVal->key));
    }

    public function testGetWrong() {
        $this->assertNull(Memcacher::get('wrong_key'));
    }

    public function testTags() {
        $vals = array(
            new MemcacheObject('key1', 'value1', 3600, array('tag1')),
            new MemcacheObject('key2', 'value2', 3600, array('tag2')),
            new MemcacheObject('key3', 'value3', 3600, array('tag1', 'tag2')),
            new MemcacheObject('key4', 'value4', 1, array('tag1'))
        );

        foreach($vals as $val) {
            Memcacher::set($val);
        }
        Memcacher::set($vals[0]);

        $this->assertEquals(array('key1', 'key3', 'key4'), Memcacher::getTaggedKeys('tag1'));

        $savedVals = Memcacher::getByTag('tag1');
        $this->assertEquals(array($vals[0]->value, $vals[2]->value, $vals[3]->value), $savedVals);
        sleep(1);
        $savedVals = Memcacher::getByTag('tag1');
        $this->assertEquals(array($vals[0]->value, $vals[2]->value), $savedVals);
        Memcacher::delByTag('tag2');
        $this->assertCount(0, Memcacher::getByTag('tag2'));
    }

    public function testFlush() {
        Memcacher::set($this->simpleValue);
        Memcacher::flush();
        $this->assertNull(Memcacher::get($this->simpleValue->key));
    }

    public function testObjectSave() {
        $testObject = new \stdClass();
        $testObject->field = 'test';
        $val = new MemcacheObject('test_object', $testObject);
        $this->assertTrue(Memcacher::set($val));
        $this->assertEquals($testObject, Memcacher::get('test_object'));
    }

    protected function setUp() {
        $this->simpleValue = new MemcacheObject('test_key', 'value');
    }

    protected function tearDown() {
        $this->simpleValue = null;
    }

    public static function tearDownAfterClass() {
        Memcacher::flush();
    }

}