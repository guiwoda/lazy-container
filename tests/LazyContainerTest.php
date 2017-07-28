<?php
declare(strict_types=1);

namespace Woda\Container;

use Closure;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Woda\Container\Stubs\DecoratorInterface;
use Woda\Container\Stubs\FourDecorator;
use Woda\Container\Stubs\SomeConcrete;
use Woda\Container\Stubs\OneDecorator;
use Woda\Container\Stubs\SomethingWithoutDependencies;
use Woda\Container\Stubs\ThreeDecorator;
use Woda\Container\Stubs\TwoDecorator;

class LazyContainerTest extends TestCase
{
    /**
     * @var LazyContainer
     */
    private $lazyContainer;

    protected function setUp()
    {
        $this->lazyContainer = new LazyContainer(new Container);
    }

    public function test it can build a lazy closure with di(): void
    {
        $result = $this->lazyContainer->closure(function (SomeConcrete $foo) {
            return $foo;
        });

        $this->assertInstanceOf(Closure::class, $result);
        $this->assertInstanceOf(SomeConcrete::class, $result());
    }

    public function test it can build a lazy static method call with di(): void
    {
        $fn = $this->lazyContainer->closure([SomeConcrete::class, 'bar']);

        $result = $fn();
        $this->assertInstanceOf(SomeConcrete::class, $result[0]);
        $this->assertInstanceOf(SomeConcrete::class, $result[1]);
    }

    public function test it can build a lazy method call with di(): void
    {
        $fn = $this->lazyContainer->closure(SomeConcrete::class.'@baz');

        $result = $fn();
        $this->assertInstanceOf(SomethingWithoutDependencies::class, $result);
    }

    public function test it can build horizontal decorator chains(): void
    {
        $fn = $this->lazyContainer->decorate(
            DecoratorInterface::class,
            SomeConcrete::class,
            OneDecorator::class,
            TwoDecorator::class,
            ThreeDecorator::class,
            FourDecorator::class
        );

        /** @var DecoratorInterface $result */
        $result = $fn();

        $this->assertInstanceOf(DecoratorInterface::class, $result);
        $this->assertEquals('12345', $result->talk());
    }

    public function test it can build commands that receive non di arguments(): void
    {
        $command = $this->lazyContainer->command(function ($args, SomeConcrete $dependency) {
            return [
                $args[0],
                $dependency
            ];
        });

        $result = $command('1234');

        $this->assertEquals('1234', $result[0]);
        $this->assertInstanceOf(SomeConcrete::class, $result[1]);
    }
}
