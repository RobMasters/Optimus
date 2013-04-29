<?php

namespace Optimus;

use Optimus\Constraint\HasAttributeConstraint;
use Optimus\Constraint\HasClassConstraint;
use Optimus\Event\TranscodeElementEvent;
use Optimus\Exception\InvalidArgumentException;
use Optimus\Transformer\TransformerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseDispatcher;

class EventDispatcher extends BaseDispatcher
{
    /**
     * @param array|string $nodeNames
     * @param TransformerInterface $transformer
     * @param int $priority
     * @throws Exception\InvalidArgumentException
     */
    public function addTransformer($nodeNames, TransformerInterface $transformer, $priority = 0)
    {
        $nodeNames = (array) $nodeNames;
        foreach ($nodeNames as $nodeName) {
            if (!preg_match('/^[a-z]$/i', $nodeName)) {
                if (preg_match('/^([a-z]*)(?:#([a-z0-9_-]+))?((?:\.[a-z0-9_-]+)*)$/', $nodeName, $matches)) {
                    $nodeName = $matches[1] ?: '*';
                    $id = $matches[2];
                    if (!empty($id)) {
                        $transformer->addConstraint(new HasAttributeConstraint('id', $id));
                    }
                    $classes = array_filter(explode('.', trim($matches[3], '.')));
                    if (!empty($classes)) {
                        $transformer->addConstraint(new HasClassConstraint($classes));
                    }
                } else {
                    throw new InvalidArgumentException(sprintf('Invalid node selector: `%s`', $nodeName));
                }
            }

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
            if ($listener instanceof TransformerInterface && $event instanceof TranscodeElementEvent) {
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