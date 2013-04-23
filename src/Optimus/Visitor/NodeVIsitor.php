<?php

namespace Optimus\Visitor;

use Optimus\Event\TranscodeNodeEvent;

abstract class NodeVisitor
{
    abstract public function visitNode(TranscodeNodeEvent $nodeEvent);
}