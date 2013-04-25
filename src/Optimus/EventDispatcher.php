<?php

namespace Optimus;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\Rule\RuleInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseDispatcher;

class EventDispatcher extends BaseDispatcher
{
    /**
     * @param $listeners
     * @param string $eventName
     * @param Event $event
     */
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            if ($listener instanceof RuleInterface && $event instanceof TranscodeNodeEvent) {
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