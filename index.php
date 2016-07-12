<?php

require_once "config.php";

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
/**
 * Hanlder for "Not allowed" response code (HTTP 405)
 */
$notAllowedHandler = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $c['response']->withStatus(405)->withHeader('Allow', implode(', ', $methods))->withHeader('Content-type', 'application/json')->write(\API\Response::create(\API\ResponseCodes::METHOD_NOT_ALLOWED, "Method must be one of: " . implode(', ', $methods)));
    };
};
/**
 * Handler for "Not found" response code (HTTP 404)
 */
$notFoundHandler = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']->withStatus(404)->withHeader('Content-Type', 'application/json')->write(\API\Response::create(\API\ResponseCodes::NOT_FOUND));
    };
};
/**
 * Slim\App Configuration
 */
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
    'notAllowedHandler' => $notAllowedHandler,
    'notFoundHandler' => $notFoundHandler,
];
/**
 * Slim\App instance
 */
$app = new Slim\App($configuration);
// Web config as stdClass object
$config = new stdClass();
// Source
$config->source = ROOT;
$app->map(["get", "post"], "/files/{path:.*}", function (Request $request, Response $response, $args) use($app, $config) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $path = $args['path'] != "" ? $args['path'] : "/";
    if (is_dir($config->source . $path)) {
        $dir = dir($config->source . $path);
        $resources = [];
        while ($file = $dir->read()) {
            $resources[] = $file;
        }
        $apiResponse->setAll(false, $resources);
    }
    $api->setResponse($apiResponse);
    return $api->get();
});
$app->map(["get", "post"], "/upload/{file:.*}", function (Request $request, Response $response, $args) use($app, $config) {
    //todo Upload $file
});
$app->map(["get","post"],"/download/{file:.*}",function (Request $request, Response $response, $args) use($app, $config) {
   //todo Download $file 
});
$app->run();
