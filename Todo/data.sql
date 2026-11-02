CREATE DATABASE IF NOT EXISTS `todo_db`;
USE `todo_db`;

CREATE TABLE IF NOT EXISTS `todos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('personal', 'shared') NOT NULL DEFAULT 'personal',
  `task_date` DATE NOT NULL,
  `task_name` VARCHAR(255) NOT NULL,
  `task_time` TIME NOT NULL,
  `notif_time` TIME DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `is_completed` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `todos` (`type`, `task_date`, `task_name`, `task_time`, `notif_time`, `photo`) VALUES
('personal', '2026-10-20', 'Latihan Basket', '09:00:00', '08:30:00', NULL),
('shared', '2026-10-21', 'Rapat Divisi Logistik', '13:00:00', '12:30:00', NULL);