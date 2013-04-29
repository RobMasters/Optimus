<?php

namespace Optimus\Tests\Transformer;

use Optimus\Transformer\AddPositionClassTransformer;
use \Mockery as m;

class AddPositionClassTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddPositionClassTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $this->transformer = new AddPositionClassTransformer();
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function testAddsFirstClassWhenFirstElement()
    {
        $document = new \DOMDocument();
        $document->loadHTML('<div>a</div><div>b</div>');

        /** @var $node \DOMElement */
        $node = $document->getElementsByTagName('div')->item(0);
        $event = $this->getMockEvent($node, 0);
        $this->transformer->transform($event);

        $this->assertContains('first', $node->getAttribute('class'));
    }

    /**
     * @test
     */
    public function testAddsLastClassWhenLastElement()
    {
        $document = new \DOMDocument();
        $document->loadHTML('<div>a</div><div>b</div><div>c</div>');

        /** @var $node \DOMElement */
        $node = $document->getElementsByTagName('div')->item(2);
        $event = $this->getMockEvent($node, 0);
        $this->transformer->transform($event);

        $this->assertContains('last', $node->getAttribute('class'));
    }

    /**
     * @test
     */
    public function testAddsLastClassWhenLastElementExcludingTest()
    {
        $document = new \DOMDocument();
        $document->loadHTML('<div>a</div><div>b</div><div>c</div>This is a text node');

        /** @var $node \DOMElement */
        $node = $document->getElementsByTagName('div')->item(2);
        $event = $this->getMockEvent($node, 0);
        $this->transformer->transform($event);

        $this->assertContains('last', $node->getAttribute('class'));
    }

    /**
     * @test
     */
    public function testThrowsExceptionWhenNotElementNode()
    {
        $document = new \DOMDocument();
        $document->loadHTML('<div>a</div><div>b</div><div>c</div>This is a text node');

        $this->setExpectedException('\RuntimeException', 'AddPositionClassTransformer can only process element nodes');

        /** @var $node \DOMElement */
        $textNode = $document->getElementsByTagName('div')->item(2)->nextSibling;
        $event = $this->getMockEvent($textNode, 0);
        $this->transformer->transform($event);
    }

    /**
     * @param $node
     * @param $position
     * @return m\MockInterface
     */
    protected function getMockEvent($node, $position)
    {
        $mock = m::mock('Optimus\Event\TranscodeNodeEvent', array(
            'getNode' => $node,
            'getPosition' => $position
        ));

        return $mock;
    }
}