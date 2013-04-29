<?php

namespace Optimus\Tests\Constraint;

use Optimus\Constraint\DepthConstraint;

class DepthConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DepthConstraint
     */
    protected $constraint;

    /**
     * @var \DOMDocument
     */
    protected $document;

    protected function setUp()
    {
        $this->constraint = new DepthConstraint();
        $this->document = new \DOMDocument();
        $this->document->loadHTML('<div><ul><li><span>Hello</span></li></ul></div>');
    }

    /**
     * @return array
     */
    public function minimumDepthProvider()
    {
        return array(
            array(4, true),
            array(3, false),
            array(2, false)
        );
    }

    /**
     * @test
     * @dataProvider minimumDepthProvider
     *
     * @param $minimumDepth
     * @param $expected
     */
    public function testMinimumDepthConstraint($minimumDepth, $expected)
    {
        $node = $this->document->getElementsByTagName('ul')->item(0); // depth = 3 (html > body > div > ul)

        $this->constraint->setMinimum($minimumDepth);
        $this->assertEquals($expected, $this->constraint->constrain($node));
    }

    /**
     * @return array
     */
    public function maximumDepthProvider()
    {
        return array(
            array(2, true),
            array(3, false),
            array(4, false)
        );
    }

    /**
     * @test
     * @dataProvider maximumDepthProvider
     *
     * @param $maximumDepth
     * @param $expected
     */
    public function testMaximumDepthConstraint($maximumDepth, $expected)
    {
        $node = $this->document->getElementsByTagName('ul')->item(0); // depth = 3 (html > body > div > ul)

        $this->constraint->setMaximum($maximumDepth);
        $this->assertEquals($expected, $this->constraint->constrain($node));
    }

    /**
     * @test
     */
    public function testThrowsExceptionWhenMinimumEqualsMaximum()
    {
        $this->setExpectedException(
            'Optimus\Exception\ConstraintException',
            'If specifying the minimum and maximum depth, the minimum must be lower than the maximum'
        );

        $this->constraint->setMinimum(5);
        $this->constraint->setMaximum(5);
        $this->constraint->constrain(new \DOMElement('div'));
    }

    /**
     * @test
     */
    public function testThrowsExceptionWhenMinimumGreaterThanMaximum()
    {
        $this->setExpectedException(
            'Optimus\Exception\ConstraintException',
            'If specifying the minimum and maximum depth, the minimum must be lower than the maximum'
        );

        $this->constraint->setMinimum(5);
        $this->constraint->setMaximum(4);
        $this->constraint->constrain(new \DOMElement('div'));
    }

    public function eventNameProvider()
    {
        return array(
            array('div'),
            array('div.badger'),
            array('div.badger.monkey'),
            array('div#beaver.badger'),
            array('div#beaver.badger.monkey'),
            array('#beaver.badger.monkey'),
            array('div.badger li.monkey')
        );
    }

    /**
     * @dataProvider eventNameProvider
     */
    public function testNothing($eventName)
    {
        if (preg_match('/^([a-z]*)(?:#([a-z0-9_-]+))?((?:\.[a-z0-9_-]+)*)$/', $eventName, $matches)) {
            $tag = $matches[1];
            $id = $matches[2];
            $classes = explode('.', trim($matches[3], '.'));
            $a = 1;
        }
        $this->assertTrue(true);
    }
}