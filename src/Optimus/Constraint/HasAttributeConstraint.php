<?php

namespace Optimus\Constraint;

class HasAttributeConstraint implements ConstraintInterface
{
    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var null|mixed
     */
    protected $value;

    function __construct($attribute, $value = null)
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * @param \DOMElement $node
     * @return bool
     */
    public function check(\DOMElement $node)
    {
        if (!$this->getSubject($node)->hasAttribute($this->attribute)) {
            return false;
        }

        if (is_null($this->value)) {
            // Only checking the attribute exists, which it does
            return true;
        }

        return $this->checkValue($this->getSubject($node)->getAttribute($this->attribute));
    }

    /**
     * @param $value
     * @return bool
     */
    protected function checkValue($value)
    {
        return $value === $this->value;
    }

    /**
     * @param \DOMElement $node
     * @return \DOMElement
     */
    protected function getSubject(\DOMElement $node)
    {
        return $node;
    }
}