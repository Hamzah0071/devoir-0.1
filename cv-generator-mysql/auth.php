<?php
session_start();
require_once 'config.php';

$action = $_POST['action'] ?? '';
$db = getDB();

// ── DECONNEXION ───────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: index.php?mode=login');
    exit;
}

// ── CONNEXION ─────────────────────────────────────────────────────
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs.';
        header('Location: index.php?mode=login');
        exit;
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']); // Sécurité : on retire le mot de passe
        $_SESSION['user'] = $user;
        header('Location: cv-form.php');
    } else {
        $_SESSION['error'] = 'Email ou mot de passe incorrect.';
        header('Location: index.php?mode=login');
    }
    exit;
}

// ── INSCRIPTION ───────────────────────────────────────────────────
if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($surname) || empty($email) || strlen($password) < 6) {
        $_SESSION['error'] = 'Champs invalides (mot de passe : 6 caractères min).';
        header('Location: index.php?mode=register');
        exit;
    }

    try {
        $insert = $db->prepare('INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)');
        $insert->execute([$name, $surname, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $_SESSION['success'] = 'Compte créé ! Connectez-vous.';
        header('Location: index.php?mode=login');
    } catch (Exception $e) {
        $_SESSION['error'] = 'Cet email est déjà utilisé.';
        header('Location: index.php?mode=register');
    }
    exit;
}

header('Location: index.php');
exit;