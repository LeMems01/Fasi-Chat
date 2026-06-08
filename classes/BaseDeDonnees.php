<?php
/**
 * BaseDeDonnees — Singleton PDO
 * Concept POO : Singleton, Encapsulation
 */
class BaseDeDonnees
{
    private static ?PDO $instance = null;
    private function __construct() {}
    private function __clone()    {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                throw new RuntimeException('Connexion BDD impossible : ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
