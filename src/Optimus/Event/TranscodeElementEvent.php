<?php

namespace Optimus\Event;

use Optimus\Exception\InvalidArgumentException;

class TranscodeElementEvent extends TranscodeNodeEvent
{
    /**
     * @param \DOMElement $node
     * @param TranscodeNodeEvent $parent
     * @param int $position
     * @throws \Optimus\Exception\InvalidArgumentException
     */
    function __construct(\DOMElement $node, TranscodeNodeEvent $parent = null, $position = 0)
    {
        parent::__construct($node, $parent, $position);
    }

    /**
     * @return \DOMElement
     */
    public function getNode()
    {
        return parent::getNode();
    }
}