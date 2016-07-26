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

namespace Fsm;

/**
 * Description of FileResource
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class FileResource extends Resource
{

    /**
     * @var string Nombre del archivo
     */
    private $fileName;

    /**
     * @var string Nombre del archivo sin extensión
     */
    private $name;

    /**
     * @var string Extensión del archivo 
     */
    private $extension;

    public function __construct($source) {
        parent::__construct($source);
        if (is_file($this->getSource())) {
            $this->catchData();
        } else {
            $this->setValid(false);
        }
    }

    /**
     * Recopila todos los datos de la clase
     */
    private function catchData() {
        $source = $this->getSource();
        $explode = explode(DS, $source);
        // Obtiene el archivo
        $file = $explode[count($explode) - 1];
        $this->fileName = $file;
        $explode = explode(".", $file);
        //Obtiene la extensión del archivo
        if (count($explode) > 1) {
            $this->extension = $explode[count($explode) - 1];
            //Quita la extensión del array
            array_pop($explode);
        }
        $this->name = implode(".", $explode);
    }

    /**
     * Obtiene el nombre del archivo
     * @return string Nombre del archivo
     */
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * Obtiene el nombre del archivo sin extensión
     * @return string Nombre del archivo (sin extensión)
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Obtiene la extensión del archivo
     * @return string Extensión del archivo
     */
    public function getExtension() {
        return $this->extension;
    }

}
