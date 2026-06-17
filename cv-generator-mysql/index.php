<?php
    session_start();

    // Redirect to CV form if already logged in
    if (isset($_SESSION['user'])) {
        header('Location: cv-form.php');
        exit;
    }

    $mode    = $_GET['mode'] ?? 'login';
    $error   = $_SESSION['error']   ?? '';
    $success = $_SESSION['success'] ?? '';

    unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Generator — <?= $mode === 'login' ? 'Connexion' : 'Inscription' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="assets/bg.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <main class="auth-wrapper">

        <!-- ── Tab switcher ─────────────────────────────────────── -->
        <div class="tab-switcher">
            <a href="?mode=login"
               class="tab <?= $mode === 'login'    ? 'active' : '' ?>">
                Se connecter
            </a>
            <a href="?mode=register"
               class="tab <?= $mode === 'register' ? 'active' : '' ?>">
                S'inscrire
            </a>
        </div>

        <!-- ── Card ─────────────────────────────────────────────── -->
        <div class="auth-card">

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

            <!-- ══ LOGIN ══ -->
            <?php if ($mode === 'login'): ?>

                <form action="auth.php" method="POST" class="auth-form">
                    <input type="hidden" name="action" value="login">

                    <div class="field-group">
                        <label class="field-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="field-input"
                            placeholder="votre@email.com"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label class="field-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="field-input"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            Se souvenir de moi
                        </label>
                    </div>

                    <button type="submit" class="btn-primary">
                        Se connecter
                    </button>

                    <p class="switch-link">
                        Pas de compte ?
                        <a href="?mode=register">S'inscrire</a>
                    </p>
                </form>

            <!-- ══ REGISTER ══ -->
            <?php else: ?>

                <form action="auth.php" method="POST" class="auth-form">
                    <input type="hidden" name="action" value="register">

                    <div class="field-group">
                        <label class="field-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="field-input"
                            placeholder="Prénom"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label class="field-label">Surname</label>
                        <input
                            type="text"
                            name="surname"
                            class="field-input"
                            placeholder="Nom de famille"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label class="field-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="field-input"
                            placeholder="votre@email.com"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label class="field-label">Mot de passe</label>
                        <input
                            type="password"
                            name="password"
                            class="field-input"
                            placeholder="••••••••"
                            required
                            minlength="6"
                        >
                    </div>

                    <button type="submit" class="btn-primary">
                        Register
                    </button>

                    <p class="switch-link">
                        Déjà un compte ?
                        <a href="?mode=login">Se connecter</a>
                    </p>
                </form>

            <?php endif; ?>

        </div><!-- /.auth-card -->

    </main>

    <script src="assets/app.js"></script>
</body>
</html>
