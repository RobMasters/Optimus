<?php

namespace Optimus\Constraint;

use Optimus\Exception\ConstraintException;
use Optimus\Exception\InvalidArgumentException;

class CompositeConstraint implements ConstraintInterface
{
    const MODE_ALL = 0;
    const MODE_ANY = 1;
    const MODE_NONE = 2;

    /**
     * @var array|ConstraintInterface[]
     */
    protected $constraints;

    /**
     * @var null
     */
    protected $mode;

    /**
     * @param array $constraints
     * @param int $mode
     */
    function __construct(array $constraints = array(), $mode = self::MODE_ANY)
    {
        $this->constraints = $constraints;
        $this->setMode($mode);
    }

    /**
     * Check if the given node satisfies the constraint. Returns true if it does.
     *
     * @param \DOMElement $node
     * @throws \Optimus\Exception\ConstraintException
     * @return bool
     */
    public function check(\DOMElement $node)
    {
        if (empty($this->constraints)) {
            throw new ConstraintException('CompositeConstraint must contain at least one constraint');
        }

        foreach ($this->constraints as $constraint) {
            $result = $constraint->check($node);

            if (!$result && $this->mode === self::MODE_ALL) {
                return false;
            }

            if ($result && $this->mode === self::MODE_ANY) {
                return true;
            }

            if ($result && $this->mode === self::MODE_NONE) {
                return false;
            }
        }

        return ($this->mode !== self::MODE_ANY);
    }

    /**
     * @param ConstraintInterface $constraint
     * @return $this
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param $mode
     * @return $this
     * @throws \Optimus\Exception\InvalidArgumentException
     */
    public function setMode($mode)
    {
        if (!in_array($mode, range(0, 2), true)) {
            throw new InvalidArgumentException(sprintf('Invalid CompositeConstraint mode: `%s`', $mode));
        }

        $this->mode = $mode;

        return $this;
    }
}