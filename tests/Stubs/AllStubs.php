<?php
namespace Woda\Container\Stubs;

class SomethingWithoutDependencies {}

interface DecoratorInterface
{
    public function talk(): string;
}

class SomeConcrete implements DecoratorInterface
{
    public static function bar(SomeConcrete $foo, SomeConcrete $other) {
        return [$foo, $other];
    }

    public function baz(SomethingWithoutDependencies $fooBar) {
        return $fooBar;
    }

    public function talk(): string
    {
        return '5';
    }
}

abstract class AbstractDecorator implements DecoratorInterface
{
    /** @var DecoratorInterface */
    public $decorated;

    public function __construct(DecoratorInterface $decorated)
    {
        $this->decorated = $decorated;
    }
}
class OneDecorator extends AbstractDecorator
{
    public function talk(): string
    {
        return '1'.$this->decorated->talk();
    }
}
class TwoDecorator extends AbstractDecorator
{
    public function talk(): string
    {
        return '2'.$this->decorated->talk();
    }
}
class ThreeDecorator extends AbstractDecorator
{
    public function talk(): string
    {
        return '3'.$this->decorated->talk();
    }
}
class FourDecorator extends AbstractDecorator
{
    public function talk(): string
    {
        return '4'.$this->decorated->talk();
    }
}
