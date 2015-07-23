<?php

namespace Optimus;

use Optimus\Adapter\AdapterInterface;
use Optimus\Event\TranscodeElementEvent;
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
     * @var Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @param \Optimus\EventDispatcher $dispatcher
     * @param \Optimus\Adapter\AdapterInterface $adapter
     */
    function __construct(EventDispatcher $dispatcher, AdapterInterface $adapter = null)
    {
        $this->dispatcher = $dispatcher;
        $this->adapter = $adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return \DOMDocument
     */
    public function transcode()
    {
        $dom = $this->adapter->getDocument();
        $this->transcodeList($dom->childNodes);

        return $dom;
    }

    /**
     * @param \DOMNodeList $list
     * @param null $parentEvent
     */
    protected function transcodeList(\DOMNodeList $list, $parentEvent = null)
    {
        $i = 0;
        while ($node = $list->item($i)) {
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

            $i++;
        }
    }

    /**
     * @param TranscodeTextEvent $event
     */
    protected function transcodeTextNode(TranscodeTextEvent $event)
    {
        $this->dispatcher->dispatch('text', $event);
    }

    /**
     * @param TranscodeElementEvent $event
     */
    protected function transcodeElementNode(TranscodeElementEvent $event)
    {
        $node = $event->getNode();
        $this->dispatcher->dispatch($node->nodeName, $event);

        if ($event->isNodeRemoved()) {
            $node->parentNode->removeChild($node);
        }

        if (!$event->isPropagationStopped()) {
            $this->dispatcher->dispatch('*', $event);

            if ($event->isNodeRemoved()) {
                $node->parentNode->removeChild($node);
            }

            if (!$event->isPropagationStopped() && $children = $node->childNodes) {
                $this->transcodeList($children, $event);
            }
        }
    }
}