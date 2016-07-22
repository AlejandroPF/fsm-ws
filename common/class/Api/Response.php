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
 * Contenedor de la respuesta de la clase \Api\Api
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class Response
{

    /**
     * @var boolean TRUE en caso de error
     */
    private $error;

    /**
     * @var mixed Respuesta de la petición
     */
    private $response;

    /**
     * Constructor
     * @param boolean $error TRUE en caso de error en la petición
     * @param mixed $response Respuesta de la petición
     */
    public function __construct($error = true, $response = "") {
        $this->setAll($error, $response);
    }

    /**
     * Establece los parámetros $error y $response
     * @param boolean $error Error
     * @param mixed $response Respuesta
     */
    public function setAll($error, $response) {
        $this->error = $error;
        $this->response = $response;
    }

    /**
     * Establece si ha habido error
     * @param boolean $error Error
     * @return \Api\Response Devuelve $this
     */
    public function setError($error) {
        $this->error = $error;
        return $this;
    }

    /**
     * Establece la respuesta de la petición
     * @param mixed $response Respuesta de la petición
     * @return \Api\Response
     */
    public function setResponse($response) {
        $this->response = $response;
        return $this;
    }

    /**
     * Crea una instancia a partir de un código de respuesta.
     * @param int $responseCode Código de respuesta
     * @param mixed $responseContent Respuesta de la petición
     * @return \Api\Response Instancia de la clase
     * @see \Api\ResponseCodes
     */
    public static function create($responseCode, $responseContent = null) {
        $output = null;
        $error = false;
        $response = [];
        switch ($responseCode) {
            case ResponseCodes::UNAUTHORIZED:
                $error = true;
                $responseContent = $responseContent !== null ? $responseContent : "Unauthorized";
                break;
            case ResponseCodes::NOT_FOUND:
                $error = true;
                $responseContent = $responseContent !== null ? $responseContent : "Not found";
                break;
            case ResponseCodes::MISSING_FIELDS:
                $error = true;
                $responseContent = $responseContent !== null ? $responseContent : "Missing fields";
                break;
            case ResponseCodes::BAD_GATEWAY:
                $error = true;
                $responseContent = $responseContent !== null ? $responseContent : "Server Error";
                break;
            case ResponseCodes::OK:
            case ResponseCodes::ACCEPTED:
            case ResponseCodes::NO_CONTENT:
                $responseContent = $responseContent !== null ? $responseContent : "No Content";
                if ($responseContent == null) {
                    $responseContent = "No Content";
                }
                break;
            default:
                $error = true;
                $responseContent = $responseContent !== null ? $responseContent : "Undefined";
                break;
        }
        if ($error) {
            $response = ["code" => $responseCode, "message" => $responseContent];
        } else {
            $response = $responseContent;
        }
        $output = new Response($error, $response);
        return $output;
    }

    /**
     * Obtiene si se ha producido un error
     * @return boolean 
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Obtiene la respuesta
     * @return mixed Respuesta
     */
    public function getResponse() {
        return $this->response;
    }

    public function __toString() {
        return json_encode(array("error" => (boolean) $this->error, "response" => $this->response),JSON_UNESCAPED_UNICODE);
    }

}
