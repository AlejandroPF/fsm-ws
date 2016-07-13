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
 * Description of Resource
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class Resource
{

    /**
     * @var string Ruta al recurso
     */
    private $source;

    /**
     * @var string Directorio del recurso
     */
    private $path;

    /**
     * @var boolean TRUE en caso de que el recurso exista
     */
    private $validResource;

    /**
     * @var array Recursos excluidos
     */
    private $excludedResources = [".", ".."];

    /**
     * Constructor
     * @param string $source Ruta al recurso
     */
    public function __construct($source) {
        if (is_file($source) || is_dir($source)) {
            $this->validResource = true;
            $this->source = $source;
            $this->path = dirname($source);
        }
    }

    /**
     * Establece si el recurso existe
     * @param boolean $value
     * @return \Fsm\Resource $this 'Fuild setter'
     */
    protected function setValid($value) {
        $this->validResource = $value;
        return $this;
    }

    /**
     * Obtiene si es válido el recurso
     * @return boolean TRUE en caso de que exista el recurso.
     */
    public function isValid() {
        return $this->validResource;
    }

    /**
     * Obtiene el path del recurso
     * @return string Path del recurso
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Obtiene el directorio padre como instancia de esta clase
     * @return \Fsm\Resource Directorio padre
     */
    public function getParent() {
        return new Resource($this->path);
    }

    /**
     * Obtiene los recursos excluidos
     * @return array Recursos excluidos
     */
    public function getExcludedResources() {
        return $this->excludedResources;
    }

    /**
     * Agrega un recurso a recursos excluidos
     * @param string $resourceName Nombre del recurso
     * @return \Fsm\Resource 'Fluent setter'
     */
    public function addExcludedResource($resourceName) {
        $this->excludedResources[] = $resourceName;
        return $this;
    }
}
