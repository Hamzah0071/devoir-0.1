<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user']))           { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: formulaire.php'); exit; }

$user = $_SESSION['user'];

// ── Variables ─────────────────────────────────────────────────────
$prenom      = trim($_POST['prenom']      ?? '');
$nom         = trim($_POST['nom']         ?? '');
$titre       = trim($_POST['titre']       ?? '');
$contact     = trim($_POST['contact']     ?? '');
$experience  = trim($_POST['experience']  ?? '');
$competences = trim($_POST['competences'] ?? '');
$loisirs     = trim($_POST['loisirs']     ?? '');
$template    = in_array($_POST['template'] ?? '', ['classic','modern','elegant'])
               ? $_POST['template'] : 'classic';

if (!$prenom || !$nom || !$titre) {
    $_SESSION['cv_error'] = 'Prénom, Nom et Titre sont obligatoires.';
    $_SESSION['cv_form']  = $_POST;
    header('Location: formulaire.php'); exit;
}

// ── Upload photo ──────────────────────────────────────────────────
$photoPath = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['photo'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);

    if ($file['size'] > 5*1024*1024 || !in_array($mime, $allowed)) {
        $_SESSION['cv_error'] = 'Photo invalide (JPEG/PNG/WEBP, max 5 Mo).';
        $_SESSION['cv_form']  = $_POST;
        header('Location: formulaire.php'); exit;
    }

    $dir  = __DIR__ . '/assets/uploads/';
    $name = uniqid('photo_') . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    move_uploaded_file($file['tmp_name'], $dir . $name);
    $photoPath = 'assets/uploads/' . $name;
}

// ── INSERT ou UPDATE en base ──────────────────────────────────────
$db      = getDB();
$current = $db->prepare('SELECT id, photo FROM cvs WHERE user_id = ? LIMIT 1');
$current->execute([$user['id']]);
$old     = $current->fetch();

if ($old) {
    // Supprimer ancienne photo si nouvelle uploadée
    if ($photoPath && $old['photo'] && file_exists(__DIR__ . '/' . $old['photo'])) {
        unlink(__DIR__ . '/' . $old['photo']);
    }
    $photoPath = $photoPath ?? $old['photo'];

    $db->prepare('UPDATE cvs SET template=?,titre=?,contact=?,experience=?,
                  competences=?,loisirs=?,photo=? WHERE user_id=?')
       ->execute([$template,$titre,$contact,$experience,$competences,$loisirs,$photoPath,$user['id']]);
} else {
    $db->prepare('INSERT INTO cvs (user_id,template,titre,contact,experience,competences,loisirs,photo)
                  VALUES (?,?,?,?,?,?,?,?)')
       ->execute([$user['id'],$template,$titre,$contact,$experience,$competences,$loisirs,$photoPath]);
}

// ── Fonction utilitaire : texte → liste <li> ──────────────────────
function toLi(string $text): string
{
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    if (!$lines) return '<li class="cv-empty">—</li>';
    return implode('', array_map(fn($l) => '<li>' . htmlspecialchars($l) . '</li>', $lines));
}

$photoSrc = $photoPath && file_exists(__DIR__ . '/' . $photoPath) ? $photoPath : null;
$fullName = strtoupper($prenom . ' ' . $nom);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV — <?= htmlspecialchars($fullName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;600;700&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ── Topbar ──────────────────────────────────────────────────── -->
<nav class="topbar">
    <span class="topbar-brand">CV<span class="accent">GEN</span></span>
    <div class="topbar-actions">
        <a href="formulaire.php" class="btn-outline">← Modifier</a>
        <button onclick="window.print()" class="btn-primary btn-sm">
            🖨 Imprimer / PDF
        </button>
    </div>
</nav>

<main class="preview-wrapper">

<?php if ($template === 'classic'): ?>
<!-- ══════════════════════════════════════════════════
     TEMPLATE CLASSIC
═══════════════════════════════════════════════════ -->
<div class="cv-document cv-classic">

    <div class="cv-header cv-header--dark">
        <div class="cv-photo-wrap">
            <?php if ($photoSrc): ?>
                <img src="<?= htmlspecialchars($photoSrc) ?>" class="cv-photo" alt="Photo">
            <?php else: ?>
                <div class="cv-photo-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
        <div class="cv-identity cv-identity--bordered">
            <h1 class="cv-name"><?= htmlspecialchars($fullName) ?></h1>
            <p class="cv-titre-tag"><?= htmlspecialchars($titre) ?></p>
            <?php if ($contact): ?>
                <p class="cv-contact-line">📞 <?= htmlspecialchars($contact) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="cv-body cv-body--2col">
        <aside class="cv-aside">
            <section class="cv-section">
                <h2 class="cv-section-title">Compétences</h2>
                <ul class="cv-list"><?= toLi($competences) ?></ul>
            </section>
            <section class="cv-section">
                <h2 class="cv-section-title">Loisirs</h2>
                <ul class="cv-list"><?= toLi($loisirs) ?></ul>
            </section>
        </aside>
        <div class="cv-main-col">
            <section class="cv-section">
                <h2 class="cv-section-title">Expérience</h2>
                <ul class="cv-list cv-list--exp"><?= toLi($experience) ?></ul>
            </section>
        </div>
    </div>

</div>

<?php elseif ($template === 'modern'): ?>
<!-- ══════════════════════════════════════════════════
     TEMPLATE MODERN
═══════════════════════════════════════════════════ -->
<div class="cv-document cv-modern">

    <div class="cv-modern-sidebar">
        <div class="cv-modern-photo-wrap">
            <?php if ($photoSrc): ?>
                <img src="<?= htmlspecialchars($photoSrc) ?>" class="cv-photo cv-photo--round" alt="Photo">
            <?php else: ?>
                <div class="cv-photo-placeholder cv-photo-placeholder--round">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
        <h1 class="cv-modern-name"><?= htmlspecialchars($fullName) ?></h1>
        <p class="cv-modern-titre"><?= htmlspecialchars($titre) ?></p>

        <?php if ($contact): ?>
            <div class="cv-modern-sep"></div>
            <h2 class="cv-section-title cv-section-title--light">Contact</h2>
            <p class="cv-modern-contact"><?= htmlspecialchars($contact) ?></p>
        <?php endif; ?>

        <div class="cv-modern-sep"></div>
        <h2 class="cv-section-title cv-section-title--light">Compétences</h2>
        <ul class="cv-list cv-list--light"><?= toLi($competences) ?></ul>

        <div class="cv-modern-sep"></div>
        <h2 class="cv-section-title cv-section-title--light">Loisirs</h2>
        <ul class="cv-list cv-list--light"><?= toLi($loisirs) ?></ul>
    </div>

    <div class="cv-modern-main">
        <section class="cv-section">
            <h2 class="cv-section-title">Expérience</h2>
            <ul class="cv-list cv-list--exp"><?= toLi($experience) ?></ul>
        </section>
    </div>

</div>

<?php else: ?>
<!-- ══════════════════════════════════════════════════
     TEMPLATE ELEGANT
═══════════════════════════════════════════════════ -->
<div class="cv-document cv-elegant">

    <div class="cv-elegant-top">
        <?php if ($photoSrc): ?>
            <img src="<?= htmlspecialchars($photoSrc) ?>" class="cv-photo cv-photo--elegant" alt="Photo">
        <?php else: ?>
            <div class="cv-photo-placeholder cv-photo-placeholder--elegant">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
        <?php endif; ?>
        <h1 class="cv-elegant-name"><?= htmlspecialchars($fullName) ?></h1>
        <p class="cv-elegant-titre"><?= htmlspecialchars($titre) ?></p>
        <?php if ($contact): ?>
            <p class="cv-elegant-contact"><?= htmlspecialchars($contact) ?></p>
        <?php endif; ?>
        <div class="cv-elegant-rule"></div>
    </div>

    <div class="cv-elegant-body">
        <div class="cv-elegant-col">
            <section class="cv-section">
                <h2 class="cv-section-title">Compétences</h2>
                <ul class="cv-list"><?= toLi($competences) ?></ul>
            </section>
            <section class="cv-section">
                <h2 class="cv-section-title">Loisirs</h2>
                <ul class="cv-list"><?= toLi($loisirs) ?></ul>
            </section>
        </div>
        <div class="cv-elegant-col cv-elegant-col--wide">
            <section class="cv-section">
                <h2 class="cv-section-title">Expérience</h2>
                <ul class="cv-list cv-list--exp"><?= toLi($experience) ?></ul>
            </section>
        </div>
    </div>

</div>
<?php endif; ?>

</main>

<!-- Bouton flottant -->
<div class="preview-actions">
    <button onclick="window.print()" class="btn-primary">
        💾 Enregistrer
    </button>
</div>

</body>
</html>
