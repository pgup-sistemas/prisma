CREATE TABLE IF NOT EXISTS scans (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    qr_id      BIGINT UNSIGNED NOT NULL,
    ip         VARCHAR(45),
    ip_hash    CHAR(64)    NULL COMMENT 'SHA-256 do IP (anonimizado para LGPD)',
    user_agent VARCHAR(512),
    referer    VARCHAR(512),
    country    CHAR(2),
    city       VARCHAR(100),
    device     ENUM('desktop','mobile','tablet','bot','unknown') DEFAULT 'unknown',
    scanned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (qr_id) REFERENCES qrcodes(id) ON DELETE CASCADE,
    INDEX idx_qr_id   (qr_id),
    INDEX idx_scanned (scanned_at),
    INDEX idx_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
