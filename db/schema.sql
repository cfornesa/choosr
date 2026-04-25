-- ALTER TABLE: Add is_featured and tags to artworks
-- NOTE: The fields are already included in the CREATE TABLE above.
-- If the table already exists on your server, run these ALTER TABLE
-- statements manually via phpMyAdmin or mysql CLI:
-- ============================================================

-- ALTER TABLE `artworks` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_public`;
-- ALTER TABLE `artworks` ADD COLUMN `tags` VARCHAR(255) NULL AFTER `description`;
-- ALTER TABLE `artworks` ADD INDEX `idx_artworks_public_featured` (`is_public`, `is_featured`, `created_at`);
=======
-- ============================================================
-- ALTER TABLE: Add is_featured and tags to artworks
-- NOTE: The fields are already included in the CREATE TABLE above.
-- If the table already exists on your server, run these ALTER TABLE
-- statements manually via phpMyAdmin or mysql CLI:
-- ============================================================

-- ALTER TABLE `artworks` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_public`;
-- ALTER TABLE `artworks` ADD COLUMN `tags` VARCHAR(255) NULL AFTER `description`;
-- ALTER TABLE `artworks` ADD INDEX `idx_artworks_public_featured` (`is_public`, `is_featured`, `created_at`);

-- ============================================================
-- ALTER TABLE: Add mode and visual_dimensions for Manual mode support
-- NOTE: The fields are already included in the CREATE TABLE above.
-- If the table already exists on your server, run these ALTER TABLE
-- statements manually via phpMyAdmin or mysql CLI:
-- ============================================================

-- ALTER TABLE `artworks` ADD COLUMN `mode` ENUM('manual', 'data') NOT NULL DEFAULT 'data' AFTER `is_featured`;
-- ALTER TABLE `artworks` ADD COLUMN `visual_dimensions` JSON NULL AFTER `rendering_config`;
-- ALTER TABLE `artworks` ADD INDEX `idx_artworks_mode` (`mode`);============================================================
-- Data-to-Art Studio — MySQL Schema
-- Session: 2026-04-23, Session 2
-- Engine: InnoDB | Charset: utf8mb4
-- Idempotent: all CREATE TABLE uses IF NOT EXISTS
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ------------------------------------------------------------
-- 1. users — accounts for authentication and gallery ownership
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT             NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(64)     NOT NULL,
    `email`         VARCHAR(255)    NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
        UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`(191)),
    KEY `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 2. datasets — normalized uploaded / preloaded / api_feed data
--    Includes sanitization status per C-04.
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `datasets` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`           INT             NULL,
    `source_type`       ENUM('upload', 'preloaded', 'api_feed') NOT NULL,
    `source_name`       VARCHAR(255)    NOT NULL,
    `original_filename` VARCHAR(255)    NULL,
    `file_size_bytes`   INT UNSIGNED    NULL,
    `mime_type`         VARCHAR(100)    NULL,
    `storage_path`      VARCHAR(500)    NULL,
    `row_count`         INT UNSIGNED    NOT NULL DEFAULT 0,
    `is_sanitized`      TINYINT(1)      NOT NULL DEFAULT 0,
    `sanitized_at`      DATETIME        NULL DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_datasets_user_id` (`user_id`),
    KEY `idx_datasets_source_type` (`source_type`),
    KEY `idx_datasets_created_at` (`created_at`),
    KEY `idx_datasets_is_sanitized` (`is_sanitized`),

    CONSTRAINT `fk_datasets_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 3. dataset_columns — column-level metadata per dataset
--    Powers the column mapper UI.
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `dataset_columns` (
    `id`            INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `dataset_id`    INT UNSIGNED        NOT NULL,
    `column_name`   VARCHAR(128)        NOT NULL,
    `display_name`  VARCHAR(128)        NOT NULL,
    `data_type`     ENUM('string', 'number', 'date', 'boolean', 'unknown')
                                        NOT NULL DEFAULT 'unknown',
    `sample_values` JSON                NULL,
    `column_order`  TINYINT UNSIGNED    NOT NULL,
    `is_mappable`   TINYINT(1)          NOT NULL DEFAULT 1,
    `created_at`    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_dataset_columns_dataset_id` (`dataset_id`),
    KEY `idx_dataset_columns_column_order` (`column_order`),

    CONSTRAINT `fk_dataset_columns_dataset_id`
        FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 4. art_styles — enumerated rendering modes (extensible)
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `art_styles` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `style_key`     VARCHAR(64)     NOT NULL,
    `display_name`  VARCHAR(128)    NOT NULL,
    `description`   TEXT            NULL,
    `default_config` JSON           NULL,
    `preview_svg`   TEXT            NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_art_styles_style_key` (`style_key`),
    KEY `idx_art_styles_is_active_sort_order` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 5. artworks — saved artwork state with mappings, style,
--    palette, and rendering configuration.
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `artworks` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`          INT             NOT NULL,
    `dataset_id`       INT UNSIGNED    NULL,
    `art_style_id`     INT UNSIGNED    NOT NULL,
    `title`            VARCHAR(255)    NOT NULL,
    `description`      TEXT            NULL,
    `column_mapping`   JSON            NOT NULL,
    `palette_config`   JSON            NOT NULL,
    `rendering_config` JSON            NULL,
    `is_public`        TINYINT(1)      NOT NULL DEFAULT 0,
    `is_featured`      TINYINT(1)      NOT NULL DEFAULT 0,
    `mode`            ENUM('manual', 'data') NOT NULL DEFAULT 'data',
    `visual_dimensions` JSON           NULL,
    `tags`             VARCHAR(255)    NULL,
    `thumbnail_path`   VARCHAR(500)    NULL,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_artworks_user_id` (`user_id`),
    KEY `idx_artworks_dataset_id` (`dataset_id`),
    KEY `idx_artworks_art_style_id` (`art_style_id`),
    KEY `idx_artworks_is_public_created_at` (`is_public`, `created_at`),
    KEY `idx_artworks_public_featured` (`is_public`, `is_featured`, `created_at`),
    KEY `idx_artworks_created_at` (`created_at`),

    CONSTRAINT `fk_artworks_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE,

    CONSTRAINT `fk_artworks_dataset_id`
        FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`)
        ON DELETE SET NULL,

    CONSTRAINT `fk_artworks_art_style_id`
        FOREIGN KEY (`art_style_id`) REFERENCES `art_styles` (`id`)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 6. api_cache — cached API feed responses with TTL
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `api_cache` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `source_url`      VARCHAR(1000)   NOT NULL,
    `source_name`     VARCHAR(255)    NOT NULL,
    `response_data`   JSON            NOT NULL,
    `cached_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`      DATETIME        NOT NULL,
    `etag`            VARCHAR(255)    NULL,
    `last_modified`   VARCHAR(255)    NULL,
    `http_status`     SMALLINT        NOT NULL DEFAULT 200,
    `access_count`    INT UNSIGNED    NOT NULL DEFAULT 0,
    `last_accessed_at` DATETIME       NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_api_cache_source_url` (`source_url`(191)),
    KEY `idx_api_cache_expires_at` (`expires_at`),
    KEY `idx_api_cache_cached_at` (`cached_at`),
    KEY `idx_api_cache_source_name` (`source_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Seed Data — art_styles
-- Three initial rendering modes as specified in DESIGN.md
-- ============================================================

INSERT IGNORE INTO `art_styles` (`style_key`, `display_name`, `description`, `default_config`, `sort_order`)
VALUES (
    'particle_field',
    'Particle Field',
    'Floating particles with properties mapped to data columns. Each particle\'s position, size, color, opacity, and velocity can be driven by dataset values.',
    '{"particleCount": 500, "minSize": 1, "maxSize": 8, "speed": 0.5, "trail": false}',
    1
);

INSERT IGNORE INTO `art_styles` (`style_key`, `display_name`, `description`, `default_config`, `sort_order`)
VALUES (
    'geometric_grid',
    'Geometric Grid',
    'Structured grid patterns with data-driven geometry. Cell shape, rotation, fill, and spacing respond to mapped columns.',
    '{"gridCols": 20, "gridRows": 20, "cellPadding": 2, "shape": "rect", "rotation": false}',
    2
);

INSERT IGNORE INTO `art_styles` (`style_key`, `display_name`, `description`, `default_config`, `sort_order`)
VALUES (
    'flowing_curves',
    'Flowing Curves',
    'Organic curve flows with data influence. Bezier control points, stroke weight, and color progression are driven by dataset values.',
    '{"curveCount": 50, "tension": 0.4, "strokeWeight": 1.5, "smoothing": 0.6}',
    3
);


-- ============================================================
-- ALTER TABLE: Add is_featured and tags to artworks
-- NOTE: The fields are already included in the CREATE TABLE above.
-- If the table already exists on your server, run these ALTER TABLE
-- statements manually via phpMyAdmin or mysql CLI:
-- ============================================================

-- ALTER TABLE `artworks` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_public`;
-- ALTER TABLE `artworks` ADD COLUMN `tags` VARCHAR(255) NULL AFTER `description`;
-- ALTER TABLE `artworks` ADD INDEX `idx_artworks_public_featured` (`is_public`, `is_featured`, `created_at`);
