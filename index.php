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

$app->map(["get", "post"], "/authenticate[/]", function (Request $request, Response $response, $args) use($app, $config) {
    $api = new Api\Api($app, $request, $response);
    $api->verifyRequiredParams("user", "password");
    $apiResponse = new Api\Response;
    $user = $api->param("user");
    $password = $api->param("password");
    if (\Fsm\UserManager::authenticate($user, $password)) {
        $jwt = new JWTManager();
        $jwt->set("usr", $user);
        $jwt->set("pwd", encrypt($password, $config->SALT));
        $apiResponse->setAll(false, $jwt->getToken());
    } else {
        $apiResponse->setAll(true, "Fallo de autenticación");
    }
    $api->setResponse($apiResponse);
    $api->get();
});
$app->map(["get", "post"], "/files/{path:.*}", function (Request $request, Response $response, $args) use($app, $config) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $path = $args['path'] != "" ? $args['path'] : "/";
    $directory = new \Fsm\DirectoryResource($config->source . $path);
    $currentPath = str_replace($config->source, "", $directory->getSource());
    if ($directory->isValid()) {
        $files = $directory->getFiles();
        $subdirs = $directory->getDirectories();
        $apiResponse->setAll(false, ["path" => $currentPath, "files" => $files, "directories" => $subdirs]);
    }
    $api->setResponse($apiResponse);
    return $api->get();
});
$app->map(["get", "post"], "/upload/{file:.*}", function (Request $request, Response $response, $args) use($app, $config) {
    //todo Upload $file
});
$app->map(["get", "post"], "/download/{path:.*}", function (Request $request, Response $response, $args) use($app, $config) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $path = $args['path'] != "" ? $args['path'] : "/";
    $file = new Fsm\FileResource($path);
    if ($file->isValid()) {
        // DOWNLOAD FILE!
        header("Content-type: application/octet-stream");
        header('Pragma: no-cache');
        header('Content-Disposition: attachment; filename=' . $file->getFileName());
        header("Content-Transfer-Encoding: binary");
        readfile($config->source . $path);
        die();
    } else {
        $apiResponse = Api\Response::create(Api\ResponseCodes::NOT_FOUND, "File not found");
    }
    $api->setResponse($apiResponse);
    return $api->get();
});
$app->run();
