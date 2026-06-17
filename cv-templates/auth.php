<?php
session_start();
require_once __DIR__ . '/config.php';

$action = $_POST['action'] ?? '';

if     ($action === 'login')    handleLogin();
elseif ($action === 'register') handleRegister();
elseif ($action === 'logout')   handleLogout();
else   header('Location: index.php');

// ── Login ─────────────────────────────────────────────────────────
function handleLogin(): void
{
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['error'] = 'Remplissez tous les champs.';
        header('Location: index.php?mode=login'); exit;
    }

    $u = getDB()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $u->execute([$email]);
    $user = $u->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Email ou mot de passe incorrect.';
        header('Location: index.php?mode=login'); exit;
    }

    $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'],
                         'surname' => $user['surname'], 'email' => $user['email']];
    header('Location: formulaire.php'); exit;
}

// ── Register ──────────────────────────────────────────────────────
function handleRegister(): void
{
    $name     = trim($_POST['name']     ?? '');
    $surname  = trim($_POST['surname']  ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (!$name || !$surname || !$email || !$password) {
        $_SESSION['error'] = 'Remplissez tous les champs.';
        header('Location: index.php?mode=register'); exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Email invalide.';
        header('Location: index.php?mode=register'); exit;
    }

    $db    = getDB();
    $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$email]);

    if ($check->fetch()) {
        $_SESSION['error'] = 'Cet email est déjà utilisé.';
        header('Location: index.php?mode=register'); exit;
    }

    $db->prepare('INSERT INTO users (name, surname, email, password) VALUES (?,?,?,?)')
       ->execute([$name, $surname, $email, password_hash($password, PASSWORD_DEFAULT)]);

    $_SESSION['success'] = 'Compte créé ! Connectez-vous.';
    header('Location: index.php?mode=login'); exit;
}

// ── Logout ────────────────────────────────────────────────────────
function handleLogout(): void
{
    session_destroy();
    header('Location: index.php?mode=login'); exit;
}
