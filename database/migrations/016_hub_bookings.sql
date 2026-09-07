CREATE TABLE IF NOT EXISTS hub_bookings (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid             CHAR(36)      NOT NULL UNIQUE,
    hub_id           BIGINT UNSIGNED NOT NULL,
    block_id         BIGINT UNSIGNED NOT NULL,
    customer_name    VARCHAR(150)  NOT NULL,
    customer_contact VARCHAR(150)  NOT NULL,
    notes            TEXT,
    booking_date     DATE          NOT NULL,
    start_time       TIME          NOT NULL,
    end_time         TIME          NOT NULL,
    status           ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hub_id)   REFERENCES hub_pages(id)  ON DELETE CASCADE,
    FOREIGN KEY (block_id) REFERENCES hub_blocks(id) ON DELETE CASCADE,
    INDEX idx_hub_date (hub_id, booking_date),
    INDEX idx_block_date_status (block_id, booking_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
