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

$authenticateToken = function($request, $response, $next) use($app) {
    $api = new Api\Api($app, $request, $response);
    $api->verifyRequiredParams("token");
    $apiResponse = new Api\Response;
    $token = $api->param("token");
    try {
        $jwt = JWTManager::createFromToken($token);
        $usr = $jwt->get("usr");
        $pwd = $jwt->get("pwd");
        if (\Fsm\UserManager::authenticate($usr, $pwd, true)) {
            $apiResponse->setAll(false, $jwt->getToken());
        }
    } catch (Exception $ex) {
        $apiResponse->setAll(true, "Invalid token");
    }
    $api->setResponse($apiResponse);
    if ($apiResponse->getError()) {
        return $api->get();
    } else {
        return $next($request, $response);
    }
};
$app->options("/{p:.*}", function (Request $request, Response $response, $args) {
    $response = $response->withHeader("Content-type", $request->getHeader("Content-type"));
    return $response;
});
$app->map(["get", "post"], "/authenticate[/]", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $user = $api->param("user", null);
    $password = $api->param("password", null);
    $apiResponse = new Api\Response;
    $token = $api->param("token", null);
    $encrypted = false;
    if (null === $token || empty(trim($token))) {
        $api->verifyRequiredParams("user", "password");
        $user = $api->param("user");
        $password = $api->param("password");
    } else {
        try {
            $jwt = JWTManager::createFromToken($token);
            $user = $jwt->get("usr");
            $password = $jwt->get("pwd");
            $encrypted = true;
        } catch (Exception $ex) {
            $apiResponse = Api\Response::create(Api\ResponseCodes::UNAUTHORIZED, "Token no válido");
            $api->setResponse($apiResponse);
            return $api->get();
        }
    }
    if (\Fsm\UserManager::authenticate($user, $password, $encrypted)) {
        $jwt = new JWTManager();
        $jwt->set("usr", $user);
        $jwt->set("pwd", encrypt($password, \WebConfig::SALT));
        $apiResponse->setAll(false, $jwt->getToken());
    } else {
        $apiResponse = Api\Response::create(Api\ResponseCodes::UNAUTHORIZED, "Credenciales no validas");
    }
    $api->setResponse($apiResponse);
    return $api->get();
});
$app->map(["get", "post"], "/files/{path:.*}", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $path = $args['path'] != "" ? $args['path'] : "/";
    $directory = new \Fsm\DirectoryResource(WebConfig::$SOURCE . $path);
    $currentPath = str_replace(WebConfig::$SOURCE, "", $directory->getSource());
    if ($directory->isValid()) {
        $files = $directory->getFiles();
        $subdirs = $directory->getDirectories();
        $apiResponse->setAll(false, ["path" => $currentPath, "files" => $files, "directories" => $subdirs]);
    } else {
        $apiResponse = Api\Response::create(Api\ResponseCodes::NOT_FOUND, "Directorio no encontrado");
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN
$app->map(["get", "post"], "/info/[{path:.*}]", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    if (!isset($args['path'])) {
        $args['path'] = "/";
    }
    $file = new \Fsm\FileResource(WebConfig::$SOURCE . $args['path']);
    $apiResponse = new Api\Response;
    if ($file->isValid()) {
        $output['name'] = $file->getFileName();
        $output['size'] = filesize($file->getSource());
        $output['lastUpdate'] = date(WebConfig::$DATE_FORMAT, filemtime($file->getSource()));
        $encodedSource = substr($file->getSource(), strlen(WebConfig::$SOURCE));
        $output['download'] = "http://" . $_SERVER['SERVER_NAME'] . "/download/" . $encodedSource;
        $apiResponse->setAll(false, $output);
    } else {
        $dir = new \Fsm\DirectoryResource(WebConfig::$SOURCE . $args["path"]);
        if ($dir->isValid()) {
            $output['name'] = $dir->getSource();
            $output['size'] = filesize($dir->getSource());
            $output['lastUpdate'] = date(WebConfig::$DATE_FORMAT, filemtime($file->getSource()));
            $apiResponse->setAll(false, $output);
        } else {
            $apiResponse = Api\Response::create(Api\ResponseCodes::NOT_FOUND, "Archivo o directorio no encontrado");
        }
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken);
$app->map(["get", "post"], "/upload/", function (Request $request, Response $response, $args) use($app) {

    if (isset($_FILES) && !empty($_FILES)) {
        $file = $_FILES['file'];
        $path = $_REQUEST['path'];
        if (move_uploaded_file($file['tmp_name'], WebConfig::$SOURCE . $path . "/" . $file['name'])) {
            header("HTTP/1.1 200 OK");
            echo "File uploaded!";
        }
    } else {
        header("HTTP/1.1 404 Not Found");
    }
});
$app->map(["get", "post"], "/download/{path:.*}", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $path = $args['path'] != "" ? $args['path'] : "/";
    $file = new Fsm\FileResource(WebConfig::$SOURCE . $path);
    if ($file->isValid()) {
        // DOWNLOAD FILE!
        header("Content-type: application/octet-stream");
        header('Pragma: no-cache');
        header('Content-Disposition: attachment; filename=' . $file->getFileName());
        header("Content-Transfer-Encoding: binary");
        readfile(WebConfig::$SOURCE . $path);
        die();
    } else {
        $apiResponse = Api\Response::create(Api\ResponseCodes::NOT_FOUND, "File not found");
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN
$app->map(["get", "post"], "/delete/{path:.*}", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $path = $args['path'] != "" ? $args['path'] : "/";
    $file = new Fsm\FileResource(WebConfig::$SOURCE . $path);
    if ($file->isValid()) {
        if (unlink($file->getSource())) {
            $apiResponse->setAll(false, "Archivo eliminado correctamente");
        } else {
            $apiResponse->setResponse("No se ha podido eliminar el archivo");
        }
    } else {
        $dir = new \Fsm\DirectoryResource(WebConfig::$SOURCE . $args["path"]);
        if ($dir->isValid()) {
            if ($dir->isEmpty()) {
                if (rmdir($dir->getSource())) {
                    $apiResponse->setAll(false, "Directorio eliminado correctamente");
                } else {
                    $apiResponse->setResponse("No se ha podido eliminar el directorio");
                }
            } else {
                $apiResponse->setResponse("El directorio no está vacío");
            }
        } else {
            $apiResponse = Api\Response::create(Api\ResponseCodes::NOT_FOUND, "Archivo o directorio no encontrado " . $path);
        }
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN
$app->map(["get", "post"], "/folder/add/", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $api->verifyRequiredParams("path", "folder");
    $folder = $api->param("folder");
    $path = $api->param("path");
    $fullPath = Utils::addTrailingSlash(WebConfig::$SOURCE . $path);
    if (Utils::directoryExists($fullPath) && !Utils::directoryExists($fullPath . $folder)) {
        if (mkdir($fullPath . $folder, 0777, true)) {
            $apiResponse->setAll(FALSE, "Directorio creado correctamente");
        } else {
            $apiResponse->setResponse("No se ha podido crear el directorio. Comprueba los permisos");
        }
    } else {
        if (Utils::directoryExists($fullPath)) {
            $apiResponse->setResponse("El directorio '" . $folder . "' ya existe");
        } else {
            $apiResponse->setResponse("No se encuentra el directorio base '" . $path . "'");
        }
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN
$app->map(["get", "post"], "/changePassword/", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $api->verifyRequiredParams("password");
    $password = $api->param("password");
    $token = $api->param("token");
    $jwt = JWTManager::createFromToken($token);
    $userName = $jwt->get("usr");
    $user = \Fsm\UserManager::findUser($userName);
    if ($user !== FALSE) {
        $user->setPassword(encrypt($password, \WebConfig::SALT));
        \Fsm\UserManager::saveUser($user);
        $apiResponse->setAll(false, "Contaseña cambiada correctamente");
    } else {
        $apiResponse->setResponse("Nombre de usuario no válido");
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN
$app->map(["get", "post"], "/users/", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $token = $api->param("token");
    $jwt = JWTManager::createFromToken($token);
    $userName = $jwt->get("usr");
    if ($userName == "root") {
        $users = \Fsm\UserManager::getUsers();
        $size = count($users);
        for ($index = 0; $index < $size; $index++) {
            $user = unserialize($users[$index]);
            $users[$index] = $user->getName();
        }
        sort($users);
        $apiResponse->setAll(false, $users);
    } else {
        $apiResponse->setResponse("Solo el usuario 'root' puede ver a los demás usuarios");
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN

$app->map(["get", "post"], "/users/add/", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $api->verifyRequiredParams("user", "password");
    $token = $api->param("token");
    $jwt = JWTManager::createFromToken($token);
    $userName = $jwt->get("usr");
    if ($userName == "root") {
        $userName = $api->param("user");
        $password = $api->param("password");
        $userAlreadyExists = \Fsm\UserManager::findUser($userName);
        if ($userAlreadyExists === FALSE) {
            $newUser = new Fsm\User;
            $newUser->setName($userName);
            $newUser->setPassword(encrypt($password, WebConfig::SALT));
            \Fsm\UserManager::saveUser($newUser);
            $apiResponse->setAll(false, "Usuario agregado correctamente");
        } else {
            $apiResponse->setResponse("El usuario ya existe");
        }
    } else {
        $apiResponse->setResponse("Solo el usuario 'root' puede ver agregar usuarios");
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN

$app->map(["get", "post"], "/users/delete/", function (Request $request, Response $response, $args) use($app) {
    $api = new Api\Api($app, $request, $response);
    $apiResponse = new Api\Response;
    $api->verifyRequiredParams("user");
    $token = $api->param("token");
    $jwt = JWTManager::createFromToken($token);
    $userName = $jwt->get("usr");
    if ($userName == "root") {
        $userName = $api->param("user");
        $password = $api->param("password");
        $userAlreadyExists = \Fsm\UserManager::findUser($userName);
        if ($userAlreadyExists !== FALSE) {
            if ($userName !== "root") {
                if (\Fsm\UserManager::deleteUser($userName)) {
                    $apiResponse->setAll(false, "Usuario eliminado correctamente");
                } else {
                    $apiResponse->setResponse("No se ha podido eliminar al usuario");
                }
            } else {
                $apiResponse->setResponse("No se puede eliminar al usuario root");
            }
        } else {
            $apiResponse->setResponse("El usuario no existe");
        }
    } else {
        $apiResponse->setResponse("Solo el usuario 'root' puede eliminar otros usuarios");
    }
    $api->setResponse($apiResponse);
    return $api->get();
})->add($authenticateToken); // AGREGA AUTENTICACIÓN VIA TOKEN
$app->run();

