CREATE TABLE IF NOT EXISTS password_resets (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(180) NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    expires_at TIMESTAMP    NOT NULL,
    used_at    TIMESTAMP    NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
