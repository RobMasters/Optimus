<?php

namespace Optimus\Constraint;

interface ConstraintInterface
{
    /**
     * @param \DOMElement $node
     * @return bool
     */
    public function constrain(\DOMElement $node);
}