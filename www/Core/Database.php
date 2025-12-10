<?php 

namespace App\Core;

use PDO;
use PDOException;


class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // Chargement des variables d'environnement
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('POSTGRES_DB') ?: 'devdb';
        $user = getenv('POSTGRES_USER') ?: 'devuser';
        $password = getenv('POSTGRES_PASSWORD') ?: 'devpass';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        // Options de sécurité PDO
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // CRITIQUE: Désactive l'émulation pour la sécurité
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize a singleton."); }
}
