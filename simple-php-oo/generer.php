<?php
// ══════════════════════════════════════════════════════════════════
//  CV GENERATOR — Génération du CV (version OOP)
// ══════════════════════════════════════════════════════════════════

session_start();

require_once 'FormValidator.php';
require_once 'PhotoUploader.php';
require_once 'CvRenderer.php';

// ── Vérifier que le formulaire a bien été soumis ──────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: formulaire.php');
    exit;
}

// ── 1. Valider les données du formulaire ──────────────────────────
$validator = new CvFormValidator($_POST);

if (!$validator->validate()) {
    $_SESSION['erreur'] = $validator->getFirstError();
    $_SESSION['form']   = $_POST;
    header('Location: formulaire.php');
    exit;
}

// ── 2. Traiter la photo ───────────────────────────────────────────
$uploader = new PhotoUploader();

if (!$uploader->process($_FILES['photo'] ?? [])) {
    $_SESSION['erreur'] = $uploader->getError();
    $_SESSION['form']   = $_POST;
    header('Location: formulaire.php');
    exit;
}

// ── 3. Préparer le rendu du CV ────────────────────────────────────
$renderer = new CvRenderer(
    $validator->getData(),
    $uploader->getBase64()
);

$cvHtml    = $renderer->render();
$fullName  = $renderer->getFullName();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV de <?= htmlspecialchars($fullName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;500;600;700&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <nav class="topbar">
        <span class="topbar-brand">CV<span class="accent">GEN</span></span>
        <div class="topbar-actions">
            <a href="formulaire.php" class="btn-outline">← Modifier</a>
            <button onclick="window.print()" class="btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5 a2 2 0 0 1 2-2h16 a2 2 0 0 1 2 2v5 a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Imprimer / PDF
            </button>
        </div>
    </nav>

    <main class="preview-wrapper">
        <?= $cvHtml ?>
    </main>

    <div class="preview-actions">
        <button onclick="window.print()" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5 a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Enregistrer
        </button>
    </div>

</body>
</html>
