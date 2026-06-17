<?php
// ══════════════════════════════════════════════════════════════════
//  CV GENERATOR — Formulaire (version OOP)
// ══════════════════════════════════════════════════════════════════

session_start();

// Récupérer les erreurs et anciennes valeurs si retour arrière
$erreur  = $_SESSION['erreur'] ?? '';
$anciens = $_SESSION['form']   ?? [];

unset($_SESSION['erreur'], $_SESSION['form']);

// Fonction helper pour pré-remplir les champs
function old(string $champ, array $anciens): string
{
    return htmlspecialchars($anciens[$champ] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Generator — Formulaire</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <nav class="topbar">
        <span class="topbar-brand">CV<span class="accent">GEN</span></span>
    </nav>

    <main class="form-wrapper">

        <h1 class="page-title">Créez votre <span class="accent">CV</span></h1>

        <?php if ($erreur): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form action="generer.php" method="POST" enctype="multipart/form-data" class="cv-form">
            <div class="form-grid">

                <!-- Colonne gauche -->
                <div class="form-col">

                    <div class="form-row">
                        <div class="field-group">
                            <label class="field-label">Prénom</label>
                            <input type="text" name="prenom" class="field-input"
                                   placeholder="Jean"
                                   value="<?= old('prenom', $anciens) ?>" required>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Nom</label>
                            <input type="text" name="nom" class="field-input"
                                   placeholder="Dupont"
                                   value="<?= old('nom', $anciens) ?>" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Titre / Poste visé</label>
                        <input type="text" name="titre" class="field-input"
                               placeholder="Développeur Web, Designer UX…"
                               value="<?= old('titre', $anciens) ?>" required>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Contact</label>
                        <input type="text" name="contact" class="field-input"
                               placeholder="+261 34 00 000 00 — jean@email.com"
                               value="<?= old('contact', $anciens) ?>">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Expérience</label>
                        <textarea name="experience" class="field-input field-textarea"
                                  placeholder="2022-2024 : Développeur chez Acme Corp&#10;2020-2022 : Stagiaire chez …"
                                  rows="5"><?= old('experience', $anciens) ?></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Compétences</label>
                        <textarea name="competences" class="field-input field-textarea"
                                  placeholder="PHP&#10;HTML / CSS&#10;JavaScript"
                                  rows="4"><?= old('competences', $anciens) ?></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Loisirs</label>
                        <textarea name="loisirs" class="field-input field-textarea"
                                  placeholder="Lecture&#10;Sport&#10;Musique"
                                  rows="3"><?= old('loisirs', $anciens) ?></textarea>
                    </div>

                </div>

                <!-- Colonne droite : photo -->
                <div class="form-col photo-col">
                    <div class="photo-upload-zone">
                        <div class="photo-preview" id="photoPreview">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8 a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <p class="photo-hint">
                            Cliquez pour choisir
                            <small>Votre photo — max 5 Mo</small>
                        </p>
                        <input type="file" name="photo" id="photoInput" accept="image/*" class="photo-file-input">
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary btn-submit">
                    <span>Générer mon CV</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </form>
    </main>

    <script>
        document.getElementById('photoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('La photo ne doit pas dépasser 5 Mo.');
                this.value = '';
                return;
            }
            const reader  = new FileReader();
            const preview = document.getElementById('photoPreview');
            reader.onload = function (e) {
                preview.innerHTML = '';
                const img = document.createElement('img');
                img.src   = e.target.result;
                img.alt   = 'Aperçu';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    </script>

</body>
</html>
