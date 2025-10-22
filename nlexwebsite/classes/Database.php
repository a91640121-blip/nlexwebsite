<?php
namespace App\Classes;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(array $config): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['db_host'], $config['db_name'], $config['db_charset']);
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
            } catch (PDOException $e) {
            
                throw $e;
            }
        }

        return self::$pdo;
    }
}
