<?php

namespace Optimus;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\Transformer\TransformerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseDispatcher;

class EventDispatcher extends BaseDispatcher
{
    /**
     * @param array|string $nodeNames
     * @param TransformerInterface $transformer
     * @param int $priority
     */
    public function addTransformer($nodeNames, TransformerInterface $transformer, $priority = 0)
    {
        $nodeNames = (array) $nodeNames;
        foreach ($nodeNames as $nodeName) {
            // TODO - evaluate event names and add constraints dynamically
            // e.g. for "div.container" a relevant HasClass constraint should be added

            parent::addListener($nodeName, array($transformer, 'transform'), $priority);
        }
    }

    /**
     * @param $listeners
     * @param string $eventName
     * @param Event $event
     */
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            if ($listener instanceof TransformerInterface && $event instanceof TranscodeNodeEvent) {
                $constraints = $listener->getConstraints();
                foreach ($constraints as $constraint) {
                    if ($constraint->constrain($event->getNode())) {
                        continue(2);
                    }
                }
            }
            call_user_func($listener, $event);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
    }
}