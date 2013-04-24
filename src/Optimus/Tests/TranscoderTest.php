<?php

namespace Optimus\Tests;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\Transcoder;
use Symfony\Component\EventDispatcher\EventDispatcher;

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

    /**
     * @test
     */
    public function testChildListenerNotCalledWhenPropogationStopped()
    {
        $this->dispatcher->addListener('transcode.ul', function(TranscodeNodeEvent $event) use (&$count) {
           $event->stopPropagation();
        });

        $childListenerCalled = false;
        $this->dispatcher->addListener('transcode.li', function(TranscodeNodeEvent $event) use (&$childListenerCalled) {
            $childListenerCalled = true;
        });

        $this->transcoder
            ->setDocument($this->getDocument())
            ->transcode()
        ;

        $this->assertFalse($childListenerCalled);
    }

    /**
     * @test
     */
    public function testChildListenerNotCalledWhenNodeRemoved()
    {
        $this->dispatcher->addListener('transcode.ul', function(TranscodeNodeEvent $event) use (&$count) {
           $event->removeNode();
        });

        $childListenerCalled = false;
        $this->dispatcher->addListener('transcode.li', function(TranscodeNodeEvent $event) use (&$childListenerCalled) {
            $childListenerCalled = true;
        });

        $this->transcoder
            ->setDocument($this->getDocument())
            ->transcode()
        ;

        $this->assertFalse($childListenerCalled);
    }

    /**
     * @test
     */
    public function testGenericListenerNotCalledWhenPropogationStopped()
    {
        $this->dispatcher->addListener('transcode.ul', function(TranscodeNodeEvent $event) use (&$count) {
           $event->stopPropagation();
        });

        $ulGenericListenerCalled = false;
        $this->dispatcher->addListener('transcode.*', function(TranscodeNodeEvent $event) use (&$ulGenericListenerCalled) {
            if ($event->getNode()->nodeName === 'ul') {
                $ulGenericListenerCalled = true;
            }
        });

        $this->transcoder
            ->setDocument($this->getDocument())
            ->transcode()
        ;

        $this->assertFalse($ulGenericListenerCalled);
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

        $dom->loadHTML($html);

        return $dom;
    }
}