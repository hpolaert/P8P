<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */
namespace P8PTest\Container;

use P8P\Container\Container;

/**
 * Container Test Cases
 *
 * Asserts P8P\Core\Container functionalities
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container Container to be reused in test cases
     */
    private $testContainer;

    /**
     * Container instantiation
     */
    public function __construct()
    {
        $this->testContainer = new Container();
        $this->assertInstanceOf('P8P\Container\Container', $this->testContainer);
    }

    /**
     * @Covers Container::offsetGet
     * @Covers Container::offsetSet
     */
    public function testShouldReturnString()
    {
        $this->testContainer['aString'] = 'Hello World!';
        $this->assertEquals('Hello World!', $this->testContainer['aString']);
    }

    /**
     * @Covers Container::offsetGet
     * @Covers Container::offsetSet
     */
    public function testShouldReturnArray()
    {
        $this->testContainer['anArray'] = [1, 2, 3];
        $this->assertEquals(1, $this->testContainer['anArray'][0]);
    }

    /**
     * @Covers Container::offsetGet
     * @Covers Container::offsetSet
     */
    public function testShouldReturnInvokable()
    {
        $this->testContainer['anInvokable'] = new Fixtures\Invokable();
        $this->assertEquals('Hello World!', $this->testContainer['anInvokable']);
        $this->assertNotInstanceOf('P8PTest\Container\Fixtures\Invokable', $this->testContainer['anInvokable']);
    }

    /**
     * @Covers Container::offsetGet
     * @Covers Container::offsetSet
     */
    public function testShouldReturnNotInvokable()
    {
        $this->testContainer['aNotInvokable'] = new Fixtures\NotInvokable();
        $this->assertInstanceOf('P8PTest\Container\Fixtures\NotInvokable', $this->testContainer['aNotInvokable']);
    }

    /**
     * @Covers Container::offsetGet
     * @Covers Container::offsetSet
     * @Covers Container::forceNew
     */
    public function testForceNewInstance()
    {
        $this->testContainer['library'] = $this->testContainer->forceNew(
            function () {
                return new Fixtures\Library();
            }
        );
        $instanceOne                    = $this->testContainer['library'];
        $instanceTwo                    = $this->testContainer['library'];
        $this->assertInstanceOf('P8PTest\Container\Fixtures\Library', $instanceOne);
        $this->assertInstanceOf('P8PTest\Container\Fixtures\Library', $instanceTwo);
        $this->assertNotSame($instanceOne, $instanceTwo);
    }

    /**
     * @Covers Container::offsetGet
     * @Covers Container::offsetSet
     */
    public function testShareInstance()
    {
        $this->testContainer['library'] = function () {
            return new Fixtures\Library();
        };
        $instanceOne                    = $this->testContainer['library'];
        $instanceTwo                    = $this->testContainer['library'];
        $this->assertInstanceOf('P8PTest\Container\Fixtures\Library', $instanceOne);
        $this->assertInstanceOf('P8PTest\Container\Fixtures\Library', $instanceTwo);
        $this->assertSame($instanceOne, $instanceTwo);
    }

    /**
     * @expectedException \P8P\Container\Exception\NotFoundException
     * @expectedExceptionMessage Error, key "doesNotExist" is not registered
     */
    public function testNotFoundException()
    {
        $this->testContainer['doesNotExist'];
    }

    /**
     * @expectedException \P8P\Container\Exception\ContainerException
     * @expectedExceptionMessage Error, "Hello" is not instantiable
     */
    public function testContainerException()
    {
        $notInstantiable                        = 'Hello';
        $this->testContainer['notInstantiable'] = $this->testContainer->forceNew($notInstantiable);
    }

    /**
     * @Covers Container::offsetUnset
     * @Covers Container::offsetSet
     */
    public function testUnsetKey()
    {
        $this->testContainer['testVar']     = 'Hello world!';
        $this->testContainer['testClosure'] = function () {
            return 'Hello world!';
        };
        $this->assertEquals('Hello world!', $this->testContainer['testVar']);
        $this->assertEquals('Hello world!', $this->testContainer['testClosure']);
        unset($this->testContainer['testVar']);
        unset($this->testContainer['testClosure']);
        $this->assertFalse(isset($this->testContainer['testVar']));
        $this->assertFalse(isset($this->testContainer['testClosure']));
    }
    
    /**
     * @Covers Container::has
     */
    public function testHasAlias()
    {
    	$this->testContainer['testVar'] = 'Index is set';
    	$this->assertTrue($this->testContainer->has('testVar'));
    	$this->assertFalse($this->testContainer->has('thisVarDoesNotExist'));
    }
    
    /**
     * @Covers Container::get
     */
    public function testGetAlias()
    {
    	$this->testContainer['testEqual'] = 'Hello world!';
    	$this->testContainer['testNotEqual'] = 'Alpha';
    	$this->assertEquals('Hello world!', $this->testContainer->get('testEqual'));
    	$this->assertNotEquals('Beta', $this->testContainer->get('testNotEqual'));
    }
    
    /**
     * @Covers Container::output
     */
    public function testRawOutput()
    {
    	
    	$this->testContainer['anInvokable'] = new Fixtures\Invokable();
    	$this->assertEquals('Hello World!', $this->testContainer->output('anInvokable')->sayHello());
    	$this->assertEquals('Hello World!', $this->testContainer['anInvokable']);
    }
}