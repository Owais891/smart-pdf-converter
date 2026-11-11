CREATE TABLE IF NOT EXISTS `social_accounts` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED NOT NULL,
    `provider`          ENUM('google','facebook') NOT NULL,
    `provider_user_id`  VARCHAR(191) NOT NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_social_provider_user` (`provider`, `provider_user_id`),
    UNIQUE KEY `uniq_social_user_provider` (`user_id`, `provider`),
    CONSTRAINT `fk_social_accounts_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
