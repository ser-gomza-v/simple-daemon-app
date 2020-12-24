<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;


$containerBuilder = require_once 'container.php';

$routes = new RouteCollection();

$routes->add(
    'generate-task',
    new Route(
        '/api/generate-task',
        [
            'controller' => $containerBuilder->get('controller.tasks'),
            'method' => 'generate'
        ],
        [],
        [],
        '',
        'Http',
        'Get'
    )
);

return $routes;