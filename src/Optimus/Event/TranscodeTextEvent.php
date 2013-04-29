<?php

namespace Optimus\Event;

use Optimus\Exception\InvalidArgumentException;

class TranscodeTextEvent extends TranscodeNodeEvent
{
    /**
     * @param \DOMText $node
     * @param TranscodeNodeEvent $parent
     * @param int $position
     * @throws \Optimus\Exception\InvalidArgumentException
     */
    function __construct(\DOMText $node, TranscodeNodeEvent $parent = null, $position = 0)
    {
        parent::__construct($node, $parent, $position);
    }

    /**
     * @return \DOMText
     */
    public function getNode()
    {
        return parent::getNode();
    }
}