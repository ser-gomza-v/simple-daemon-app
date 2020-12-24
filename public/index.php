<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;


try {
    register_shutdown_function(function () {
        $error = error_get_last();

        if ($error !== NULL && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING,E_RECOVERABLE_ERROR))) {
            $response = new JsonResponse(['error' => $error['message']], 500);

            $response->send();
        }
    });

    /** @var RouteCollection $routes */
    $routes = include __DIR__ . '/../config/routes.php';

    $request = Request::createFromGlobals();
    $cors = [
        'Access-Control-Allow-Origin' => $request->headers->get('Origin'),
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'DNT, X-User-Token, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Authorization',
    ];

    if ($request->getMethod() === Request::METHOD_OPTIONS) {
        $response =  new Response('', 204);

        foreach ($cors as $headerName => $headerValues) {
            $response->headers->set($headerName, $headerValues);
        }

        $response->send();
    }

    $context = new RequestContext();
    $context->fromRequest($request);

    $matcher = new UrlMatcher($routes, $context);

    $parameters = $matcher->matchRequest($request);
    $request->attributes->add($parameters);

    $response = $parameters['controller']->{$parameters['method'] . 'Action'}($request);

} catch (ResourceNotFoundException $exception) {
    file_put_contents('/tmp/ResourceNotFoundException.log', $exception->getMessage() . $exception->getTraceAsString(), FILE_APPEND);
    $response = new JsonResponse(['error' => 'Not found'], 404);
} catch (Exception $exception) {
    file_put_contents('/tmp/Exception.log', $exception->getMessage(), FILE_APPEND);
    $response = new JsonResponse(['error' => 'An error occurredв'], 500);
}
if (is_array($cors) && count($cors)){
    foreach ($cors as $headerName => $headerValues) {
        $response->headers->set($headerName, $headerValues);
    }
}

$response->send();
