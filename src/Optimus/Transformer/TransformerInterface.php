<?php

namespace Optimus\Transformer;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\Constraint\ConstraintInterface;

interface TransformerInterface
{
    /**
     * @param TranscodeNodeEvent $event
     * @return mixed
     */
    public function transform(TranscodeNodeEvent $event);

    /**
     * @return array|ConstraintInterface[]
     */
    public function getConstraints();
}