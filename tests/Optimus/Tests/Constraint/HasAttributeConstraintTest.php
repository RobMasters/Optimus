<?php

namespace Optimus\Tests\Constraint;

use Optimus\Constraint\HasAttributeConstraint;

class HasAttributeConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testConstrain_returnsTrue_whenAttributeExists()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('list');

        $constraint = new HasAttributeConstraint('id');
        $this->assertTrue($constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_returnsFalse_whenAttributeDoesNotExist()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('list');

        $constraint = new HasAttributeConstraint('class');
        $this->assertFalse($constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_returnsTrue_whenAttributeExistsAndMatchesValue()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('list');

        $constraint = new HasAttributeConstraint('id', 'list');
        $this->assertTrue($constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_returnsFalse_whenAttributeExistsButDoesNotMatchValue()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('list');

        $constraint = new HasAttributeConstraint('id', 'unordered-list');
        $this->assertFalse($constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_returnsTrue_whenAttributeExistsAndClassIsMatchedExactly()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('wrapper');

        $constraint = new HasAttributeConstraint('class', 'container small-text');
        $this->assertTrue($constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_returnsFalse_whenAttributeExistsButOnlySingleClassNameMatches()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('wrapper');

        $constraint = new HasAttributeConstraint('class', 'container');
        $this->assertFalse($constraint->check($element));
    }

    /**
     * @test
     */
    public function testConstrain_returnsFalse_whenAttributeExistsAndClassesExistButInDifferentOrder()
    {
        $document = $this->getDocument();
        $element = $document->getElementById('wrapper');

        $constraint = new HasAttributeConstraint('class', 'small-text container');
        $this->assertFalse($constraint->check($element));
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