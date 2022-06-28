<?php

namespace Realodix\Assert\Tests;

use ArrayObject;
use LogicException;
use RuntimeException;
use stdClass;

trait AssertTestProvider
{
    public function validIsTypeProvider()
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

            'callable'                     => [['null', 'callable'], 'time'],
            'static callable'              => ['callable', 'Realodix\Assert\Assert::isType'],
            'callable array'               => ['callable', ['Realodix\Assert\Assert', 'isType']],
            'callable $this'               => ['callable', [$this, 'validIsTypeProvider']],
            'Closure is callable'          => ['callable', $staticFunction],
            'callable_callback'            => [['null', 'callback'], 'time'],
            'static callable_callback'     => ['callback', 'Realodix\Assert\Assert::isType'],
            'callable_callback array'      => ['callback', ['Realodix\Assert\Assert', 'isType']],
            'callable_callback $this'      => ['callback', [$this, 'validIsTypeProvider']],
            'Closure is callable_callback' => ['callback', $staticFunction],

            'Traversable' => ['traversable', new ArrayObject],
        ];
    }

    public function invalidIsTypeProvider()
    {
        return [
            // 'bool shortcut is not accepted'  => ['bool', true],
            // 'int shortcut is not accepted'   => ['int', 1],
            // 'float alias is not accepted'    => ['float', 1.0],
            // 'callback alias is not accepted' => ['callback', 'time'],

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
}
