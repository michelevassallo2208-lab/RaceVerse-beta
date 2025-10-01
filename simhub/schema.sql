-- MetaSim DB
CREATE DATABASE IF NOT EXISTS simhub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE simhub;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','pro','guest') NOT NULL DEFAULT 'guest',
  subscription_plan VARCHAR(64) DEFAULT NULL, -- 'RaceVerse Pro'
  subscription_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS games (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tracks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  image_path VARCHAR(255) NULL,
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  category_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  image_path VARCHAR(255) NULL,
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hotlaps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  category_id INT NOT NULL,
  track_id INT NOT NULL,
  car_id INT NOT NULL,
  driver VARCHAR(120) NULL,
  lap_time_ms INT NOT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  setup_file VARCHAR(255) NULL,
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
  INDEX(game_id, category_id, track_id, lap_time_ms)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed base
INSERT INTO games (id,name) VALUES (1,'LMU') ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (id,name) VALUES (1,'Hypercar'),(2,'LMP2'),(3,'LMGT3')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO tracks (id, game_id, name)
VALUES
  (1, 1, 'Circuit de la Sarthe'),
  (2, 1, 'Sebring International Raceway')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO cars (id, game_id, category_id, name)
VALUES
  (1, 1, 1, 'Porsche 963'),
  (2, 1, 1, 'Toyota GR010 Hybrid'),
  (3, 1, 1, 'Cadillac V-Series.R')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO hotlaps (id, game_id, category_id, track_id, car_id, driver, lap_time_ms, recorded_at, setup_file)
VALUES
  (1, 1, 1, 1, 1, 'Elena Rossi', 221045, '2024-05-10 12:00:00', 'storage/setups/lmu_hypercar_sarthe.json'),
  (2, 1, 1, 1, 2, 'Marco Valli', 222310, '2024-05-08 17:30:00', NULL),
  (3, 1, 1, 1, 3, 'Jacob Miles', 223020, '2024-05-09 09:12:00', NULL)
ON DUPLICATE KEY UPDATE
  lap_time_ms = VALUES(lap_time_ms),
  recorded_at = VALUES(recorded_at),
  setup_file = VALUES(setup_file);

-- Admin demo: admin@example.com / admin123
INSERT INTO users (email,password_hash,role,subscription_plan,subscription_active)
VALUES ('admin@example.com', '$2y$10$wH5iC7R0iHq1w1e9VvbDWO9sV.8Xv1VdOZC2kQd7t0OQv3RrQqU9K', 'admin', 'RaceVerse Pro', 1)
ON DUPLICATE KEY UPDATE role='admin', subscription_plan='RaceVerse Pro', subscription_active=1;

-- Demo RaceVerse Pro member
INSERT INTO users (email,password_hash,role,subscription_plan,subscription_active)
VALUES ('pro@example.com', '$2y$10$wH5iC7R0iHq1w1e9VvbDWO9sV.8Xv1VdOZC2kQd7t0OQv3RrQqU9K', 'pro', 'RaceVerse Pro', 1)
ON DUPLICATE KEY UPDATE role='pro', subscription_plan='RaceVerse Pro', subscription_active=1;

-- Demo guest user
INSERT INTO users (email,password_hash,role)
VALUES ('guest@example.com', '$2y$10$wH5iC7R0iHq1w1e9VvbDWO9sV.8Xv1VdOZC2kQd7t0OQv3RrQqU9K', 'guest')
ON DUPLICATE KEY UPDATE role='guest';
