<?php

namespace Optimus;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\EventDispatcher;

class Transcoder
{
    /**
     *
     * @var \Optimus\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $eventPrefix;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @param \Optimus\EventDispatcher $dispatcher
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

    /**
     * @return \DOMDocument
     */
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
        $i = 0;
        $node = $list->item(0);

        while ($node) {
            $node = $list->item($i);
            $event = new TranscodeNodeEvent($node, $parentEvent, $i);

            switch ($node->nodeType) {
                case XML_TEXT_NODE:
                    $this->transcodeTextNode($event);
                    break;

                default:
                    $this->transcodeElementNode($event);
            }

            $node = $node->nextSibling;
            $i++;
        }
    }

    /**
     * @param TranscodeNodeEvent $event
     */
    protected function transcodeTextNode(TranscodeNodeEvent $event)
    {
        $this->dispatcher->dispatch($this->getEventName('text'), $event);
    }

    /**
     * @param TranscodeNodeEvent $event
     */
    protected function transcodeElementNode(TranscodeNodeEvent $event)
    {
        $node = $event->getNode();
        $this->dispatcher->dispatch($this->getEventName($node), $event);

        if ($event->isNodeRemoved()) {
            $node->parentNode->removeChild($node);
        }

        if (!$event->isPropagationStopped()) {
            $this->dispatcher->dispatch($this->getEventName('*'), $event);

            if (!$event->isPropagationStopped() && $children = $node->childNodes) {
                $this->transcodeList($children, $event);
            }
        }
    }

    /**
     * @param \DOMNode|string $subject
     * @return string
     */
    private function getEventName($subject)
    {
        $name = ($subject instanceof \DOMNode) ? strtolower($subject->nodeName) : $subject;

        return sprintf('%s.%s',
            $this->eventPrefix,
            $name
        );
    }
}