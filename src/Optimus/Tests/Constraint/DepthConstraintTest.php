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
        $this->constraint->constrain(new \DOMNode());
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
        $this->constraint->constrain(new \DOMNode());
    }
}