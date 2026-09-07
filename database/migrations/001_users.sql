CREATE TABLE IF NOT EXISTS users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(180) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    role            ENUM('superadmin','admin','user') NOT NULL DEFAULT 'user',
    avatar          VARCHAR(255),
    email_verified  TINYINT(1)   NOT NULL DEFAULT 0,
    api_key         VARCHAR(64)  UNIQUE,
    credits         INT UNSIGNED NOT NULL DEFAULT 0,
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at   TIMESTAMP    NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role),
    INDEX idx_api   (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
