<?php
// ══════════════════════════════════════════════════════════════════
//  CV GENERATOR — Génération du CV depuis les variables POST
// ══════════════════════════════════════════════════════════════════

session_start();

// ── Vérifier que le formulaire a bien été soumis ──────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: formulaire.php');
    exit;
}

// ── Récupérer et nettoyer les variables ───────────────────────────
$prenom      = trim($_POST['prenom']      ?? '');
$nom         = trim($_POST['nom']         ?? '');
$titre       = trim($_POST['titre']       ?? '');
$contact     = trim($_POST['contact']     ?? '');
$experience  = trim($_POST['experience']  ?? '');
$competences = trim($_POST['competences'] ?? '');
$loisirs     = trim($_POST['loisirs']     ?? '');

// ── Validation minimale ───────────────────────────────────────────
if (empty($prenom) || empty($nom) || empty($titre)) {
    $_SESSION['erreur'] = 'Prénom, Nom et Titre sont obligatoires.';
    $_SESSION['form']   = $_POST;
    header('Location: formulaire.php');
    exit;
}

// ── Gérer l'upload de photo ───────────────────────────────────────
$photoBase64 = null;  // On stocke la photo en base64 pour ne pas avoir de fichier sur le serveur

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

    $file         = $_FILES['photo'];
    $maxSize      = 5 * 1024 * 1024; // 5 Mo
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $mimeType     = $finfo->file($file['tmp_name']);

    if ($file['size'] > $maxSize) {
        $_SESSION['erreur'] = 'La photo ne doit pas dépasser 5 Mo.';
        $_SESSION['form']   = $_POST;
        header('Location: formulaire.php');
        exit;
    }

    if (!in_array($mimeType, $allowedMimes, true)) {
        $_SESSION['erreur'] = 'Format non autorisé (JPEG, PNG, GIF, WEBP).';
        $_SESSION['form']   = $_POST;
        header('Location: formulaire.php');
        exit;
    }

    // Encoder en base64 pour l'afficher directement dans le HTML
    $imageData   = file_get_contents($file['tmp_name']);
    $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
}

// ── Fonction utilitaire : texte multiligne → liste HTML ───────────
function lignesEnListe(string $texte, string $classeVide = 'cv-empty'): string
{
    $lignes = array_filter(
        array_map('trim', explode("\n", $texte))
    );

    if (empty($lignes)) {
        return '<li class="' . $classeVide . '">—</li>';
    }

    $html = '';
    foreach ($lignes as $ligne) {
        $html .= '            <li>' . htmlspecialchars($ligne) . '</li>' . "\n";
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV de <?= htmlspecialchars($prenom . ' ' . $nom) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;500;600;700&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <!-- ── Topbar ──────────────────────────────────────────────── -->
    <nav class="topbar">
        <span class="topbar-brand">CV<span class="accent">GEN</span></span>

        <div class="topbar-actions">
            <a href="formulaire.php" class="btn-outline">← Modifier</a>

            <button onclick="window.print()" class="btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5
                             a2 2 0 0 1 2-2h16
                             a2 2 0 0 1 2 2v5
                             a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Imprimer / PDF
            </button>
        </div>
    </nav>

    <!-- ── Aperçu du CV ────────────────────────────────────────── -->
    <main class="preview-wrapper">

        <div class="cv-document">

            <!-- ══ En-tête ══════════════════════════════════════ -->
            <div class="cv-header">

                <!-- Photo -->
                <div class="cv-photo-wrap">
                    <?php if ($photoBase64): ?>
                        <img
                            src="<?= $photoBase64 ?>"
                            alt="Photo de <?= htmlspecialchars($prenom) ?>"
                            class="cv-photo"
                        >
                    <?php else: ?>
                        <div class="cv-photo-placeholder">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8
                                         a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Identité -->
                <div class="cv-identity">
                    <h1 class="cv-name">
                        <?= htmlspecialchars(strtoupper($prenom . ' ' . $nom)) ?>
                    </h1>

                    <p class="cv-titre">
                        <?= htmlspecialchars($titre) ?>
                    </p>

                    <?php if (!empty($contact)): ?>
                        <p class="cv-contact">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                                         19.79 19.79 0 0 1-8.63-3.07
                                         A19.5 19.5 0 0 1 4.11 13
                                         a19.79 19.79 0 0 1-3.07-8.67
                                         A2 2 0 0 1 3 2.18h3
                                         a2 2 0 0 1 2 1.72
                                         c.127.96.361 1.903.7 2.81
                                         a2 2 0 0 1-.45 2.11L7.09 9.91
                                         a16 16 0 0 0 6 6l1.27-1.27
                                         a2 2 0 0 1 2.11-.45
                                         c.907.339 1.85.573 2.81.7
                                         A2 2 0 0 1 21 16.92z"/>
                            </svg>
                            <?= htmlspecialchars($contact) ?>
                        </p>
                    <?php endif; ?>
                </div>

            </div><!-- /.cv-header -->

            <!-- ══ Corps ═════════════════════════════════════════ -->
            <div class="cv-body">

                <!-- Colonne gauche -->
                <aside class="cv-aside">

                    <section class="cv-section">
                        <h2 class="cv-section-title">Compétences</h2>
                        <ul class="cv-list">
                            <?= lignesEnListe($competences) ?>
                        </ul>
                    </section>

                    <section class="cv-section">
                        <h2 class="cv-section-title">Loisirs</h2>
                        <ul class="cv-list">
                            <?= lignesEnListe($loisirs) ?>
                        </ul>
                    </section>

                </aside>

                <!-- Colonne principale -->
                <div class="cv-main-col">

                    <section class="cv-section">
                        <h2 class="cv-section-title">Expérience</h2>
                        <ul class="cv-list cv-list-experience">
                            <?= lignesEnListe($experience) ?>
                        </ul>
                    </section>

                </div>

            </div><!-- /.cv-body -->

        </div><!-- /.cv-document -->

    </main>

    <!-- ── Bouton flottant Enregistrer ─────────────────────────── -->
    <div class="preview-actions">
        <button onclick="window.print()" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5
                         a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Enregistrer
        </button>
    </div>

</body>
</html>
