CREATE TABLE IF NOT EXISTS link_clicks (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    link_id    BIGINT UNSIGNED NOT NULL,
    ip_hash    CHAR(64),
    country    CHAR(2),
    device     ENUM('desktop','mobile','tablet','bot','unknown') DEFAULT 'unknown',
    referer    VARCHAR(512),
    variant    VARCHAR(10)   NULL COMMENT 'para A/B',
    clicked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
    INDEX idx_link    (link_id),
    INDEX idx_clicked (clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
