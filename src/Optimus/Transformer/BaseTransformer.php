<?php

namespace Optimus\Transformer;

use Optimus\Constraint\ConstraintInterface;

abstract class BaseTransformer implements TransformerInterface
{
    /**
     * @var array|ConstraintInterface[]
     */
    protected $constraints = array();

    /**
     * @param \DOMElement $node
     * @return bool
     */
    public function isLastSiblingElement(\DOMElement $node)
    {
        $sibling = $node->nextSibling;
        while ($sibling) {
            switch ($sibling->nodeType) {
                case XML_ELEMENT_NODE:
                    return false;

                default:
                    $sibling = $sibling->nextSibling;
            }
        }

        return true;
    }

    /**
     * @param ConstraintInterface $constraint
     * @return $this|\Optimus\Transformer\TransformerInterface
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @return array|ConstraintInterface[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }
}