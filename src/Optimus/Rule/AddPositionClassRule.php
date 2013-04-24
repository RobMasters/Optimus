<?php

namespace Optimus\Rule;

use Optimus\Event\TranscodeNodeEvent;

class AddPositionClassRule extends BaseRule
{
    public function handle(TranscodeNodeEvent $event)
    {
        $node = $event->getNode();

        if (!$node instanceof \DOMElement) {
            throw new \RuntimeException('AddPositionClassRule can only process element nodes');
        }

        if ($event->getPosition() === 0) {
            $node->setAttribute('class', 'first');
        }

        if ($this->isLastSiblingElement($node)) {
            $node->setAttribute('class', 'last');
        }
    }
}