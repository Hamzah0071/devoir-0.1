<?php
session_start();
require 'config.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $pass    = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id FROM utilisateur WHERE email = :email");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        $erreur = "Cet email est déjà utilisé.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO utilisateur (name, surname, email, password) VALUES (:name, :surname, :email, :pass)");
        $stmt->execute([':name' => $name, ':surname' => $surname, ':email' => $email, ':pass' => $pass]);
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container small">
    <h1>Créer un compte</h1>

    <?php if ($erreur): ?>
        <p class="erreur"><?= $erreur ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Prénom</label>
        <input type="text" name="name" required>

        <label>Nom</label>
        <input type="text" name="surname" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Mot de passe</label>
        <input type="password" name="password" required>

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
</div>
</body>
</html>
