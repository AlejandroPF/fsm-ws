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
     * Obtiene todos los usuarios
     * @return array Conjunto de usuarios
     */
    public static function getUsers() {
        return json_decode(file_get_contents(self::$usersFile));
    }

}
