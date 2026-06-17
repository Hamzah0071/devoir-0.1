<?php
session_start();
// prendre les donner grace au formulairs
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cv = null;
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM bdd_cv WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $edit_id, ':uid' => $user_id]);
    $cv = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cv) { header("Location: dashboard.php"); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre       = htmlspecialchars(trim($_POST['titre']));
    $contact     = htmlspecialchars(trim($_POST['contact']));
    $experience  = htmlspecialchars(trim($_POST['experience']));
    $competences = htmlspecialchars(trim($_POST['competences']));
    $metierav    = htmlspecialchars(trim($_POST['metierav']));
    $loisirs     = htmlspecialchars(trim($_POST['loisirs']));
    $photo_nom   = $cv ? $cv['photo'] : null;

    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $photo_nom = uniqid('photo_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo_nom);
        }
    }

    if ($edit_id) {
        $stmt = $pdo->prepare("UPDATE bdd_cv SET titre=:titre, contact=:contact, experience=:experience, competences=:competences, metierav=:metierav, loisirs=:loisirs, photo=:photo WHERE id=:id AND user_id=:uid");
        $stmt->execute([':titre'=>$titre,':contact'=>$contact,':experience'=>$experience,':competences'=>$competences,':metierav'=>$metierav,':loisirs'=>$loisirs,':photo'=>$photo_nom,':id'=>$edit_id,':uid'=>$user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO bdd_cv (user_id, titre, contact, experience, competences,metierav, loisirs, photo) VALUES (:uid,:titre,:contact,:experience,:competences,:metierav,:loisirs,:photo)");
        $stmt->execute
        ([':uid'=>$user_id,
        ':titre'=>$titre,
        ':contact'=>$contact,
        ':experience'=>$experience,
        ':competences'=>$competences,
        ':metierav'=>$metierav,
        ':loisirs'=>$loisirs,
        ':photo'=>$photo_nom]);
        $edit_id = $pdo->lastInsertId();
    }

    header("Location: cv_voir.php?id=$edit_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $cv ? 'Modifier' : 'Nouveau' ?> CV</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container small">
    <a href="dashboard.php" class="retour">← Retour</a>
    <h1><?= $cv ? 'Modifier le CV' : 'Créer un CV' ?></h1>

    <form method="POST" enctype="multipart/form-data">

        <label>Titre du poste *</label>
        <input type="text" name="titre" value="<?= $cv ? htmlspecialchars($cv['titre']) : '' ?>" placeholder="Ex: Développeur Web" required>

        <label>Photo de profil</label>
        <div class="photo-zone">
            <?php if ($cv && $cv['photo']): ?>
                <img src="uploads/<?= htmlspecialchars($cv['photo']) ?>" class="apercu-photo">
            <?php else: ?>
                <div class="apercu-photo placeholder">📷</div>
            <?php endif; ?>
            <input type="file" name="photo" accept="image/*">
        </div>

        <label>Contact (email)</label>
        <input type="text" name="contact" value="<?= $cv ? htmlspecialchars($cv['contact']) : '' ?>" placeholder="Ex: jean@mail.com | 033 12 234 56">
        <!-- <input type="number" name="contact" value=" $cv ? htmlspecialchars($cv['contact']) : '' ?>" placeholder="Ex: jean@mail.com | 06 12 34 56 78"> -->

        <label>Expériences professionnelles</label>
        <textarea name="experience" rows="5" placeholder="Ex: 2022-2024 : Développeur chez X..."><?= $cv ? htmlspecialchars($cv['experience']) : '' ?></textarea>

        <label>Compétences</label>
        <textarea name="competences" rows="4" placeholder="Ex: PHP, MySQL, HTML, CSS..."><?= $cv ? htmlspecialchars($cv['competences']) : '' ?></textarea>

        <label>Metier d'avenir </label>
        <textarea name="metierav" rows="4" placeholder=""><?= $cv ? htmlspecialchars($cv['metierav']) : '' ?></textarea>

        <label>Loisirs</label>
        <textarea name="loisirs" rows="3" placeholder="Ex: Lecture, Sport, Voyage..."><?= $cv ? htmlspecialchars($cv['loisirs']) : '' ?></textarea>

        <button type="submit"><?= $cv ? 'Enregistrer' : 'Créer le CV' ?></button>
    </form>
</div>
</body>
</html>
