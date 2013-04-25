<?php

namespace Optimus\Rule;

use Optimus\Event\TranscodeNodeEvent;
use Optimus\Constraint\ConstraintInterface;

interface RuleInterface
{
    /**
     * @param TranscodeNodeEvent $event
     * @return mixed
     */
    public function handle(TranscodeNodeEvent $event);

    /**
     * @return array|ConstraintInterface[]
     */
    public function getConstraints();
}