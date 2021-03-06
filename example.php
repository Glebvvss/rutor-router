<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ruter\Route;
use Ruter\Router;
use Ruter\RouteFactory;
use Ruter\Request\Request;
use Ruter\Route\Common\Match;
use Ruter\Route\Decoration\Method;
use Ruter\Request\RequestInterface;
use Ruter\Route\Contract\RouteInterface;
use Ruter\Route\Contract\MatchInterface;

class UserController {
    public function all() {}
    public function one() {}
    public function create() {}
    public function update() {}
    public function delete() {}
}

class CustomController {
    public function handle() {}
}

function handleHomePage() {}

function handleNotFound() {}

class MyRoute implements RouteInterface
{
    public function match(RequestInterface $request): MatchInterface
    {
        // Extra data, which will be provided to match result
        $extra = [
            '_route'      => 'custom_route',
            '_controller' => [CustomController::class, 'handle'],
        ];

        return $request->path() === $this->toUrl()
            ? new Match(true, $extra)
            : new Match(false);
    }

    public function toUrl(array $params = []): string
    {
        return '/custome/route/url';            
    }
}

$routes = [
    // Regular route with any request method
    'home' => RouteFactory::any('/', fn() => handleHomePage()),

    // Routes combined to group
    'user' => RouteFactory::group('/users', [
        'list'   => RouteFactory::get('/', [new UserController(), 'all']),
        'one'    => RouteFactory::get('/{id}', [new UserController(), 'one']),
        'create' => RouteFactory::post('/{id}', [new UserController(), 'create']),
        'update' => RouteFactory::patch('/{id}', [new UserController(), 'update']),
        'delete' => RouteFactory::delete('/{id}', [new UserController(), 'delete']),
    ]),

    // Your own route implementation for anyway, decorated by request method guard, 
    // which will be matched only on POST, and PUT request methods
    'my_post_or_put_route' => new Method(['POST', 'PUT'], new MyRoute()),

    // Your own route implementation for anyway, just need to 
    // implement Ruter\Route\Contracts\RouteInterface and wrap it into Ruter\Route class
    'my_route' => new Route(new MyRoute()),

    // Not found route example
    'not_found' => RouteFactory::any('/{url}', fn() => handleNotFound()),
];

$router = new Router($routes);
$match  = $router->match(Request::fromGlobals());
if ($match->isSuccessfull()) {
    // Extra data, which has provided to route match result
    print_r($match->extra());
}