<?php

class Conexion
{
    private static $pdo = null;

    public static function getConexion()
    {
        if (self::$pdo === null) {
            $dsn  = 'mysql:host=localhost;dbname=mybox;charset=utf8mb4';
            $user = getenv('DB_USER') ?: 'root'; 
            $pass = getenv('DB_PASS') ?: 'Sos@2006';       

            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
