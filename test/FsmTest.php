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
require_once 'config.php';

/**
 * Test case for \Fsm package
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class FsmTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Fsm\Resource Resource
     */
    private $resource;

    /**
     * @var \Fsm\DirectoryResource DirectoryResource
     */
    private $directory;

    /**
     * @var \Fsm\FileResource FileResource
     */
    private $file;

    public function setUp() {
        $this->resource = new Fsm\Resource(__DIR__);
        $this->directory = new Fsm\DirectoryResource(__DIR__);
        $this->file = new Fsm\FileResource(__FILE__);
        $this->directory->removeSourcePathFromResources(false);
    }

    public function tearDown() {
        
    }

    public function testResourceIsValid() {
        $this->assertTrue($this->resource->isValid());
    }

    public function testResourceGetSource() {
        $this->assertEquals(__DIR__, $this->resource->getSource());
    }

    /**
     * @depends testResourceIsValid
     */
    public function testResourceGetPath() {
        $this->assertEquals(dirname(__DIR__), $this->resource->getPath());
        return $this->resource->getPath();
    }

    /**
     * @depends testResourceGetPath
     * @param string $path Path
     */
    public function testResourceGetParent($path) {
        $this->assertEquals(new Fsm\Resource($path), $this->resource->getParent());
    }

    public function testDirectoryResourceIsValid() {
        $this->assertTrue($this->directory->isValid());
    }

    /**
     * @depends testDirectoryResourceIsValid
     */
    public function testDirectoryResourceGetFiles() {
        $dir = dir(__DIR__);
        $expectedFiles = [];
        while ($entry = $dir->read()) {
            if (is_file($entry)) {
                $expectedFiles[] = __DIR__ . DS . $entry;
            }
        }
        $expectedFiles = array_map(function($element) {
            return str_replace(DS, "/", $element);
        }, $expectedFiles);
        $dir->close();
        $this->assertEquals($expectedFiles, $this->directory->getFiles());
        return $expectedFiles;
    }

    /**
     * @depends testDirectoryResourceGetFiles
     * @param array $files Array de archivos
     */
    public function testDirectoryResourceGetFilesAsResource($files) {
        $expectedFiles = [];
        for ($index = 0; $index < count($files); $index++) {
            $expectedFiles[$index] = new Fsm\FileResource($files[$index]);
        }
        $this->assertEquals($expectedFiles, $this->directory->getFilesAsResorce());
    }

    /**
     * @depends testDirectoryResourceIsValid
     */
    public function testDirectoryResourceGetDirectories() {
        $dir = dir(__DIR__);
        $expectedDirectories = [];
        $excluded = $this->directory->getExcludedResources();
        while ($entry = $dir->read()) {
            if (is_dir($entry) && !in_array($entry, $excluded)) {
                $expectedDirectories[] = __DIR__ . DS . $entry;
            }
        }
        $dir->close();
        $this->assertEquals($expectedDirectories, $this->directory->getDirectoriesAsResource());
        return $expectedDirectories;
    }

    /**
     * @depends testDirectoryResourceGetDirectories
     * @param array $directories Array de directorios
     */
    public function testDirectoryResourceGetDirectoriesAsResource($directories) {
        $expectedDirectories = [];
        for ($index = 0; $index < count($directories); $index++) {
            $expectedDirectories[$index] = new Fsm\DirectoryResource($directories[$index]);
        }
        $this->assertEquals($expectedDirectories, $this->directory->getDirectoriesAsResource());
    }

    public function testFileResourceIsValid() {
        $this->assertTrue($this->file->isValid());
    }

    /**
     * @depends testFileResourceIsValid
     */
    public function testFileResourceGetFileName() {
        $explode = explode(DS, __FILE__);
        $expectedFileName = $explode[count($explode) - 1];
        $this->assertEquals($expectedFileName, $this->file->getFileName());
        return $expectedFileName;
    }

    /**
     * @depends testFileResourceGetFileName
     * @param string $fileName File Name
     */
    public function testFileResourceGetName($fileName) {
        $explode = explode(".", $fileName);
        array_pop($explode);
        $expectedName = implode(".", $explode);
        $this->assertEquals($expectedName, $this->file->getName());
    }

    /**
     * @depends testFileResourceGetFileName
     * @param string $fileName File Name
     */
    public function testFileResourceGetExtension($fileName) {
        $explode = explode(".", $fileName);
        $expectedExtension = count($explode) > 1 ? $explode[count($explode) - 1] : null;
        $this->assertEquals($expectedExtension, $this->file->getExtension());
    }

}
