<?php

namespace Optimus;

use Optimus\Event\TranscodeNodeEvent;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Transcoder
{
    /**
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $eventPrefix;

    /**
     * @var \DomDocument
     */
    protected $dom;

    /**
     * @param EventDispatcher $dispatcher
     * @param string $eventPrefix
     */
    function __construct(EventDispatcher $dispatcher, $eventPrefix = 'transcode')
    {
        $this->dispatcher = $dispatcher;
        $this->eventPrefix = $eventPrefix;
    }

    /**
     * @param \DOMDocument $dom
     * @return $this
     */
    public function setDocument(\DOMDocument $dom)
    {
        $this->dom = $dom;

        return $this;
    }

    public function transcode()
    {
        $this->transcodeList($this->dom->childNodes);

        return $this->dom;
    }

    /**
     * @param \DOMNodeList $list
     * @param null $parentEvent
     */
    protected function transcodeList(\DOMNodeList $list, $parentEvent = null)
    {
        $length = $list->length;
        for ($i = 0; $i < $length; $i++) {
            $node = $list->item($i);

            $event = new TranscodeNodeEvent($node, $parentEvent, $i);
            $this->dispatcher->dispatch($this->getEventName($node), $event);

            if ($event->isNodeRemoved()) {
                $node->parentNode->removeChild($node);
            }

            if ($children = $node->childNodes) {
                $this->transcodeList($children, $event);
            }
        }
    }

    /**
     * @param \DOMNode $node
     * @return string
     */
    private function getEventName(\DOMNode $node)
    {
        return sprintf('%s.%s',
            $this->eventPrefix,
            strtolower($node->nodeName)
        );
    }
}