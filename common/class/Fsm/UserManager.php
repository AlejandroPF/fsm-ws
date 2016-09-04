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
 * Clase que controla a los usuarios de la aplicación
 *
 * @author Alejandro Peña Florentín (alejandropenaflorentin@gmail.com)
 */
class UserManager
{

    /**
     * @var string Ruta del archivo que contiene los usuarios
     */
    private static $usersFile = COMMON . "users.json";

    /**
     * Busca un usuario.
     * 
     * @param string $userName Nombre del usuario
     * @return \Fsm\User Usuario. En caso de no encontrar al usuario devuelve FALSE
     */
    public static function findUser($userName) {
        $output = false;
        $users = self::getUsers();
        // Rutina de búsqueda
        $index = 0;
        while ($index < count($users) && !$output) {
            /**
             * @var \Fsm\User $obj Objeto cargado desde archivo
             */
            $obj = unserialize($users[$index]);
            if ($obj->getName() === $userName) {
                $output = $obj;
            }
            $index++;
        }
        return $output;
    }

    /**
     * Autentica a un usuario
     * @param string $user Nombre del usuario
     * @param string $password Contraseña del usuario
     * @param boolean $encrypted Indica si la contraseña pasada como parámetro <b>$password</b> está encriptada.
     * @return boolean TRUE en caso de éxito. FALSE en cualquier otro caso
     */
    public static function authenticate($user, $password, $encrypted = false) {
        $output = false;
        $dbUser = self::findUser($user);
        if ($dbUser) {
            $output = $encrypted ? ($password === $dbUser->getPassword()) : (encrypt($password, \WebConfig::SALT) === $dbUser->getPassword());
        }
        return $output;
    }
    /**
     * Guarda al nuevo usuario o modifica el que ya existe
     * @param \Fsm\User $newUser Usuario
     */
    public static function saveUser(User $newUser) {
        // Checks if user already exists
        $users = self::getUsers();
        $index = 0;
        $size = count($users);
        $found = false;
        while ($index < $size && $found === FALSE) {
            $obj = unserialize($users[$index]);
            if ($obj->getName() == $newUser->getName()) {
                $found = $index;
            } else {
                $index++;
            }
        }
        if ($found !== FALSE) {
            $users[$index] = serialize($newUser);
        } else {
            array_push($users, serialize($newUser));
        }
        file_put_contents(self::$usersFile, json_encode($users));
    }

    /**
     * Obtiene todos los usuarios
     * @return array Conjunto de usuarios
     */
    public static function getUsers() {
        return json_decode(file_get_contents(self::$usersFile));
    }
    /**
     * Obtiene todos los usuarios como un array de \Fsm\User
     * @return array Conjunto de usuarios
     */
    public static function getUsersAsObject() {
        $users = self::getUsers();
        $size = count($users);
        for ($index = 0; $index < $size; $index++) {
            $users[$index] = unserialize($users[$index]);
        }
        return $users;
    }
    /**
     * Elimina a un usuario en función de su nombre
     * @param string $userName Nombre de usuario
     * @return boolean TRUE en caso de éxito
     */
    public static function deleteUser($userName) {
        $output = false;
        // Checks if user already exists
        $users = self::getUsersAsObject();
        $index = 0;
        $size = count($users);
        $found = false;
        while ($index < $size && $found === FALSE) {
            if ($users[$index]->getName() == $userName) {
                $found = $index;
            } else {
                $index++;
            }
        }
        if ($found !== FALSE) {
            unset($users[$found]);
            sort($users);
            $fileContent = \Utils::serializeArray($users);
            file_put_contents(self::$usersFile, json_encode($fileContent));
            $output = true;
        } 
        return $output;
    }
    
}
