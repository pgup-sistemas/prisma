CREATE TABLE IF NOT EXISTS bookmark_folders (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(200)    NOT NULL,
    parent_id  BIGINT UNSIGNED NULL,
    position   INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES bookmark_folders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookmarks (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    folder_id  BIGINT UNSIGNED NULL,
    title      VARCHAR(500)    NOT NULL,
    url        TEXT            NOT NULL,
    favicon    VARCHAR(512),
    health     ENUM('ok','broken','unknown') NOT NULL DEFAULT 'unknown',
    health_checked_at TIMESTAMP NULL,
    in_launcher TINYINT(1)     NOT NULL DEFAULT 0,
    position   INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (folder_id) REFERENCES bookmark_folders(id) ON DELETE SET NULL,
    INDEX idx_user    (user_id),
    INDEX idx_folder  (folder_id),
    INDEX idx_health  (health),
    INDEX idx_launcher (in_launcher)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
