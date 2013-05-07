<?php

namespace Optimus\Tests;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\Transcoder;
use Optimus\EventDispatcher;
use \Mockery as m;

class TranscoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var Transcoder
     */
    protected $transcoder;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->transcoder = new Transcoder($this->dispatcher);
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function testChildListenerNotCalledWhenPropogationStopped()
    {
        $this->dispatcher->addListener('ul', function(TranscodeNodeEvent $event) use (&$count) {
           $event->stopPropagation();
        });

        $childListenerCalled = false;
        $this->dispatcher->addListener('li', function() use (&$childListenerCalled) {
            $childListenerCalled = true;
        });

        $this->transcoder
            ->setAdapter($this->getMockAdapter())
            ->transcode()
        ;

        $this->assertFalse($childListenerCalled);
    }

    /**
     * @test
     */
    public function testChildListenerNotCalledWhenNodeRemoved()
    {
        $this->dispatcher->addListener('ul', function(TranscodeNodeEvent $event) use (&$count) {
           $event->removeNode();
        });

        $childListenerCalled = false;
        $this->dispatcher->addListener('li', function(TranscodeNodeEvent $event) use (&$childListenerCalled) {
            $childListenerCalled = true;
        });

        $this->transcoder
            ->setAdapter($this->getMockAdapter())
            ->transcode()
        ;

        $this->assertFalse($childListenerCalled);
    }

    /**
     * @test
     */
    public function testGenericListenerNotCalledWhenPropogationStopped()
    {
        $this->dispatcher->addListener('ul', function(TranscodeNodeEvent $event) use (&$count) {
           $event->stopPropagation();
        });

        $ulGenericListenerCalled = false;
        $this->dispatcher->addListener('*', function(TranscodeNodeEvent $event) use (&$ulGenericListenerCalled) {
            if ($event->getNode()->nodeName === 'ul') {
                $ulGenericListenerCalled = true;
            }
        });

        $this->transcoder
            ->setAdapter($this->getMockAdapter())
            ->transcode()
        ;

        $this->assertFalse($ulGenericListenerCalled);
    }

    /**
     * @return \DOMDocument
     */
    protected function getMockAdapter()
    {
        $html = <<<ENDHTML
<html>
    <head></head>
    <body>
        <div>
            <h1>Heading</h1>
            <ul>
                <li>First</li>
                <li>Second</li>
            </ul>
            <div>Some text <span>in a span</span></div>
        </div>
    </body>
</html>
ENDHTML;

        $document = new \DOMDocument();
        $document->loadHTML($html);

        return m::mock('Optimus\Adapter\AdapterInterface', array(
            'getDocument' => $document
        ));
    }
}