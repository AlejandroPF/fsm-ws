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

class DirectoryResource extends Resource
{

    /**
     * @var array Contiene los archivos del directorio
     */
    private $files;

    /**
     * @var array Contiene los subdirectorios del directorio
     */
    private $directories;
    private $removeSourcePathFromResources = true;

    /**
     * Constructor
     * @param string $source Ruta del directorio
     */
    public function __construct($source) {
        parent::__construct($source);
        if (!is_dir($source)) {
            $this->setValid(false);
        }
    }

    public function removeSourcePathFromResources($boolean) {
        $this->removeSourcePathFromResources = $boolean;
        return $this;
    }

    /**
     * Obtiene los archivos del directorio
     * @return array Archivos
     */
    public function getFiles() {
        $output = [];
        if ($this->isValid()) {
            $dir = dir($this->getSource());
            // Obtiene un elemento del directorio
            while ($entry = $dir->read()) {
                $resource = $dir->path . DS . $entry;
                // Si no se trata de un recurso excluido
                if (!in_array($entry, $this->getExcludedResources())) {
                    if (is_file($resource)) { // Si es archivo
                        if ($this->removeSourcePathFromResources) {
                            $output[] = str_replace($this->getSource(), "", $resource);
                        } else {
                            $output[] = $resource;
                        }
                    }
                }
            }
        } else {
            $output = null;
        }
        $output = array_map(function($element) {
            return str_replace(DS, "/", $element);
        }, $output);
        return $output;
    }

    /**
     * Obtiene los archivos del directorio como instancia de la clase \Fsm\FileResource
     * @return array Conjunto de archivos
     */
    public function getFilesAsResorce() {
        $output = [];
        for ($index = 0; $index < count($this->files); $index++) {
            $output[$index] = new FileResource($this->files[$index]);
        }
        return $output;
    }

    /**
     * Obtiene los subdirectorios del directorio
     * @return array Directorios
     */
    public function getDirectories() {
        $output = [];
        if ($this->isValid()) {
            $dir = dir($this->getSource());
            // Obtiene un elemento del directorio
            while ($entry = $dir->read()) {
                $resource = $dir->path . DS . $entry;
                // Si no se trata de un recurso excluido
                if (!in_array($entry, $this->getExcludedResources())) {
                    if (is_dir($resource)) { // Si es archivo
                        if ($this->removeSourcePathFromResources) {
                            $output[] = str_replace($this->getSource(), "", $resource);
                        } else {
                            $output[] = $resource;
                        }
                    }
                }
            }
        } else {
            $output = null;
        }
        $output = array_map(function($element) {
            return str_replace(DS, "/", $element);
        }, $output);
        return $output;
    }

    /**
     * Obtiene los subdirectorios del directorio como instancia de la clase \Fsm\DirectoryResource
     * @return \Fsm\DirectoryResource Conjunto de directorios
     */
    public function getDirectoriesAsResource() {
        $output = [];
        for ($index = 0; $index < count($this->directories); $index++) {
            $output[$index] = new DirectoryResource($this->directories[$index]);
        }
        return $output;
    }

}
