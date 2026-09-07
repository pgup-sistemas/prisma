CREATE TABLE IF NOT EXISTS hub_pages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    slug            VARCHAR(80)  NOT NULL UNIQUE COMMENT 'prisma.app/hub/{slug}',
    title           VARCHAR(200) NOT NULL,
    bio             TEXT,
    avatar          VARCHAR(255),
    theme_color     CHAR(7)      NOT NULL DEFAULT '#2E86AB',
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
