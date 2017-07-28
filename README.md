# lazy-container
A lazy (or lazier) version of Laravel's Container.

## Install

Composer blah blah

## Usage

As with most container bindings, you should limit container dependency
to service providers. Make the `LazyContainer` on your `AppServiceProvider`, to start with:

```php
use Woda\Container\LazyContainer;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $lazy = $this->app->make(LazyContainer::class);
    }
}
```

Following examples will assume an instance of the `LazyContainer` on the `$lazy` variable.

### Closures

A good way to decouple objects is to replace a concrete method call with a higher order function.
Laravel's `Collection` object is a good example of this.

`LazyContainer` allows you to delegate all dependency resolution of a message call to the container,
and build a zero-parameter `Closure` that can be injected on your objects, thus making them unaware of
those dependencies.

```php
$fun = $lazy->closure('SomeComplicated@method');
$moreFun = $lazy->closure(function (Depend $on, Whatever $youNeed) {});

$this->app->bind(MyPretty::class, function () use ($fun) {
    new MyPretty($fun);
});
```

Any callable (even Laravel's callable strings) will be resolved through the `Container` when called on.

### Decorator chains

Decorators are a complicated beast for dependency injection containers:

- There's a common interface, so you _should_ be able to resolve something when asked for it, but
- Each decorator depends on that interface, so you _need_ contextual binding, else you'll end up in recursive dependencies.

With Laravel's contextual binding this is _doable_, but `LazyContainer` makes it way easier and more readable:

```php
 * // Given an abstract, concrete and two decorators
 * $fn = $lazy->decorate('LoggerInterface', 'Logger', 'BufferingLogger', 'EmailLogger');
 *
 * $logger = $fn(); // new BufferingLogger(new EmailLogger(new Logger)));
 *
 * $logger = $this->app('LoggerInterface'); // Also returns the decorated chain
```

As you can see, the outermost decorator is bound to the interface, and then each link in the chain is 
contextually bound to the next until the _concrete_ implementation, which shouldn't depend on the common 
interface anymore.

