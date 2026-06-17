-- CV GENERATOR — Schéma MySQL
-- mysql -u root -p nom_de_ta_base < database.sql

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `surname`    VARCHAR(100) NOT NULL,
    `email`      VARCHAR(191) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cvs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL,
    `template`    VARCHAR(20)  NOT NULL DEFAULT 'classic',
    `titre`       VARCHAR(255) NOT NULL,
    `contact`     VARCHAR(255) DEFAULT NULL,
    `experience`  TEXT         DEFAULT NULL,
    `competences` TEXT         DEFAULT NULL,
    `loisirs`     TEXT         DEFAULT NULL,
    `photo`       VARCHAR(255) DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_cv_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
