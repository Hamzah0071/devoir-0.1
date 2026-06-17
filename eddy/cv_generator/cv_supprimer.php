<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM bdd_cv WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $id, ':uid' => $user_id]);

header("Location: dashboard.php");
exit;
?>
