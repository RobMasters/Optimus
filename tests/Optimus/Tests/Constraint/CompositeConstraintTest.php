<?php

namespace Optimus\Tests\Constraint;

use Optimus\Constraint\CompositeConstraint;
use \Mockery as m;

class CompositeConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeConstraint
     */
    protected $constraint;

    protected function setUp()
    {
        $this->constraint = new CompositeConstraint();
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * @return array
     */
    public function modeAndConstraintValuesProvider()
    {
        return array(
            // ALL mode returns true when all constraints return true
            array(
                CompositeConstraint::MODE_ALL,
                array(true, true, true),
                true
            ),

            // ALL mode returns false when all constraints return false
            array(
                CompositeConstraint::MODE_ALL,
                array(false, false, false),
                false
            ),

            // ALL mode returns false when mix of true and false
            array(
                CompositeConstraint::MODE_ALL,
                array(true, false, true),
                false
            ),

            // ANY mode returns true when all constraints return true
            array(
                CompositeConstraint::MODE_ANY,
                array(true, true, true),
                true
            ),

            // ANY mode returns false when all constraints return false
            array(
                CompositeConstraint::MODE_ANY,
                array(false, false, false),
                false
            ),

            // ANY mode returns true when mix of true and false
            array(
                CompositeConstraint::MODE_ANY,
                array(true, false, true),
                true
            ),

            // NONE mode returns false when all constraints return true
            array(
                CompositeConstraint::MODE_NONE,
                array(true, true, true),
                false
            ),

            // NONE mode returns true when all constraints return false
            array(
                CompositeConstraint::MODE_NONE,
                array(false, false, false),
                true
            ),

            // NONE mode returns false when mix of true and false
            array(
                CompositeConstraint::MODE_NONE,
                array(true, false, true),
                false
            ),
        );
    }

    /**
     * @test
     * @dataProvider modeAndConstraintValuesProvider
     */
    public function testCheck_returnsCorrectOutput_usingModeAndConstraintProvider($mode, $constraintResults, $expected)
    {
        $this->constraint->setMode($mode);

        foreach ($constraintResults as $constraintResult) {
            $this->constraint->addConstraint($this->getMockConstraint($constraintResult));
        }

        $this->assertEquals($expected, $this->constraint->check(m::mock('\DOMElement')));
    }

    /**
     * @test
     */
    public function testCheck_throwsException_whenNoConstraintsProvided()
    {
        $this->setExpectedException(
            'Optimus\Exception\ConstraintException',
            'CompositeConstraint must contain at least one constraint'
        );

        $this->constraint->check(m::mock('\DOMElement'));
    }

    /**
     * @return array
     */
    public function invalidModeProvider()
    {
        return array(
            array('string'),
            array(3),
            array(-1)
        );
    }

    /**
     * @test
     * @dataProvider invalidModeProvider
     * @param $invalidMode
     */
    public function testSetMode_throwsException_whenGivenInvalidMode($invalidMode)
    {
        $this->setExpectedException(
            '\Optimus\Exception\InvalidArgumentException',
            sprintf('Invalid CompositeConstraint mode: `%s`', $invalidMode)
        );

        $this->constraint->setMode($invalidMode);
    }

    /**
     * @param $returnValue
     * @return m\MockInterface
     */
    protected function getMockConstraint($returnValue)
    {
        return m::mock('Optimus\Constraint\ConstraintInterface', array(
            'check' => $returnValue
        ));
    }
}