<?php

namespace Optimus\Constraint;

use Optimus\Exception\ConstraintException;

class DepthConstraint implements ConstraintInterface
{
    /**
     * @var null|integer
     */
    protected $minimum;

    /**
     * @var null|integer
     */
    protected $maximum;

    /**
     * @param null|integer $minimum
     * @param null|integer $maximum
     */
    function __construct($minimum = null, $maximum = null)
    {
        $this->minimum = $minimum;
        $this->maximum = $maximum;
    }

    /**
     * @param int|null $maximum
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;
    }

    /**
     * @param int|null $minimum
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
    }

    /**
     * @param \DOMElement $node
     * @throws \Optimus\Exception\ConstraintException
     * @return bool
     */
    public function constrain(\DOMElement $node)
    {
        if (!empty($this->minimum) && (!empty($this->maximum)) && ($this->minimum >= $this->maximum)) {
            throw new ConstraintException('If specifying the minimum and maximum depth, the minimum must be lower than the maximum');
        }

        $depth = $this->getDepth($node);

        if (!empty($this->minimum) && $this->minimum > $depth) {
            return true;
        }

        if (!empty($this->maximum) && $this->maximum < $depth) {
            return true;
        }

        return false;
    }

    /**
     * @param \DOMNode $node
     * @return int
     */
    protected function getDepth(\DOMNode $node)
    {
        $beef = array_filter(explode('/', $node->getNodePath()));
        return count($beef) - 1;
    }
}