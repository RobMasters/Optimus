<?php

namespace Optimus\Constraint;

use Optimus\Exception\InvalidArgumentException;

class HasClassConstraint extends HasAttributeConstraint
{
    /**
     * @param array|string $values
     * @throws \Optimus\Exception\InvalidArgumentException
     */
    function __construct($values)
    {
        if (is_string($values)) {
            $values = preg_split('/\s/', $values);
        } elseif (!is_array($values)) {
            throw new InvalidArgumentException('HasClassConstraint only accepts a single array or string argument');
        }

        if (count($values) !== count(array_filter($values))) {
            throw new InvalidArgumentException('Class name cannot be empty');
        }

        parent::__construct('class', $values);
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
            if (strpos($value, $className) === false) {
                return false;
            }
        }

        return true;
    }
}