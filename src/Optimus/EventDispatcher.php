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
     * @param array|string $selectors
     * @param TransformerInterface $transformer
     * @param int $priority
     * @throws Exception\InvalidArgumentException
     */
    public function addTransformer($selectors, TransformerInterface $transformer, $priority = 0)
    {
        $selectors = (array) $selectors;
        foreach ($selectors as $selector) {
            $transformerClone = null;
            if (!preg_match('/^[a-z]$/i', $selector)) {
                if (!preg_match('/^([a-z]*)(?:#([a-z0-9_-]+))?((?:\.[a-z0-9_-]+)*)$/', $selector, $matches)) {
                    throw new InvalidArgumentException(sprintf('Invalid node selector: `%s`', $selector));
                }
                // Prevent adding constraints that would affect other selectors
                $transformerClone = clone $transformer;

                $selector = $matches[1] ?: '*';
                $id = $matches[2];
                if (!empty($id)) {
                    $transformerClone->addConstraint(new HasAttributeConstraint('id', $id));
                }
                $classes = array_filter(explode('.', trim($matches[3], '.')));
                if (!empty($classes)) {
                    $transformerClone->addConstraint(new HasClassConstraint($classes));
                }
            }

            parent::addListener($selector, array($transformerClone ?: $transformer, 'transform'), $priority);
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
            if (is_array($listener) && $listener[0] instanceof TransformerInterface && $event instanceof TranscodeElementEvent) {
                $constraints = $listener[0]->getConstraints();
                foreach ($constraints as $constraint) {
                    if (!$constraint->check($event->getNode())) {
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