<?php

namespace Optimus\Event;

use Symfony\Component\EventDispatcher\Event;

class TranscodeNodeEvent extends Event
{
    /**
     * @var \DOMNode
     */
    protected $node;

    /**
     * @var TranscodeNodeEvent
     */
    protected $parent;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var bool
     */
    protected $nodeRemoved = false;

    /**
     * @param \DOMNode $node
     * @param TranscodeNodeEvent $parent
     * @param int $position
     */
    function __construct(\DOMNode $node, TranscodeNodeEvent $parent = null, $position = 0)
    {
        $this->node = $node;
        $this->parent = $parent;
        $this->position = $position;
    }

    /**
     * @return \DOMNode
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return TranscodeNodeEvent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        $depth = 0;
        $parent = $this->parent;
        while (!is_null($parent)) {
            $parent = $parent->getParent();
            $depth++;
        }

        return $depth;
    }

    /**
     * Mark the node for removal from the DOM and prevent any further propagation
     */
    public function removeNode()
    {
        $this->nodeRemoved = true;

        $this->stopPropagation();
    }

    /**
     * @return bool
     */
    public function isNodeRemoved()
    {
        return $this->nodeRemoved;
    }
}