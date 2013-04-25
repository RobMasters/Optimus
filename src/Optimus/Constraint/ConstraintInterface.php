<?php

namespace Optimus\Constraint;

interface ConstraintInterface
{
    /**
     * @param \DOMNode $node
     * @return bool
     */
    public function constrain(\DOMNode $node);
}