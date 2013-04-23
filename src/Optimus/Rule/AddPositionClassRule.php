<?php

namespace Optimus\Rule;

use Optimus\Event\TranscodeNodeEvent;

class AddPositionClassRule
{
    public function handle(TranscodeNodeEvent $event)
    {
        $node = $event->getNode();

        if (!$node instanceof \DOMElement) {
            throw new \RuntimeException('');
        }

        if ($event->getPosition() === 0) {
            $node->setAttribute('class', 'first');
        }

        if (!$node->nextSibling) {
            $node->setAttribute('class', 'last');
        }
    }
}