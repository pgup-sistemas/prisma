CREATE TABLE IF NOT EXISTS hub_blocks (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hub_id     BIGINT UNSIGNED NOT NULL,
    type       ENUM(
                   'link','group','whatsapp','social','map',
                   'schedule','catalog','video','contact_form'
               ) NOT NULL,
    title      VARCHAR(200),
    blocks_json JSON NOT NULL COMMENT 'dados especificos do tipo',
    position   INT UNSIGNED NOT NULL DEFAULT 0,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hub_id) REFERENCES hub_pages(id) ON DELETE CASCADE,
    INDEX idx_hub      (hub_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
