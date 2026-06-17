<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT bdd_cv.*, utilisateur.name, utilisateur.surname 
    FROM bdd_cv 
    INNER JOIN utilisateur ON bdd_cv.user_id = utilisateur.id 
    WHERE bdd_cv.id = :id AND bdd_cv.user_id = :uid
");
$stmt->execute([':id' => $id, ':uid' => $user_id]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv) { header("Location: dashboard.php"); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CV — <?= htmlspecialchars($cv['name'] . ' ' . $cv['surname']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #e8e4df;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            padding: 30px 20px;
        }

        /* ── Barre actions ── */
        .top-bar {
            max-width: 900px;
            margin: 0 auto 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar a {
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #555;
            background: #fff;
            padding: 8px 18px;
            border-radius: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }

        .top-bar a:hover { background: #1a1a2e; color: #fff; }

        .top-bar .btn-modifier {
            background: #1a1a2e;
            color: #fff;
        }

        /* ── CV Page ── */
        .cv-page {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
            overflow: hidden;
            display: grid;
            grid-template-columns: 300px 1fr;
            min-height: 1100px;
        }

        /* ── Colonne gauche ── */
        .col-gauche {
            background: #1a1a2e;
            color: #fff;
            padding: 50px 32px;
            display: flex;
            flex-direction: column;
            gap: 36px;
        }

        /* Photo */
        .photo-wrapper {
            text-align: center;
        }

        .cv-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.15);
            display: block;
            margin: 0 auto;
        }

        .photo-placeholder {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            border: 4px solid rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto;
        }

        /* Nom dans colonne gauche */
        .cv-nom {
            text-align: center;
        }

        .cv-nom h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .cv-nom h2 {
            font-size: 13px;
            font-weight: 400;
            color: #a0a8c0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Séparateur */
        .sep {
            width: 40px;
            height: 2px;
            background: #c9a96e;
            margin: 0 auto;
        }

        /* Bloc sidebar */
        .sidebar-bloc h3 {
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #c9a96e;
            margin-bottom: 14px;
        }

        .sidebar-bloc p {
            font-size: 13px;
            color: #c8cfe0;
            line-height: 1.7;
        }

        /* ── Colonne droite ── */
        .col-droite {
            padding: 50px 44px;
            display: flex;
            flex-direction: column;
            gap: 36px;
            background: #fff;
        }

        /* En-tête droite */
        .cv-entete {
            padding-bottom: 28px;
            border-bottom: 1px solid #eee;
        }

        .cv-entete .tag {
            display: inline-block;
            background: #f0ece6;
            color: #c9a96e;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
        }

        .cv-entete h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.2;
        }

        /* Section droite */
        .section {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .section-titre {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 4px;
        }

        .section-titre h3 {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #1a1a2e;
        }

        .section-titre .ligne {
            flex: 1;
            height: 1px;
            background: #eee;
        }

        .section-content {
            font-size: 14px;
            color: #555;
            line-height: 1.8;
        }

        /* Tags compétences */
        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag-item {
            background: #f0ece6;
            color: #1a1a2e;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid #e0d8ce;
        }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .cv-page { grid-template-columns: 1fr; }
            .col-gauche { padding: 36px 24px; }
            .col-droite { padding: 36px 24px; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <a href="dashboard.php">← Retour</a>
    <a href="cv_form.php?id=<?= $cv['id'] ?>" class="btn-modifier">✏️ Modifier</a>
</div>

<div class="cv-page">

    <!-- COLONNE GAUCHE -->
    <div class="col-gauche">

        <!-- Photo -->
        <div class="photo-wrapper">
            <?php if ($cv['photo']): ?>
                <img src="uploads/<?= htmlspecialchars($cv['photo']) ?>" class="cv-photo">
            <?php else: ?>
                <div class="photo-placeholder">👤</div>
            <?php endif; ?>
        </div>

        <!-- Nom -->
        <div class="cv-nom">
            <h1><?= htmlspecialchars($cv['name'] . ' ' . $cv['surname']) ?></h1>
            <div class="sep"></div>
        </div>

        <!-- Contact -->
        <?php if ($cv['contact']): ?>
        <div class="sidebar-bloc">
            <h3>Contact</h3>
            <p><?= nl2br(htmlspecialchars($cv['contact'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Loisirs -->
        <?php if ($cv['loisirs']): ?>
        <div class="sidebar-bloc">
            <h3>Loisirs</h3>
            <p><?= nl2br(htmlspecialchars($cv['loisirs'])) ?></p>
        </div>
        <?php endif; ?>

    </div>

    <!-- COLONNE DROITE -->
    <div class="col-droite">

        <!-- En-tête -->
        <div class="cv-entete">
            <div class="tag">Curriculum Vitae</div>
            <h1><?= htmlspecialchars($cv['titre']) ?></h1>
        </div>

        <!-- Expériences -->
        <?php if ($cv['experience']): ?>
        <div class="section">
            <div class="section-titre">
                <h3>Expériences</h3>
                <div class="ligne"></div>
            </div>
            <div class="section-content">
                <?= nl2br(htmlspecialchars($cv['experience'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Compétences -->
        <?php if ($cv['competences']): ?>
        <div class="section">
            <div class="section-titre">
                <h3>Compétences</h3>
                <div class="ligne"></div>
            </div>
            <div class="tags">
                <?php
                $items = preg_split('/[,\n]+/', $cv['competences']);
                foreach ($items as $item):
                    $item = trim($item);
                    if ($item):
                ?>
                    <span class="tag-item"><?= htmlspecialchars($item) ?></span>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- metierv -->
        <?php if ($cv['metierav']): ?>
        <div class="section">
            <div class="section-titre">
                <h3>Metier d' avenir
                </h3>
                <div class="ligne"></div>
            </div>
            <div class="tags">
                <?php
                $items = preg_split('/[,\n]+/', $cv['metierav']);
                foreach ($items as $item):
                    $item = trim($item);
                    if ($item):
                ?>
                    <span class="tag-item"><?= htmlspecialchars($item) ?></span>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>