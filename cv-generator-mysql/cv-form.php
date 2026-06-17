<?php
session_start();
require_once 'config.php';

// Sécurité : Redirection si non connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php?mode=login');
    exit;
}

$user    = $_SESSION['user'];
$error   = $_SESSION['cv_error']   ?? '';
$success = $_SESSION['cv_success'] ?? '';
unset($_SESSION['cv_error'], $_SESSION['cv_success']);

// Récupération des données (Session en cas d'erreur, sinon BDD)
if (isset($_SESSION['cv_form'])) {
    $f = $_SESSION['cv_form'];
    unset($_SESSION['cv_form']);
} else {
    // AJOUT : On récupère également le champ 'photo' depuis la base de données
    $stmt = getDB()->prepare('SELECT titre, contact, experience, competences, loisirs, photo FROM cvs WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $f = $stmt->fetch() ?: [];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Generator — Créer mon CV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="assets/bg.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <!-- ── Topbar ──────────────────────────────────────────────── -->
    <nav class="topbar">
        <span class="topbar-brand">CV<span class="accent">GEN</span></span>

        <div class="topbar-user">
            <span>Bonjour, <?= htmlspecialchars($user['name']) ?></span>

            <form action="auth.php" method="POST" style="display:inline">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn-logout">Déconnexion</button>
            </form>
            <form action="history.php" method="GET" style="display:inline">
                <button type="submit" class="btn-logout">Historique</button>
            </form>
        </div>
    </nav>

    <!-- ── Contenu principal ───────────────────────────────────── -->
    <main class="form-wrapper">

        <h1 class="page-title">
            Créez votre <span class="accent">CV</span>
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form
            action="save-cv.php"
            method="POST"
            enctype="multipart/form-data"
            class="cv-form"
            id="cvForm"
        >
            <div class="form-grid">

                <!-- ── Colonne gauche : champs texte ─────────── -->
                <div class="form-col">

                    <div class="field-group">
                        <label class="field-label">Loisirs</label>
                        <textarea
                            name="loisirs"
                            class="field-input field-textarea"
                            placeholder="Lecture, sport, musique… (un par ligne)"
                            rows="3"
                        ><?= htmlspecialchars($f['loisirs'] ?? '') ?></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Titre / Poste visé</label>
                        <input
                            type="text"
                            name="titre"
                            class="field-input"
                            placeholder="Développeur Web, Designer UX…"
                            value="<?= htmlspecialchars($f['titre'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label class="field-label">Contact</label>
                        <input
                            type="text"
                            name="contact"
                            class="field-input"
                            placeholder="ville@email.com"
                            value="<?= htmlspecialchars($f['contact'] ?? '') ?>"
                        >
                    </div>
                    <!-- <div class="field-group">
                        <label class="field-label">Contact (Téléphone uniquement)</label>
                        <input
                            type="tel"
                            name="contact"
                            class="field-input"
                            placeholder="Ex: 034XXXXXXX"
                            pattern="[0-9]*"
                            inputmode="numeric"
                            value($f['contact'] ?? '') "
                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                        >
                    </div> -->

                    <div class="field-group">
                        <label class="field-label">Expérience</label>
                        <textarea
                            name="experience"
                            class="field-input field-textarea"
                            placeholder="2022-2024 : Développeur chez Acme&#10;2020-2022 : Stagiaire …"
                            rows="5"
                        ><?= htmlspecialchars($f['experience'] ?? '') ?></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Compétences</label>
                        <textarea
                            name="competences"
                            class="field-input field-textarea"
                            placeholder="PHP, HTML/CSS, JavaScript… (un par ligne)"
                            rows="3"
                        ><?= htmlspecialchars($f['competences'] ?? '') ?></textarea>
                    </div>

                </div><!-- /.form-col -->

                <!-- ── Colonne droite : photo ─────────────────── -->
                <div class="form-col photo-col">

                    <div class="photo-upload-zone" id="photoZone">

                        <div class="photo-preview" id="photoPreview">
                            <?php if (!empty($f['photo']) && file_exists($f['photo'])): ?>
                                <img src="<?= htmlspecialchars($f['photo']) ?>" alt="Photo de profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8 a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <p class="photo-hint">
                            Votre photo
                            <small>pas plus de 10 Mo</small>
                        </p>

                        <input
                            type="file"
                            name="photo"
                            id="photoInput"
                            accept="image/*"
                            class="photo-file-input"
                        >
                    </div>

                </div><!-- /.photo-col -->

            </div><!-- /.form-grid -->

            <!-- ── Bouton submit ─────────────────────────────── -->
            <div class="form-actions">
                <button type="submit" class="btn-primary btn-submit">
                    <span>Générer mon CV</span>
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </form>

    </main>

    <script src="assets/app.js"></script>
</body>
</html>
