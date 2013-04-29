<?php

namespace Optimus\Constraint;

class HasClassConstraint extends HasAttributeConstraint
{
    /**
     * @param array|string $values
     */
    function __construct($values)
    {
        parent::__construct('class', (array) $values);
    }

    /**
     * Check that all specified class names are within the class attribute
     *
     * @param $value
     * @return bool
     */
    protected function checkValue($value)
    {
        foreach ($this->value as $className) {
            if (strpos($className, $value) === false) {
                return false;
            }
        }

        return true;
    }
}