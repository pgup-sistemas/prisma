CREATE TABLE IF NOT EXISTS credit_transactions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    type        ENUM('purchase','consume','refund','bonus') NOT NULL,
    amount      INT             NOT NULL COMMENT 'positivo = entrada, negativo = saida',
    description VARCHAR(255),
    reference   VARCHAR(100)    NULL COMMENT 'uuid do QR-Logo, por exemplo',
    balance_after INT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
