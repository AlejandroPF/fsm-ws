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

/**
 * Clase JWTManager para el control y creación de JSON Web Token
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 * @uses \Firebase\JWT\JWT Clase de implementación de JSON Web Token
 */
class JWTManager
{

    /**
     * @var array Elementos a guardar en el Token
     */
    private $elements = [
        // Controla el dominio
        "iss" => "http://*"
    ];

    /**
     * @var string Clave secreta para generar el hash
     */
    private static $secret = "bnNc8QkBfZsq8XESnu3PQebBOIIYjByX";

    /**
     * Constructor
     */
    public function __construct() {
        // Agregar
        $this->set("iat", time());
        $this->set("exp", time() + 60 * 60 * 24 * 365 * 5); // Agrega 5 años
    }

    /**
     * Establece todos los elementos
     * @param array $array Conjunto de elementos clave => valor
     */
    public function setAll($array) {
        $this->elements = $array;
    }

    /**
     * Establece un elemento
     * @param string $name Clave
     * @param string $value Valor
     */
    public function set($name, $value) {
        $this->elements[$name] = $value;
    }

    /**
     * Obtiene un elemento
     * @param string $name Clave
     * @return string Valor del elemento o NULL si no existe
     */
    public function get($name) {
        $output = null;
        if (isset($this->elements[$name])) {
            $output = $this->elements[$name];
        }
        return $output;
    }

    /**
     * Obtiene el JSON Web Token
     * @return string JWT
     */
    public function getToken() {
        return \Firebase\JWT\JWT::encode($this->elements, self::$secret);
    }

    /**
     * Obtiene un objeto stdClass con los valores JWT
     * 
     * @param string $jwt Cadena JWT
     * @param string $secret Clave secreta para generar el hash
     * @return stdClass Clase con los campos JWT
     */
    public static function decode($jwt, $secret) {
        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        return Firebase\JWT\JWT::decode($jwt, self::$secret, array('HS256', 'HS512', 'HS384'));
    }

    /**
     * Genera un objeto JWTManager a partir de un token y su clave
     * @param string $token Cadena JWT
     * @param string $secret Clave secreta para generar el hash
     * @return \JWTManager
     */
    public static function createFromToken($token) {
        $jwt = new JWTManager();
        $stdClass = $jwt->decode($token, self::$secret);
        $jwt->setAll(get_object_vars($stdClass));
        return $jwt;
    }

}
