<?php

namespace Realodix\Assert\Tests;

use ArrayObject;
use LogicException;
use PHPUnit\Framework\TestCase;
use Realodix\Assert\Assert;
use Realodix\Assert\ParameterAssertionException;
use RuntimeException;
use stdClass;

/**
 * @covers \Realodix\Assert\Assert
 */
class AssertTest extends TestCase
{
    public function testParameterPass()
    {
        Assert::parameter(1 >= 0, 'foo', 'must be greater than 0');
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Realodix\Assert\ParameterAssertionException
     */
    public function testParameterFail()
    {
        try {
            Assert::parameter(false, 'test', 'testing');
            $this->fail('Expected ParameterAssertionException');
        } catch (ParameterAssertionException $ex) {
            $this->assertSame('test', $ex->getParameterName());
        }
    }

    public function validParameterTypeProvider()
    {
        $staticFunction = static function () {
        };

        return [
            'simple' => ['string', 'hello'],

            'boolean (true)'  => ['bool', true],
            'boolean (false)' => ['bool', false],
            'true'            => ['true', true],
            'false'           => ['false', false],

            'integer' => ['int', 1],
            'double'  => ['float', 1.0],

            'object'      => ['object', new stdClass],
            'class'       => ['RuntimeException', new RuntimeException],
            'subclass'    => ['Exception', new RuntimeException],
            'stdClass'    => ['stdClass', new stdClass],
            'multi'       => [['string', 'array', 'Closure'], $staticFunction],
            'multi (old)' => ['string|array|Closure', $staticFunction],
            'null'        => [['integer', 'null'], null],

            'callable'            => [['null', 'callable'], 'time'],
            'static callable'     => ['callable', 'Realodix\Assert\Assert::isType'],
            'callable array'      => ['callable', ['Realodix\Assert\Assert', 'isType']],
            'callable $this'      => ['callable', [$this, 'validParameterTypeProvider']],
            'Closure is callable' => ['callable', $staticFunction],

            'Traversable' => ['traversable', new ArrayObject],
        ];
    }

    /**
     * @dataProvider validParameterTypeProvider
     *
     * @param mixed $type
     * @param mixed $value
     */
    public function testParameterTypePass($type, $value)
    {
        Assert::isType($type, $value, 'test');
        $this->addToAssertionCount(1);
    }

    public function invalidParameterTypeProvider()
    {
        return [
            // 'bool shortcut is not accepted'  => ['bool', true],
            // 'int shortcut is not accepted'   => ['int', 1],
            // 'float alias is not accepted'    => ['float', 1.0],
            'callback alias is not accepted' => ['callback', 'time'],

            'simple'                    => ['string', 5],
            'integer is not boolean'    => ['boolean', 1],
            'string is not boolean'     => ['boolean', '0'],
            'boolean is not integer'    => ['integer', true],
            'false is not true'         => ['true', false],
            'true is not false'         => ['false', true],
            'string is not integer'     => ['integer', '0'],
            'double is not integer'     => ['integer', 1.0],
            'integer is not double'     => ['double', 1],
            'class'                     => ['RuntimeException', new LogicException],
            'stdClass is no superclass' => ['stdClass', new LogicException],
            'multi'                     => ['string|integer|Closure', []],
            'null'                      => ['integer|string', null],

            'callable'               => ['null|callable', []],
            'callable is no Closure' => ['Closure', 'time'],
            'object is not callable' => ['callable', new stdClass],

            'object is not Traversable'   => ['traversable', new stdClass],
            'Traversable is not Iterator' => ['Iterator', new ArrayObject],
        ];
    }

    /**
     * @dataProvider invalidParameterTypeProvider
     *
     * @param mixed $type
     * @param mixed $value
     */
    public function testParameterTypeFail($type, $value)
    {
        $this->expectException(\InvalidArgumentException::class);
        Assert::isType($type, $value, 'test');
    }

    /**
     * @covers \Realodix\Assert\ParameterAssertionException
     */
    public function testParameterTypeCatch()
    {
        $this->expectException(\InvalidArgumentException::class);
        Assert::isType('string', 17, 'test');
    }
}
