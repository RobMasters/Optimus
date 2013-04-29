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
     * @param ConstraintInterface $constraint
     * @return TransformerInterface
     */
    public function addConstraint(ConstraintInterface $constraint);

    /**
     * @return array|ConstraintInterface[]
     */
    public function getConstraints();
}