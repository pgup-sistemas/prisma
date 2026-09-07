CREATE TABLE IF NOT EXISTS organizations (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid       CHAR(36)     NOT NULL UNIQUE,
    name       VARCHAR(120) NOT NULL,
    slug       VARCHAR(80)  NOT NULL UNIQUE,
    logo       VARCHAR(255),
    owner_id   BIGINT UNSIGNED NOT NULL,
    plan       ENUM('starter','business','whitelabel') NOT NULL DEFAULT 'starter',
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    settings   JSON,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_users (
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    role            ENUM('owner','admin','member') NOT NULL DEFAULT 'member',
    joined_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (organization_id, user_id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
