CREATE TABLE IF NOT EXISTS jobs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue        VARCHAR(80)    NOT NULL DEFAULT 'default',
    payload      JSON           NOT NULL,
    status       ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
    attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    error        TEXT,
    available_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at   TIMESTAMP      NULL,
    finished_at  TIMESTAMP      NULL,
    created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_queue_status (queue, status),
    INDEX idx_available    (available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
