<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Narrowspark\HttpEmitter\SapiEmitter;
use function DI\create;
use function DI\get;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use Aminkafri\Basic\HelloWorld;
use Relay\Relay;
use Laminas\Diactoros\ServerRequestFactory;
use function FastRoute\simpleDispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAttributes(false);
$containerBuilder->addDefinitions([
    HelloWorld::class => create(HelloWorld::class)
        ->constructor(get('Foo'), get('Response')),
    'Foo' => 'pretty',
    'Response' => function() {
        return new Response();
    }
]);

$container = $containerBuilder->build();

$middlewareQueue = [];

$routes = simpleDispatcher(function (RouteCollector $r) {
    $r->get('/hello', HelloWorld::class);
});

$middlewareQueue[] = new FastRoute($routes);
$middlewareQueue[] = new RequestHandler($container);

$requestHandler = new Relay($middlewareQueue);
$response = $requestHandler->handle(ServerRequestFactory::fromGlobals());

$emitter = new SapiEmitter();
return $emitter->emit($response);
?>
