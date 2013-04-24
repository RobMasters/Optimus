<?php

namespace Optimus\Rule;

use Optimus\Event\TranscodeNodeEvent;

interface RuleInterface
{
    /**
     * @param TranscodeNodeEvent $event
     * @return mixed
     */
    public function handle(TranscodeNodeEvent $event);
}