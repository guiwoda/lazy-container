<?php
declare(strict_types=1);

namespace Woda\Container;

use Illuminate\Contracts\Container\Container;

class LazyContainer
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a zero-param closure that will resolve the given callable through
     * the DI container.
     *
     * @param callable|string $fn Supports native callable and Laravel's Class{@}method syntax.
     * @return callable
     */
    public function closure($fn): callable
    {
        return function () use ($fn) {
            return $this->container->call($fn);
        };
    }

    /**
     * Decorate a given abstract / concrete pair with a list of decorators.
     * The resulting function will be a zero-param factory function that will build
     * the decorator chain. This chain will also be bound to Laravel's container.
     *
     * <code>
     * // Given an abstract, concrete and two decorators
     * $fn = $lazy->decorate('LoggerInterface', 'Logger', 'BufferingLogger', 'EmailLogger');
     *
     * $logger = $fn(); // new BufferingLogger(new EmailLogger(new Logger)));
     *
     * $logger = app('LoggerInterface'); // Also returns the decorated chain
     * </code>
     * @param string $abstract
     * @param string $concrete
     * @param string[] ...$decorators
     *
     * @return callable
     */
    public function decorate($abstract, $concrete, ...$decorators): callable
    {
        return $this->buildDecoratorChain($abstract, $concrete, $decorators);
    }

    /**
     * Builds a contextual dependency chain recursively, so that each element of the chain
     * depends on the next one, up until the last one.
     *
     * @param  string $abstract
     * @param  string $concrete
     * @param  string[] $chain
     * @return callable
     */
    protected function buildDecoratorChain($abstract, $concrete, array $chain): callable
    {
        // When called with two parameters, this behaves as a bind + factory call.
        if (empty($chain)) {
            $this->container->bind($abstract, $concrete);
            return $this->container->factory($abstract);
        }

        $outer = array_pop($chain);

        $this->container->when($outer)->needs($abstract)->give($concrete);

        return $this->buildDecoratorChain($abstract, $outer, $chain);
    }
}
