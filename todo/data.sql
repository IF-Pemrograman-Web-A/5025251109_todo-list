CREATE DATABASE IF NOT EXISTS todo_e04;
USE todo_e04;

CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tgl DATE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    jam TIME NOT NULL,
    jam_notif TIME NOT NULL,
    foto VARCHAR(255) DEFAULT '',
    selesai TINYINT(1) NOT NULL DEFAULT 0
);

INSERT INTO todos (tgl, nama, jam, jam_notif, foto, selesai) VALUES
('2026-11-23', 'Rapat Koordinasi Tim', '09:00:00', '08:45:00', '', 1),
('2026-11-24', 'Review Endpoint REST API', '13:00:00', '12:45:00', '', 0),
('2026-11-25', 'Finalisasi Proyek E06', '16:00:00', '15:30:00', '', 0);
