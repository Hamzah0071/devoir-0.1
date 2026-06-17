-- ══════════════════════════════════════════════════════════════════
--  CV GENERATOR — Schéma MySQL
--  Importe ce fichier dans phpMyAdmin ou via :
--  mysql -u root -p nom_de_ta_base < database.sql
-- ══════════════════════════════════════════════════════════════════

-- ── Table des utilisateurs ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `surname`    VARCHAR(100) NOT NULL,
    `email`      VARCHAR(191) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Clefs et Index
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`)
) 
ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_unicode_ci;

-- ── Table des CV ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cvs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL,
    `titre`       VARCHAR(255) NOT NULL,
    `contact`     VARCHAR(255)          DEFAULT NULL,
    `experience`  TEXT                  DEFAULT NULL,
    `competences` TEXT                  DEFAULT NULL,
    `loisirs`     TEXT                  DEFAULT NULL,
    `photo`       VARCHAR(255)          DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clefs et Index
    PRIMARY KEY (`id`),
    KEY `fk_cv_user` (`user_id`),
    
    -- Relation : Liaison de sécurité entre les deux tables
    CONSTRAINT `fk_cv_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
) 
ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_unicode_ci;

-- ou avec une syntaxe plus concise :

-- Table des utilisateurs
-- CREATE TABLE users (
--     id         INT AUTO_INCREMENT PRIMARY KEY,
--     name       VARCHAR(100) NOT NULL,
--     surname    VARCHAR(100) NOT NULL,
--     email      VARCHAR(191) NOT NULL UNIQUE,
--     password   VARCHAR(255) NOT NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Table des CV
-- CREATE TABLE cvs (
--     id          INT AUTO_INCREMENT PRIMARY KEY,
--     user_id     INT NOT NULL,
--     titre       VARCHAR(255) NOT NULL,
--     contact     VARCHAR(255),
--     experience  TEXT,
--     competences TEXT,
--     loisirs     TEXT,
--     photo       VARCHAR(255),
--     created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
-- );