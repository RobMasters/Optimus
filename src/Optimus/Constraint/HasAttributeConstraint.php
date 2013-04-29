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
    public function constrain(\DOMElement $node)
    {
        if (!$this->getSubject($node)->hasAttribute($this->attribute)) {
            return false;
        }

        $value = $this->getSubject($node)->getAttribute($this->attribute);

        return (!is_null($this->value)) ? $this->checkValue($value) : false;
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