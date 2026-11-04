<?php

class Database {
    private PDO $pdo;

    public function __construct(string $configFile) {
        $config = parse_ini_file($configFile);

        if ($config === false) {
            throw new RuntimeException('Fichier de configuration introuvable : ' . $configFile);
        }

        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";

        $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}
