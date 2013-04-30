<?php

namespace Optimus\Tests;

use Optimus\EventDispatcher;
use \Mockery as m;
use Optimus\Transformer\TransformerInterface;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * @test
     * @covers addTransformer
     */
    public function testAddTransformer_doesNotAddConstraintWhenNodeNameIsOnlyTag()
    {
        $transformer = $this->getMockTransformer();
        $transformer->shouldReceive('addConstraint')->never();

        $this->dispatcher->addTransformer('div', $transformer);
        $this->assertCount(1, $this->dispatcher->getListeners('div'));
    }

    /**
     * @test
     * @covers addTransformer
     */
    public function testAddTransformer_addsClassConstraintWhenNodeNameIsTagAndId()
    {
        $transformer = $this->getMockTransformer();
        $transformer->shouldReceive('addConstraint')->never()->with(m::type('\Optimus\Constraint\HasClassConstraint'));
        $transformer->shouldReceive('addConstraint')->once()->with(m::type('\Optimus\Constraint\HasAttributeConstraint'));

        $this->dispatcher->addTransformer('div#an-id', $transformer);
        $this->assertCount(1, $this->dispatcher->getListeners('div'));
    }

    /**
     * @test
     * @covers addTransformer
     */
    public function testAddTransformer_addsClassConstraintWhenNodeNameIsTagAndClass()
    {
        $transformer = $this->getMockTransformer();
        $transformer->shouldReceive('addConstraint')->once()->with(m::type('\Optimus\Constraint\HasClassConstraint'));
        $transformer->shouldReceive('addConstraint')->never()->with(m::type('\Optimus\Constraint\HasAttributeConstraint'));

        $this->dispatcher->addTransformer('div.example', $transformer);
        $this->assertCount(1, $this->dispatcher->getListeners('div'));
    }

    /**
     * @test
     * @covers addTransformer
     */
    public function testAddTransformer_addsClassConstraintWhenNodeNameIsTagAndIdAndClass()
    {
        $transformer = $this->getMockTransformer();
        $transformer->shouldReceive('addConstraint')->once()->with(m::type('\Optimus\Constraint\HasAttributeConstraint'));
        $transformer->shouldReceive('addConstraint')->once()->with(m::type('\Optimus\Constraint\HasClassConstraint'));

        $this->dispatcher->addTransformer('div#container.example', $transformer);
        $this->assertCount(1, $this->dispatcher->getListeners('div'));
    }

    /**
     * @return array
     */
    public function invalidSelectorProvider()
    {
        return array(
            array('div ul'),
            array('div > span'),
            array('li[class]'),
            array('li:first'),
            array('li:nth-child(2)')
        );
    }

    /**
     * @test
     * @covers addTransformer
     * @dataProvider invalidSelectorProvider
     */
    public function testAddTransformer_throwsExceptionWhenGivenInvalidSelector($selector)
    {
        $this->setExpectedException(
            '\Optimus\Exception\InvalidArgumentException',
            sprintf('Invalid node selector: `%s`', $selector)
        );
        $this->dispatcher->addTransformer($selector, $this->getMockTransformer());
    }

    /**
     * @test
     * @covers addTransformer
     */
    public function testAddTransformer_addsWildcardListenerWithConstraintWhenNoTagProvided()
    {
        $transformer = $this->getMockTransformer();
        $transformer->shouldReceive('addConstraint')->once()->with(m::type('\Optimus\Constraint\HasAttributeConstraint'));

        $this->dispatcher->addTransformer('#container', $transformer);
        $this->assertCount(1, $this->dispatcher->getListeners('*'));
    }

    /**
     * @test
     * @covers addTransformer
     */
    public function testAddTransformer_clonesTransformerWhenApplyingAutomaticConstraints()
    {
        $transformer = $this->getMockTransformer();
        $transformer->shouldReceive('addConstraint')->with(m::type('\Optimus\Constraint\HasAttributeConstraint'));

        $this->dispatcher->addTransformer(array('#container', 'div.container'), $transformer);

        $wildcardListeners = $this->dispatcher->getListeners('*');
        $divListeners = $this->dispatcher->getListeners('div');

        $this->assertCount(1, $wildcardListeners);
        $this->assertCount(1, $divListeners);
    }

    /**
     * @return m\MockInterface
     */
    protected function getMockTransformer()
    {
        $mock = m::mock('\Optimus\Transformer\TransformerInterface');

        return $mock;
    }
}