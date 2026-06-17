<?php

function getDB(): PDO
{
    static $pdo = null;

    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=cv_generator;charset=utf8mb4',
            'root',  // utilisateur
            '',      // mot de passe
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}