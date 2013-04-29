<?php

namespace Optimus;

use Optimus\Event\TranscodeElementEvent;
use Optimus\Event\TranscodeNodeEvent;
use Optimus\Event\TranscodeTextEvent;
use Optimus\EventDispatcher;

class Transcoder
{
    /**
     *
     * @var \Optimus\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @param \Optimus\EventDispatcher $dispatcher
     */
    function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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

            switch ($node->nodeType) {
                case XML_TEXT_NODE:
                    $event = new TranscodeTextEvent($node, $parentEvent, $i);
                    $this->transcodeTextNode($event);
                    break;

                case XML_ELEMENT_NODE:
                    $event = new TranscodeElementEvent($node, $parentEvent, $i);
                    $this->transcodeElementNode($event);
                    break;

                default:
                    // ?
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
        $this->dispatcher->dispatch('text', $event);
    }

    /**
     * @param TranscodeNodeEvent $event
     */
    protected function transcodeElementNode(TranscodeNodeEvent $event)
    {
        $node = $event->getNode();
        $this->dispatcher->dispatch($node->nodeName, $event);

        if ($event->isNodeRemoved()) {
            $node->parentNode->removeChild($node);
        }

        if (!$event->isPropagationStopped()) {
            $this->dispatcher->dispatch('*', $event);

            if (!$event->isPropagationStopped() && $children = $node->childNodes) {
                $this->transcodeList($children, $event);
            }
        }
    }
}