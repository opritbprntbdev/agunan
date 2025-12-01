-- Tabel untuk menyimpan log lokasi user
CREATE TABLE IF NOT EXISTS user_location_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  username VARCHAR(100),
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL,
  accuracy FLOAT,
  logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_time (logged_at)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
