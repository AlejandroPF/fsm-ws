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
 * WebConfig para la configuración del proyecto web
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class WebConfig
{

    /**
     * Clave de encriptación
     */
    const SALT = "P85J-=x%pii~l@og82VixH|r\$I-wv\$SpSaDpN_n8icfZ1En_*q1ssch~)ny-[Ogk";

    /**
     * @var string Directorio base del sistema de ficheros
     */
    public static $SOURCE = "C:\\";

    /**
     * @var string Formato de fecha por defecto
     */
    public static $DATE_FORMAT = "d/m/Y H:i:s";

    public static function parseIniFile($file) {
        $config = parse_ini_file($file);
        self::$SOURCE = \Utils::addTrailingSlash($config['source']);
        self::$DATE_FORMAT = $config['dateFormat'];
    }

}
