<?php
// ══════════════════════════════════════════════════════════════════
//  CV GENERATOR — Sauvegarde du CV en base MySQL
// ══════════════════════════════════════════════════════════════════

session_start();
require_once __DIR__ . '/config.php';

// Guard : doit être connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php?mode=login');
    exit; 
}

$user = $_SESSION['user'];

// ── Collecter et valider les champs ───────────────────────────────
$titre       = trim($_POST['titre']       ?? '');
$contact     = trim($_POST['contact']     ?? '');
$experience  = trim($_POST['experience']  ?? '');
$competences = trim($_POST['competences'] ?? '');
$loisirs     = trim($_POST['loisirs']     ?? '');

if (empty($titre)) {
    $_SESSION['cv_error'] = 'Le titre / poste visé est obligatoire.';
    $_SESSION['cv_form']  = $_POST;
    header('Location: cv-form.php');
    exit;
}

// ── Gérer l'upload de photo ───────────────────────────────────────
$photoPath = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

    $file    = $_FILES['photo'];
    $maxSize = 10 * 1024 * 1024; // 10 Mo

    if ($file['size'] > $maxSize) {
        $_SESSION['cv_error'] = 'La photo ne doit pas dépasser 10 Mo.';
        $_SESSION['cv_form']  = $_POST;
        header('Location: cv-form.php');
        exit;
    }

    // Vérification MIME réelle (pas juste l'extension)
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $mimeType     = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimes, true)) {
        $_SESSION['cv_error'] = 'Format non autorisé (JPEG, PNG, GIF, WEBP uniquement).';
        $_SESSION['cv_form']  = $_POST;
        header('Location: cv-form.php');
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename    = uniqid('photo_', true) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $photoPath = 'uploads/' . $filename;
    }
}

// ── INSERT ou UPDATE du CV en base ────────────────────────────────
$db = getDB();

// Vérifier si l'utilisateur a déjà un CV
$existing = $db->prepare(
    'SELECT id, photo FROM cvs WHERE user_id = ? LIMIT 1'
);
$existing->execute([$user['id']]);
$currentCv = $existing->fetch();

if ($currentCv) {

    // Supprimer l'ancienne photo si une nouvelle est uploadée
    if ($photoPath && !empty($currentCv['photo'])) {
        $oldFile = __DIR__ . '/' . $currentCv['photo'];
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

    // Construire la requête UPDATE dynamiquement
    $newPhoto = $photoPath ?? $currentCv['photo'];

    $update = $db->prepare(
        'UPDATE cvs
         SET titre       = ?,
             contact     = ?,
             experience  = ?,
             competences = ?,
             loisirs     = ?,
             photo       = ?
         WHERE user_id   = ?'
    );
    $update->execute([
        $titre,
        $contact,
        $experience,
        $competences,
        $loisirs,
        $newPhoto,
        $user['id'],
    ]);

    $photoPath = $newPhoto;

} else {

    // Première création du CV
    $insert = $db->prepare(
        'INSERT INTO cvs
             (user_id, titre, contact, experience, competences, loisirs, photo)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $insert->execute([
        $user['id'],
        $titre,
        $contact,
        $experience,
        $competences,
        $loisirs,
        $photoPath,
    ]);
}

// ── Passer les données à la page de prévisualisation ──────────────
$_SESSION['cv_data'] = [
    'name'        => $user['name'],
    'surname'     => $user['surname'],
    'titre'       => $titre,
    'contact'     => $contact,
    'experience'  => $experience,
    'competences' => $competences,
    'loisirs'     => $loisirs,
    'photo'       => $photoPath,
];

header('Location: cv-preview.php');
exit;
