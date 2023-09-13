<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use BadMethodCallException;
use Saloon\Traits\Macroable;

beforeEach(function () {
    $this->macroableClass = new class() {
        private $privateVariable = 'privateValue';

        use Macroable;

        private static function getPrivateStatic()
        {
            return 'privateStaticValue';
        }
    };
});

test('a new macro can be registered and called', function () {
    $this->macroableClass::macro('newMethod', function () {
        return 'newValue';
    });

    $this->assertEquals('newValue', $this->macroableClass->newMethod());
});

test('a new macro can be registered and called statically', function () {
    $this->macroableClass::macro('newMethod', function () {
        return 'newValue';
    });

    $this->assertEquals('newValue', $this->macroableClass::newMethod());
});

test('a class can be registered as a new macro and be invoked', function () {
    $this->macroableClass::macro('newMethod', new class() {
        public function __invoke()
        {
            return 'newValue';
        }
    });

    $this->assertEquals('newValue', $this->macroableClass->newMethod());
    $this->assertEquals('newValue', $this->macroableClass::newMethod());
});

test('it passes parameters correctly', function () {
    $this->macroableClass::macro('concatenate', function (...$strings) {
        return implode('-', $strings);
    });

    $this->assertEquals('one-two-three', $this->macroableClass->concatenate('one', 'two', 'three'));
});

test('registered methods are bound to the class', function () {
    $this->macroableClass::macro('newMethod', function () {
        return $this->privateVariable;
    });

    $this->assertEquals('privateValue', $this->macroableClass->newMethod());
});

test('it can work on static methods', function () {
    $this->macroableClass::macro('testStatic', function () {
        return $this::getPrivateStatic();
    });

    $this->assertEquals('privateStaticValue', $this->macroableClass->testStatic());
});

test('it can mixin all public methods from another class', function () {
    $mixinClass = new class() {
        private function secretMixinMethod()
        {
            return 'secret';
        }

        public function mixinMethodA()
        {
            return function ($value) {
                return $this->mixinMethodB($value);
            };
        }

        public function mixinMethodB()
        {
            return function ($value) {
                return $this->privateVariable.'-'.$value;
            };
        }
    };

    $this->macroableClass::mixin($mixinClass);

    $this->assertEquals('privateValue-test', $this->macroableClass->mixinMethodA('test'));
});

test('it will throw an exception if a method does not exist', function () {
    $this->expectException(BadMethodCallException::class);

    $this->macroableClass->nonExistingMethod();
});

test('it will throw an exception if a static method does not exist', function () {
    $this->expectException(BadMethodCallException::class);

    $this->macroableClass::nonExistingMethod();
});
