CREATE TABLE IF NOT EXISTS launcher_links (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED NOT NULL,
    source         ENUM('qrcode','shortlink','bookmark','manual') NOT NULL,
    source_id      BIGINT UNSIGNED NULL COMMENT 'id na tabela de origem',
    title          VARCHAR(300)   NOT NULL,
    url            TEXT           NOT NULL,
    icon           VARCHAR(100)   NULL COMMENT 'nome de icone Bootstrap ou URL',
    tags_json      JSON,
    roles_json     JSON           NULL COMMENT 'roles que veem este link',
    use_count      INT UNSIGNED   NOT NULL DEFAULT 0,
    last_used_at   TIMESTAMP      NULL,
    active         TINYINT(1)     NOT NULL DEFAULT 1,
    created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user   (user_id),
    INDEX idx_source (source, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
