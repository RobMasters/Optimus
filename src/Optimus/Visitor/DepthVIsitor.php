<?php

namespace Optimus\Visitor;

use Optimus\Event\TranscodeNodeEvent;

class DepthVisitor extends NodeVisitor
{
    /**
     * @var int
     */
    protected $depth;

    function __construct()
    {
        $this->depth = 0;
    }

    /**
     * @param TranscodeNodeEvent $nodeEvent
     */
    public function visitNode(TranscodeNodeEvent $nodeEvent)
    {
        $this->depth++;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }
}