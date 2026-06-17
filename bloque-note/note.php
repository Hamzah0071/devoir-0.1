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
    <title>Mes Notes</title>
    <style>
        :root {
            --bg-color: #f8f9fa;
            --sidebar-bg: #ffffff;
            --text-color: #212529;
            --accent-color: #4f46e5;
            --border-color: #e5e7eb;
            --placeholder-color: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            background-color: var(--bg-color);
            color: var(--text-color);
            overflow: hidden;
        }

        /* Barre latérale (Sidebar) */
        aside {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .logo {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-new {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-new:hover {
            background-color: #4338ca;
        }

        /* Zone principale d'écriture */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .status-bar {
            font-size: 0.85rem;
            color: var(--placeholder-color);
            margin-bottom: 20px;
            text-align: right;
        }

        .note-title {
            font-size: 2rem;
            font-weight: 700;
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            margin-bottom: 20px;
            color: var(--text-color);
        }

        .note-title::placeholder {
            color: var(--placeholder-color);
            opacity: 0.6;
        }

        .note-content {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            resize: none;
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text-color);
        }

        .note-content::placeholder {
            color: var(--placeholder-color);
            opacity: 0.6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            aside {
                display: none; /* Cache la sidebar sur mobile pour épurer */
            }
            main {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <aside>
        <div class="logo">
            ✍️ <span>NotePad</span>
        </div>
        <button class="btn-new" id="clear-btn">Effacer la note</button>
    </aside>

    <main>
        <div class="status-bar" id="status">Modifications enregistrées localement</div>
        <input type="text" class="note-title" id="note-title" placeholder="Titre sans nom" autocomplete="off">
        <textarea class="note-content" id="note-content" placeholder="Commencez à écrire vos pensées ici..."></textarea>
    </main>

    <script>
        const titleInput = document.getElementById('note-title');
        const contentInput = document.getElementById('note-content');
        const statusDiv = document.getElementById('status');
        const clearBtn = document.getElementById('clear-btn');

        // Charger les notes existantes au démarrage
        window.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('saved-title')) {
                titleInput.value = localStorage.getItem('saved-title');
            }
            if (localStorage.getItem('saved-content')) {
                contentInput.value = localStorage.getItem('saved-content');
            }
        });

        // Fonction pour sauvegarder les données
        function autoSave() {
            statusDiv.textContent = "Enregistrement en cours...";
            localStorage.setItem('saved-title', titleInput.value);
            localStorage.setItem('saved-content', contentInput.value);
            
            setTimeout(() => {
                statusDiv.textContent = "Modifications enregistrées localement";
            }, 500);
        }

        // Écouter les changements dans les champs de texte
        titleInput.addEventListener('input', autoSave);
        contentInput.addEventListener('input', autoSave);

        // Bouton pour réinitialiser la note
        clearBtn.addEventListener('click', () => {
            if (confirm('Voulez-vous vraiment effacer cette note ?')) {
                titleInput.value = '';
                contentInput.value = '';
                localStorage.removeItem('saved-title');
                localStorage.removeItem('saved-content');
                statusDiv.textContent = "Note effacée";
            }
        });
    </script>
</body>
</html>