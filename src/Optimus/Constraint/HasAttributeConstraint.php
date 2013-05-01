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

    /**
     * @var null|string
     */
    protected $pattern;

    /**
     * @param $attribute
     * @param null $value
     */
    function __construct($attribute, $value = null)
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * @param string $pattern
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
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

        if (is_null($this->value) && is_null($this->pattern)) {
            // Only checking the attribute exists, which it must do at this point
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
        if (!empty($this->pattern)) {
            return (bool) preg_match($this->pattern, $value);
        }

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