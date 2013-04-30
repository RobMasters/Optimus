<?php

namespace Optimus\Constraint;

interface ConstraintInterface
{
    /**
     * Check if the given node satisfies the constraint. Returns true if it does.
     *
     * @param \DOMElement $node
     * @return bool
     */
    public function check(\DOMElement $node);
}