-- Raceverse DB
CREATE DATABASE IF NOT EXISTS simhub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE simhub;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  verification_token VARCHAR(100) DEFAULT NULL,
  email_verified_at DATETIME DEFAULT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  subscription_plan VARCHAR(64) DEFAULT NULL, -- 'Raceverse Pro'
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

INSERT INTO tracks (id, game_id, name) VALUES
  (1, 1, 'Le Mans Circuit de la Sarthe'),
  (2, 1, 'Spa-Francorchamps'),
  (3, 1, 'Monza'),
  (4, 1, 'Silverstone'),
  (5, 1, 'Sebring International Raceway')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO cars (id, game_id, category_id, name) VALUES
  (1, 1, 1, 'Toyota GR010 Hybrid'),
  (2, 1, 1, 'Porsche 963'),
  (3, 1, 1, 'Ferrari 499P'),
  (4, 1, 2, 'Oreca 07 LMP2'),
  (5, 1, 3, 'BMW M4 GT3')
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE IF NOT EXISTS hotlaps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  category_id INT NOT NULL,
  track_id INT NOT NULL,
  car_id INT NOT NULL,
  driver VARCHAR(120) NULL,
  lap_time_ms INT NOT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
  INDEX(game_id, category_id, track_id, lap_time_ms)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS setups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_id INT NOT NULL,
  category_id INT NOT NULL,
  track_id INT NOT NULL,
  car_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
  INDEX(game_id, category_id, track_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed base
INSERT INTO games (id,name) VALUES (1,'LMU') ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (id,name) VALUES (1,'Hypercar'),(2,'LMP2'),(3,'LMGT3')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Admin demo: admin@example.com / admin123
INSERT INTO users (email,password_hash,first_name,last_name,verification_token,email_verified_at,role,subscription_plan,subscription_active)
VALUES (
  'admin@example.com',
  '$2y$10$wH5iC7R0iHq1w1e9VvbDWO9sV.8Xv1VdOZC2kQd7t0OQv3RrQqU9K',
  'Admin',
  'User',
  NULL,
  NOW(),
  'admin',
  'Raceverse Pro',
  1
)
ON DUPLICATE KEY UPDATE role='admin';
