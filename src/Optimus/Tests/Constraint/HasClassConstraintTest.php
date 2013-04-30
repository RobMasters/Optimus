<?php

namespace Optimus\Tests\Constraint;

use Optimus\Constraint\HasClassConstraint;

class HasClassConstraintTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Provider to assert the correct behaviour for an element with multiple classes
     */
    public function wrapperConstraintValuesProvider()
    {
        return array(
            // matches first class
            array('container', true),

            // matches second class
            array('small-text', true),

            // matches exact class attribute
            array('container small-text', true),

            // matches all specified classes despite being defined in a different order
            array('small-text container', true),

            // matches each class
            array(array('container', 'small-text'), true),
            array(array('small-text', 'container'), true),

            // doesn't match unknown class
            array('wrapper', false),

            // doesn't match if any provided class is unknown
            array(array('container', 'small-text', 'wrapper'), false),
        );
    }

    /**
     * @test
     * @dataProvider wrapperConstraintValuesProvider
     */
    public function testConstrain_forElementWithMultipleClasses($constraintValues, $expected)
    {
        $document = $this->getDocument();
        $element = $document->getElementById('wrapper');

        $constraint = new HasClassConstraint($constraintValues);
        $this->assertEquals($expected, $constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_throwsException_whenValueIsNotValid()
    {
        $this->setExpectedException(
            '\Optimus\Exception\InvalidArgumentException',
            'HasClassConstraint only accepts a single array or string argument'
        );

        new HasClassConstraint(1);
    }

    /**
     * @test
     */
    public function testConstrain_throwsException_whenEmptyClassProvided()
    {
        $this->setExpectedException(
            '\Optimus\Exception\InvalidArgumentException',
            'Class name cannot be empty'
        );

        new HasClassConstraint('');
    }

    /**
     * @test
     */
    public function testConstrain_throwsException_whenEmptyClassProvidedInArray()
    {
        $this->setExpectedException(
            '\Optimus\Exception\InvalidArgumentException',
            'Class name cannot be empty'
        );

        new HasClassConstraint(array('first', '', 'last'));
    }

    /**
     * @return \DOMDocument
     */
    protected function getDocument()
    {
        $dom = new \DOMDocument();
        $html = <<<ENDHTML
<html>
    <head></head>
    <body>
        <div>
            <h1 class="title">Heading</h1>
            <ul id="list">
                <li>First</li>
                <li>Second</li>
            </ul>
            <div id="wrapper" class="container small-text">
                Some text
                <span>
                    in a span
                </span>
            </div>
        </div>
    </body>
</html>
ENDHTML;

        $dom->loadHTML($html);

        return $dom;
    }
}