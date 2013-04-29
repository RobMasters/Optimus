<?php

namespace Optimus\Transformer;

use Optimus\Event\TranscodeNodeEvent;

class AddPositionClassTransformer extends BaseTransformer
{
    public function transform(TranscodeNodeEvent $event)
    {
        $node = $event->getNode();

        if (!$node instanceof \DOMElement) {
            throw new \RuntimeException('AddPositionClassTransformer can only process element nodes');
        }

        if ($event->getPosition() === 0) {
            $node->setAttribute('class', 'first');
        }

        if ($this->isLastSiblingElement($node)) {
            $node->setAttribute('class', 'last');
        }
    }
}