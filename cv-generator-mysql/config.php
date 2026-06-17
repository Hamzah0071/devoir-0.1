<?php


// define('DB_HOST', 'localhost');       // Hôte MySQL (ex: localhost, 127.0.0.1)
// define('DB_PORT', '3306');            // Port MySQL (défaut : 3306)
// define('DB_NAME', 'cv_generator');    // Nom de ta base de données
// define('DB_USER', 'root');            // Utilisateur MySQL
// define('DB_PASS', '');                // Mot de passe MySQL

// /**
//  * Retourne une connexion PDO singleton.
//  * Lève une exception si la connexion échoue.
//  *
//  * @return PDO
//  */
// function getDB(): PDO
// {
//     static $pdo = null;

//     if ($pdo !== null) {
//         return $pdo;
//     }

//     $dsn = sprintf(
//         'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
//         DB_HOST,
//         DB_PORT,
//         DB_NAME
//     );

//     $options = [
//         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//         PDO::ATTR_EMULATE_PREPARES   => false,
//     ];

//     try {
//         $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
//     } catch (PDOException $e) {
//         // En production, ne jamais afficher le message brut
//         error_log('DB connection error: ' . $e->getMessage());
//         die('Erreur de connexion à la base de données. Vérifiez config.php.');
//     }

//     return $pdo;
// }

// ou 

// <?php

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