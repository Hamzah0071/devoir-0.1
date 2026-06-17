<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM bdd_cv WHERE user_id = :uid ORDER BY updated_at DESC");
$stmt->execute([':uid' => $user_id]);
$cvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes CV</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <header class="dash-header">
        <h1>Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h1>
        <a href="logout.php" class="btn-small">Déconnexion</a>
    </header>

    <div class="dash-top">
        <h2>Mes CV (<?= count($cvs) ?>)</h2>
        <a href="cv_form.php" class="btn">+ Nouveau CV</a>
    </div>

    <?php if (empty($cvs)): ?>
        <p class="vide">Aucun CV pour l'instant. Créez-en un !</p>
    <?php else: ?>
        <div class="cv-liste">
            <?php foreach ($cvs as $cv): ?>
            <div class="cv-card">
                <?php if ($cv['photo']): ?>
                    <img src="uploads/<?= htmlspecialchars($cv['photo']) ?>" class="cv-photo-mini">
                <?php else: ?>
                    <div class="cv-photo-mini placeholder">📷</div>
                <?php endif; ?>

                <div class="cv-info">
                    <h3><?= htmlspecialchars($cv['titre']) ?></h3>
                    <small>Modifié le <?= date('d/m/Y', strtotime($cv['updated_at'])) ?></small>
                </div>

                <div class="cv-actions">
                    <a href="cv_voir.php?id=<?= $cv['id'] ?>">Voir</a>
                    <a href="cv_form.php?id=<?= $cv['id'] ?>">Modifier</a>
                    <a href="cv_supprimer.php?id=<?= $cv['id'] ?>" onclick="return confirm('Supprimer ce CV ?')">Supprimer</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
