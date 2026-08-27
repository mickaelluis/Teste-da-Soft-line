<?php

namespace App\Models;

use App\Exceptions\FalhaDeConexao;

class Database {
    private static $conexao = null;
    public static function conectar(){
        if(self::$conexao !== null ){
            return self::$conexao;
        };
        $host = $_ENV['DB_HOST'] ?? 'db';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $db   = $_ENV['DB_DATABASE'] ?? 'softline';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        try {
            self::$conexao = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw new FalhaDeConexao;
        }
        return self::$conexao;
    }
}
