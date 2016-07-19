<?php

/**
 * Genera una cadena aleatoria con posibilidad de caracteres alfanuméricos en mayuscula y minuscula y caracteres especiales.
 * 
 * @param int $length Longitud de la cadena
 * @param boolean $uppercase Si admite mayusculas
 * @param boolean $numbers Si admite números
 * @param boolean $special_characters Si admite caracteres especiales
 * @return string Cadena aleatoria con los parámetros dados
 */
function randomString($length = 10, $uppercase = TRUE, $numbers = TRUE, $special_characters = FALSE) {
    $source = 'abcdefghijklmnopqrstuvwxyz';
    $rstr = "";
    if ($uppercase) {
        $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($numbers) {
        $source .= '1234567890';
    }
    if ($special_characters) {
        $source .= '|@#~$%()=^*+[]{}-_';
    }
    if ($length > 0) {
        $source = str_split($source, 1);
        for ($i = 1; $i <= $length; $i++) {
            $num = mt_rand(1, count($source));
            $rstr .= $source[$num - 1];
        }
    }
    return $rstr;
}

/**
 * Encripta una cadena y devuelve su valor.
 * 
 * Utiliza una semilla (salt) para encriptar la cadena. Esta encriptación se
 * puede realizar con 'base_64_encode' o mediante 'sha1'.
 * 
 * @param string $string Cadena a encriptar
 * @param string $key Semilla usada para mayor seguridad en la encriptación.
 * @param boolean $decryptable Si la cadena resultante puede desencriptarse 
 * posteriormente o no. En caso de ser 'false' usará el algoritmo
 * 'base_64_encode' para la encriptación final. En caso contrario usará 'sha1'.
 * @return string
 */
function encrypt($string, $key, $decryptable = false) {
    $string = $string . substr($key, strlen($key) / 2);
    $result = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result.=$char;
    }
    if ($decryptable) {
        $result = base64_encode($result);
    } else {
        $result = sha1($result);
    }
    return $result;
}

/**
 * Desencripta una cadena y devuelve su valor.
 * 
 * Para que esto funcione correctamente, la cadena debe haber sido encriptada
 * mediante la función 'encrypt($str,$key,true)'. Observerse el 'true' como
 * tercer parámetro
 * @param string $string Cadena a desencriptar
 * @param string $key Semilla usada en la encriptación
 * @return type
 */
function decrypt($string, $key) {
    $result = '';
    $string = base64_decode($string);
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result.=$char;
    }
    $result = substr($result, 0, strlen($result) - strlen($key) / 2);
    return $result;
}
