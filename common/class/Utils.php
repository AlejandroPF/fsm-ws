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
 * Clase Utils con utilidades
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class Utils
{

    /**
     * Comprueba si existe un directorio
     * @param string $dir Nombre del directorio
     * @return boolean TRUE en caso de éxito
     */
    public static function directoryExists($dir) {
        return is_dir($dir);
    }

    /**
     * Agrega el 'trailing slash' a un path
     * @param string $path Path
     * @return string Path con trailing slash
     */
    public static function addTrailingSlash($path) {
        if (substr($path, strlen($path) - 1) != "/") {
            $path .= "/";
        }
        return $path;
    }

    /**
     * Serializa los objetos de un array
     * @param array $array Array
     * @return array Array con objetos serializados
     */
    public static function serializeArray($array) {
        for ($index = 0; $index < count($array); $index++) {
            $array[$index] = serialize($array[$index]);
        }
        return $array;
    }

    /**
     * Des-serializa los elementos de un array en objetos
     * @param array $array Array serializado
     * @return array Array con objetos
     */
    public static function unserializeArray($array) {
        for ($index = 0; $index < count($array); $index++) {
            $array[$index] = unserialize($array[$index]);
        }
        return $array;
    }

}
