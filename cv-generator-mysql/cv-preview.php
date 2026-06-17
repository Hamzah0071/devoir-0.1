<?php
session_start();

// Sécurité : Redirection si non connecté ou pas de données
if (!isset($_SESSION['user'], $_SESSION['cv_data'])) {
    header('Location: index.php');
    exit;
}

$cv = $_SESSION['cv_data'];

// Transforme un texte multiligne en liste HTML <li>
function formatLines(string $text): string {
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    if (empty($lines)) return '<li class="cv-empty">—</li>';

    return implode('', array_map(fn($l) => '<li>' . htmlspecialchars($l) . '</li>', $lines));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Generator — Aperçu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="assets/bg.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;500;600;700&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <!-- ── Topbar ──────────────────────────────────────────────── -->
    <nav class="topbar">
        <span class="topbar-brand">CV<span class="accent">GEN</span></span>

        <div class="topbar-actions">
            <a href="cv-form.php" class="btn-outline">← Modifier</a>

            <button onclick="window.print()" class="btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16
                             a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Imprimer / PDF
            </button>

            <form action="auth.php" method="POST" style="display:inline">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn-logout">Déconnexion</button>
            </form>
        </div>
    </nav>

    <!-- ── Aperçu du document ──────────────────────────────────── -->
    <main class="preview-wrapper">

        <div class="cv-document" id="cvDocument">

            <!-- ══ En-tête ══════════════════════════════════════ -->
            <div class="cv-header">

                <!-- Photo -->
                <div class="cv-photo-wrap">
                    <?php if (!empty($cv['photo']) && file_exists(__DIR__ . '/' . $cv['photo'])): ?>
                        <img
                            src="<?= htmlspecialchars($cv['photo']) ?>"
                            alt="Photo de profil"
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
                        <?= htmlspecialchars(
                            strtoupper($cv['name'] . ' ' . $cv['surname'])
                        ) ?>
                    </h1>

                    <p class="cv-titre">
                        <?= htmlspecialchars($cv['titre']) ?>
                    </p>

                    <?php if (!empty($cv['contact'])): ?>
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
                            <?= htmlspecialchars($cv['contact']) ?>
                        </p>
                    <?php endif; ?>
                </div>

            </div><!-- /.cv-header -->

            <!-- ══ Corps du CV ════════════════════════════════════ -->
            <div class="cv-body">

                <!-- Colonne gauche : Compétences + Loisirs -->
                <aside class="cv-aside">

                    <section class="cv-section">
                        <h2 class="cv-section-title">Compétences</h2>
                        <ul class="cv-list">
                            <?= formatLines($cv['competences']) ?>
                        </ul>
                    </section>

                    <section class="cv-section">
                        <h2 class="cv-section-title">Loisirs</h2>
                        <ul class="cv-list">
                            <?= formatLines($cv['loisirs']) ?>
                        </ul>
                    </section>

                </aside>

                <!-- Colonne principale : Expérience -->
                <div class="cv-main-col">

                    <section class="cv-section">
                        <h2 class="cv-section-title">Expérience</h2>
                        <ul class="cv-list cv-list-experience">
                            <?= formatLines($cv['experience']) ?>
                        </ul>
                    </section>

                </div><!-- /.cv-main-col -->

            </div><!-- /.cv-body -->

        </div><!-- /.cv-document -->

    </main>

    <!-- ── Bouton Enregistrer flottant ─────────────────────────── -->
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

    <script src="assets/app.js"></script>
</body>
</html>
