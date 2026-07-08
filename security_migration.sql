-- ============================================================
-- Security Migration for Crispy Bytes Fast Food Restaurant
-- Run this ONCE in phpMyAdmin > food_db > SQL tab
-- ============================================================

USE food_db;

-- 1. Add brute-force protection columns to users table
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS login_attempts  INT      NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS last_attempt_at DATETIME NULL;

-- 2. Add brute-force protection columns to admins table
ALTER TABLE admins
    ADD COLUMN IF NOT EXISTS login_attempts  INT      NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS last_attempt_at DATETIME NULL;

-- 3. Create login_logs audit table
CREATE TABLE IF NOT EXISTS login_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NULL,
    user_type   ENUM('user','admin') NOT NULL DEFAULT 'user',
    ip_address  VARCHAR(45) NOT NULL,
    status      ENUM('success','failed') NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_ip (ip_address),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. View: suspicious IPs (3+ failures in last 15 min)
CREATE OR REPLACE VIEW suspicious_ips AS
    SELECT ip_address, COUNT(*) AS failed_count, MAX(created_at) AS last_attempt
    FROM login_logs
    WHERE status = 'failed' AND created_at >= NOW() - INTERVAL 15 MINUTE
    GROUP BY ip_address HAVING failed_count >= 3
    ORDER BY failed_count DESC;

SELECT 'Security migration completed!' AS result;
