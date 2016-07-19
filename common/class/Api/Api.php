<?php

/*
 * The MIT License
 *
 * Copyright 2016 Alejandro Peña Florentín (alejandropenaflorentin@gmail.com).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Api;

/**
 * Maneja el uso de peticiones y respuestas del framework Slim
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 * @uses \Slim\App Framework Slim
 * @uses \Slim\Http\Request Implementación de \Psr\Http\Message\RequestInterface
 * @uses \Slim\Http\Response Implementación de \Psr\Http\Message\ResponseInterface
 */
class Api
{

    /**
     * @var \Slim\App Aplicación Slim
     */
    private $app;

    /**
     * @var \Slim\Http\Request Petición HTTP
     */
    private $httpRequest;

    /**
     * @var \Slim\Http\Response Respuesta HTTP
     */
    private $httpResponse;

    /**
     * @var Response Contenido de la respuesta a la petición del WS
     */
    private $response;
    private $parameters = [];

    public function __construct(\Slim\App $app, \Slim\Http\Request $httpRequest, \Slim\Http\Response $httpResponse) {
        $this->app = $app;
        $this->app = $app;
        $this->httpRequest = $httpRequest;
        $this->httpResponse = $httpResponse;
        $this->setHeader("Content-Type", "application/json; charset=UTF-8");
        $this->parseParameters();
    }

    /**
     * Establece un valor para una cabecera HTTP
     * @param string $headerName Nombre de la cabecera
     * @param mixed $headerValue Valor de la cabecera
     */
    public function setHeader($headerName, $headerValue) {
        $response = $this->httpResponse->withHeader($headerName, $headerValue);
        $this->setHttpResponse($response);
    }

    /**
     * Obtiene la petición HTTP
     * @return \Slim\Http\Request
     */
    function getHttpRequest() {
        return $this->httpRequest;
    }

    /**
     * Ontiene la respuesta HTTP
     * @return \Slim\Http\Response Respuesta HTTP
     */
    function getHttpResponse() {
        return $this->httpResponse;
    }

    /**
     * Establece la respuesta HTTP
     * @param \Slim\Http\Response $response Respuesta HTTP
     */
    function setHttpResponse($response) {
        $this->httpResponse = $response;
    }

    /**
     * Establece la respuesta a la petición.
     * No es respuesta HTTP, sino su contenido
     * @param \Api\Response $response Respuesta de la petición
     */
    public function setResponse(Response $response) {
        $this->response = $response;
    }

    /**
     * Obtiene la respuesta de la petición
     * @return \Api\Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Verifica que existan los parámetros pasados.
     * Acepta un número de parámetros indefinidos de tipo <b>string</b>.
     */
    function verifyRequiredParams() {
        // Obtiene los argumentos pasados a la función
        $requiredParams = func_get_args();
        // Obtiene los parámetros pasados a la petición HTTP (independientemente del método)
        $httpParams = $this->parameters;
        // Guarda los parámetros necesarios no pasados
        $parametersNeeded = [];
        $error = false;
        for ($index = 0; $index < count($requiredParams); $index++) {
            // Comprueba si existen los parámetros necesarios
            if (!isset($httpParams[$requiredParams[$index]]) || strlen($httpParams[$requiredParams[$index]]) == 0) {
                // Si no existe genera error y guarda el nombre del parámetro para avisar al usuario
                $error = true;
                $parametersNeeded[] = $requiredParams[$index];
            }
        }
        if ($error) {
            // Si no se ha encontrado uno o varios parámetros necesarios lanza una respuesta advirtiendo al usuario
            $implode = implode(", ", $parametersNeeded);
            $str = count($parametersNeeded) == 1 ? "Falta el parámetro " . $implode . " o está vacío." : "Faltan los parámetros " . $implode . " o están vacíos";
            $response = \API\Response::create(\API\ResponseCodes::MISSING_FIELDS, $str);
            $this->setResponse($response);
            // Detiene la aplicación para evitar que salten otros eventos
            $this->stopApp($this->get());
        }
    }
    public function getParams(){
        return $this->parameters;
    }
    /**
     * Obtiene los parámetros
     */
    public function parseParameters() {
        if(strpos($this->httpRequest->getContentType(),"json")!== false){
            $this->parameters = json_decode($this->httpRequest->getBody()->read(10240));
        }
        $this->parameters = $this->httpRequest->getParams();
    }

    /**
     * Obtiene el valor del parámetro dado o un valor por defecto en caso de que no exista.
     * 
     * @param string $paramName Nombre del parámetro
     * @return string Valor del parámetro o null si no existe
     */
    public function param($paramName, $defaultValue = null) {
        return isset($this->parameters[$paramName]) ? $this->parameters[$paramName] : $defaultValue;
    }

    /**
     * Detiene la aplicación debido a un error
     * @param \Psr\Http\Message\ResponseInterface $apiResponse Respuesta que se envía al cliente
     * @throws \Exception Excepción lanzada para detener la aplicación
     */
    public function stopApp(\Psr\Http\Message\ResponseInterface $apiResponse) {
        $c = $this->app->getContainer();
        $c['errorHandler'] = function ($c) use($apiResponse) {
            return function ($request, $response, $exception) use ($c, $apiResponse) {
                return $apiResponse;
            };
        };
        // Lanza excepción para parar la aplicación
        throw new \Exception();
    }

    /**
     * Obtiene la respuesta HTTP generada.
     * @return \Psr\Http\Message\ResponseInterface Respuesta HTTP
     */
    public function get() {
        if ($this->response != null) {
            $str = $this->response->__toString();
            $this->getHttpResponse()->getBody()->write($str);
        }

        return $this->getHttpResponse();
    }

}
