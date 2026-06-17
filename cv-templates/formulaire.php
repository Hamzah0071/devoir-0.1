<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user'])) { header('Location: index.php?mode=login'); exit; }

$user  = $_SESSION['user'];
$error = $_SESSION['cv_error'] ?? '';
unset($_SESSION['cv_error']);

// Pré-charger le CV existant depuis la BDD
$db   = getDB();
$stmt = $db->prepare('SELECT * FROM cvs WHERE user_id = ? LIMIT 1');
$stmt->execute([$user['id']]);
$cv   = $stmt->fetch() ?: [];

// Priorité : retour erreur > BDD > vide
$f = $_SESSION['cv_form'] ?? $cv;
unset($_SESSION['cv_form']);

// Templates disponibles
$templates = [
    'classic' => [
        'label' => 'Classic',
        'desc'  => 'Photo + bandeau noir, 2 colonnes',
        'icon'  => '🗂️',
    ],
    'modern'  => [
        'label' => 'Modern',
        'desc'  => 'Barre latérale rouge, minimaliste',
        'icon'  => '⚡',
    ],
    'elegant' => [
        'label' => 'Élégant',
        'desc'  => 'Centré, typographie large',
        'icon'  => '✦',
    ],
];

$selectedTpl = $f['template'] ?? 'classic';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Generator — Formulaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;600;700&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ── Topbar ────────────────────────────────────────────────────── -->
<nav class="topbar">
    <span class="topbar-brand">CV<span class="accent">GEN</span></span>
    <div class="topbar-actions">
        <span class="topbar-user">Bonjour, <?= htmlspecialchars($user['name']) ?></span>
        <form action="auth.php" method="POST" style="display:inline">
            <input type="hidden" name="action" value="logout">
            <button class="btn-logout">Déconnexion</button>
        </form>
    </div>
</nav>

<main class="page-wrapper">

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="generer.php" method="POST" enctype="multipart/form-data" id="cvForm">

        <div class="editor-layout">

            <!-- ════════════════════════════════════════════════
                 PANNEAU GAUCHE — Saisie + choix template
            ═════════════════════════════════════════════════ -->
            <div class="editor-panel">

                <h2 class="panel-title">Informations</h2>

                <!-- Choix du template -->
                <div class="template-picker">
                    <?php foreach ($templates as $key => $tpl): ?>
                        <label class="tpl-card <?= $selectedTpl === $key ? 'active' : '' ?>"
                               data-tpl="<?= $key ?>">
                            <input type="radio" name="template"
                                   value="<?= $key ?>"
                                   <?= $selectedTpl === $key ? 'checked' : '' ?>>
                            <span class="tpl-icon"><?= $tpl['icon'] ?></span>
                            <span class="tpl-label"><?= $tpl['label'] ?></span>
                            <span class="tpl-desc"><?= $tpl['desc'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="divider"></div>

                <!-- Identité -->
                <div class="form-row-2">
                    <div class="field-group">
                        <label class="field-label">Prénom</label>
                        <input type="text" name="prenom" class="field-input live"
                               data-target="cv-prenom"
                               placeholder="Jean"
                               value="<?= htmlspecialchars($f['prenom'] ?? $user['name']) ?>"
                               required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Nom</label>
                        <input type="text" name="nom" class="field-input live"
                               data-target="cv-nom"
                               placeholder="Dupont"
                               value="<?= htmlspecialchars($f['nom'] ?? $user['surname']) ?>"
                               required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Titre / Poste visé</label>
                    <input type="text" name="titre" class="field-input live"
                           data-target="cv-titre"
                           placeholder="Développeur Web, Designer UX…"
                           value="<?= htmlspecialchars($f['titre'] ?? '') ?>"
                           required>
                </div>

                <div class="field-group">
                    <label class="field-label">Contact</label>
                    <input type="text" name="contact" class="field-input live"
                           data-target="cv-contact"
                           placeholder="+261 34 00 000 00 — jean@email.com"
                           value="<?= htmlspecialchars($f['contact'] ?? '') ?>">
                </div>

                <div class="divider"></div>

                <div class="field-group">
                    <label class="field-label">Expérience <small>(un par ligne)</small></label>
                    <textarea name="experience" class="field-input field-textarea live"
                              data-target="cv-experience"
                              placeholder="2022-2024 : Développeur chez Acme&#10;2020-2022 : Stagiaire …"
                              rows="5"><?= htmlspecialchars($f['experience'] ?? '') ?></textarea>
                </div>

                <div class="field-group">
                    <label class="field-label">Compétences <small>(un par ligne)</small></label>
                    <textarea name="competences" class="field-input field-textarea live"
                              data-target="cv-competences"
                              placeholder="PHP&#10;HTML / CSS&#10;JavaScript"
                              rows="4"><?= htmlspecialchars($f['competences'] ?? '') ?></textarea>
                </div>

                <div class="field-group">
                    <label class="field-label">Loisirs <small>(un par ligne)</small></label>
                    <textarea name="loisirs" class="field-input field-textarea live"
                              data-target="cv-loisirs"
                              placeholder="Lecture&#10;Sport&#10;Musique"
                              rows="3"><?= htmlspecialchars($f['loisirs'] ?? '') ?></textarea>
                </div>

                <div class="divider"></div>

                <!-- Photo -->
                <div class="field-group">
                    <label class="field-label">Photo <small>max 5 Mo</small></label>
                    <div class="photo-upload-zone">
                        <div class="photo-preview" id="photoPreview">
                            <?php if (!empty($f['photo']) && file_exists(__DIR__ . '/' . $f['photo'])): ?>
                                <img src="<?= htmlspecialchars($f['photo']) ?>" alt="Photo">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <p class="photo-hint">Cliquez pour choisir<small>JPEG, PNG, WEBP</small></p>
                        <input type="file" name="photo" id="photoInput"
                               accept="image/*" class="photo-file-input">
                    </div>
                </div>

                <button type="submit" class="btn-primary btn-submit">
                    <span>Générer & Sauvegarder</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>

            </div><!-- /.editor-panel -->

            <!-- ════════════════════════════════════════════════
                 PANNEAU DROIT — Aperçu live du CV
            ═════════════════════════════════════════════════ -->
            <div class="preview-panel">
                <div class="preview-label">Aperçu en direct</div>

                <!-- TEMPLATE : CLASSIC ─────────────────────── -->
                <div class="cv-preview tpl-preview" id="preview-classic"
                     style="<?= $selectedTpl !== 'classic' ? 'display:none' : '' ?>">

                    <div class="cvp-header cvp-header--dark">
                        <div class="cvp-photo-wrap">
                            <div class="cvp-photo" id="cvp-photo-classic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="cvp-identity">
                            <div class="cvp-name">
                                <span class="cv-prenom"><?= htmlspecialchars($f['prenom'] ?? $user['name']) ?></span>
                                <span> </span>
                                <span class="cv-nom"><?= htmlspecialchars($f['nom'] ?? $user['surname']) ?></span>
                            </div>
                            <div class="cvp-titre cv-titre"><?= htmlspecialchars($f['titre'] ?? '') ?></div>
                            <div class="cvp-contact cv-contact"><?= htmlspecialchars($f['contact'] ?? '') ?></div>
                        </div>
                    </div>

                    <div class="cvp-body cvp-body--2col">
                        <div class="cvp-aside">
                            <div class="cvp-section">
                                <div class="cvp-section-title">Compétences</div>
                                <div class="cv-competences cvp-lines"><?= nl2br(htmlspecialchars($f['competences'] ?? '')) ?></div>
                            </div>
                            <div class="cvp-section">
                                <div class="cvp-section-title">Loisirs</div>
                                <div class="cv-loisirs cvp-lines"><?= nl2br(htmlspecialchars($f['loisirs'] ?? '')) ?></div>
                            </div>
                        </div>
                        <div class="cvp-main">
                            <div class="cvp-section">
                                <div class="cvp-section-title">Expérience</div>
                                <div class="cv-experience cvp-lines"><?= nl2br(htmlspecialchars($f['experience'] ?? '')) ?></div>
                            </div>
                        </div>
                    </div>

                </div><!-- /#preview-classic -->

                <!-- TEMPLATE : MODERN ──────────────────────── -->
                <div class="cv-preview tpl-preview" id="preview-modern"
                     style="<?= $selectedTpl !== 'modern' ? 'display:none' : '' ?>">

                    <div class="cvp-modern-wrap">
                        <div class="cvp-modern-sidebar">
                            <div class="cvp-modern-photo">
                                <div class="cvp-photo" id="cvp-photo-modern">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="cvp-modern-name">
                                <span class="cv-prenom"><?= htmlspecialchars($f['prenom'] ?? $user['name']) ?></span><br>
                                <span class="cv-nom"><?= htmlspecialchars($f['nom'] ?? $user['surname']) ?></span>
                            </div>
                            <div class="cvp-modern-titre cv-titre"><?= htmlspecialchars($f['titre'] ?? '') ?></div>
                            <div class="cvp-modern-sep"></div>
                            <div class="cvp-section-title cvp-section-title--light">Contact</div>
                            <div class="cv-contact cvp-lines cvp-lines--light"><?= nl2br(htmlspecialchars($f['contact'] ?? '')) ?></div>
                            <div class="cvp-modern-sep"></div>
                            <div class="cvp-section-title cvp-section-title--light">Compétences</div>
                            <div class="cv-competences cvp-lines cvp-lines--light"><?= nl2br(htmlspecialchars($f['competences'] ?? '')) ?></div>
                            <div class="cvp-modern-sep"></div>
                            <div class="cvp-section-title cvp-section-title--light">Loisirs</div>
                            <div class="cv-loisirs cvp-lines cvp-lines--light"><?= nl2br(htmlspecialchars($f['loisirs'] ?? '')) ?></div>
                        </div>
                        <div class="cvp-modern-main">
                            <div class="cvp-section">
                                <div class="cvp-section-title">Expérience</div>
                                <div class="cv-experience cvp-lines"><?= nl2br(htmlspecialchars($f['experience'] ?? '')) ?></div>
                            </div>
                        </div>
                    </div>

                </div><!-- /#preview-modern -->

                <!-- TEMPLATE : ELEGANT ─────────────────────── -->
                <div class="cv-preview tpl-preview" id="preview-elegant"
                     style="<?= $selectedTpl !== 'elegant' ? 'display:none' : '' ?>">

                    <div class="cvp-elegant-wrap">
                        <div class="cvp-elegant-top">
                            <div class="cvp-photo cvp-photo--elegant" id="cvp-photo-elegant">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <div class="cvp-elegant-name">
                                <span class="cv-prenom"><?= htmlspecialchars($f['prenom'] ?? $user['name']) ?></span>
                                <span> </span>
                                <span class="cv-nom"><?= htmlspecialchars($f['nom'] ?? $user['surname']) ?></span>
                            </div>
                            <div class="cvp-elegant-titre cv-titre"><?= htmlspecialchars($f['titre'] ?? '') ?></div>
                            <div class="cvp-elegant-contact cv-contact"><?= htmlspecialchars($f['contact'] ?? '') ?></div>
                            <div class="cvp-elegant-line"></div>
                        </div>
                        <div class="cvp-elegant-body">
                            <div class="cvp-elegant-col">
                                <div class="cvp-section">
                                    <div class="cvp-section-title">Compétences</div>
                                    <div class="cv-competences cvp-lines"><?= nl2br(htmlspecialchars($f['competences'] ?? '')) ?></div>
                                </div>
                                <div class="cvp-section">
                                    <div class="cvp-section-title">Loisirs</div>
                                    <div class="cv-loisirs cvp-lines"><?= nl2br(htmlspecialchars($f['loisirs'] ?? '')) ?></div>
                                </div>
                            </div>
                            <div class="cvp-elegant-col cvp-elegant-col--wide">
                                <div class="cvp-section">
                                    <div class="cvp-section-title">Expérience</div>
                                    <div class="cv-experience cvp-lines"><?= nl2br(htmlspecialchars($f['experience'] ?? '')) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /#preview-elegant -->

            </div><!-- /.preview-panel -->

        </div><!-- /.editor-layout -->

    </form>

</main>

<script>
// ── Mise à jour live de l'aperçu ─────────────────────────────────
document.querySelectorAll('.live').forEach(input => {
    input.addEventListener('input', () => {
        const target = input.dataset.target;
        const val    = input.value;

        document.querySelectorAll('.' + target).forEach(el => {
            if (target === 'cv-experience' || target === 'cv-competences' || target === 'cv-loisirs') {
                el.innerHTML = val.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/\n/g,'<br>');
            } else {
                el.textContent = val;
            }
        });
    });
});

// ── Changement de template ────────────────────────────────────────
document.querySelectorAll('.tpl-card').forEach(card => {
    card.addEventListener('click', () => {
        const tpl = card.dataset.tpl;

        // Activer la carte
        document.querySelectorAll('.tpl-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        // Afficher le bon aperçu
        document.querySelectorAll('.tpl-preview').forEach(p => p.style.display = 'none');
        document.getElementById('preview-' + tpl).style.display = 'block';
    });
});

// ── Aperçu photo ─────────────────────────────────────────────────
document.getElementById('photoInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('Photo trop lourde (max 5 Mo).');
        this.value = ''; return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        // Panneau gauche
        const prev = document.getElementById('photoPreview');
        prev.innerHTML = `<img src="${e.target.result}" alt="Photo">`;

        // Tous les aperçus de templates
        ['classic','modern','elegant'].forEach(tpl => {
            const el = document.getElementById('cvp-photo-' + tpl);
            if (el) el.innerHTML = `<img src="${e.target.result}" alt="Photo">`;
        });
    };
    reader.readAsDataURL(file);
});
</script>

</body>
</html>
