CREATE DATABASE IF NOT EXISTS `rkz_db`;
USE `rkz_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','User') NOT NULL DEFAULT 'User',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin', 'Admin'),
(2, 'user', 'user', 'User');

CREATE TABLE IF NOT EXISTS `Kwit_manual_h` (
  `st` varchar(10) DEFAULT NULL,
  `no_kwitansi` varchar(20) NOT NULL,
  `no_faktur` varchar(20) DEFAULT NULL,
  `terima_dari` varchar(100) DEFAULT NULL,
  `Jumlah` decimal(15,2) DEFAULT NULL,
  `keterangan1` varchar(255) DEFAULT NULL,
  `keterangan2` varchar(255) DEFAULT NULL,
  `user` varchar(50) DEFAULT NULL,
  `tgl_kwitansi` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`no_kwitansi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Kwit_manual_h` (`st`, `no_kwitansi`, `no_faktur`, `terima_dari`, `Jumlah`, `keterangan1`, `keterangan2`, `user`, `tgl_kwitansi`, `jam`) VALUES
('LNS', '26037', 'TR2611605', 'BAPAK ANDI', 2354700.00, 'PENGOBATAN DI RKZ SURABAYA', '', 'MITA', '2026-06-06', '11:51:37'),
('LNS', '26038', 'PS225006-8', 'NOAH FAIZAN ALRACESA', 1285200.00, 'PENGOBATAN DI RKZ SURABAYA TANGGAL 06/06/2026', '', 'MITA', '2026-06-06', '14:49:10'),
('LNS', '26039', 'FK022826', 'MITA', 1290400.00, 'PENGOBATAN DI RKZ SURABAYA', '', 'MITA', '2026-06-08', '13:12:26'),
('LNS', '26040', 'FK022827', 'RERE', 1741000.00, 'Biaya Fisioterapi TGL 02 JUNI 2026 s/d 05 JUNI 2026', 'di RS RKZ SURABAYA', 'RERE', '2026-06-08', '08:04:38');

CREATE TABLE IF NOT EXISTS `Kwit_manual_d` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_faktur` varchar(20) DEFAULT NULL,
  `Kd_brg` varchar(10) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jumlah` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `Kwit_manual_d` (`id`, `no_faktur`, `Kd_brg`, `nama`, `jumlah`) VALUES
(1, 'PS225006-8', '7', 'POLI KESEHATAN ANAK (PA0010)', 275000.00),
(2, 'PS225006-8', '1', 'KLINIK ANAK (12034817)', 871200.00),
(3, 'PS225006-8', '9', 'LABORATORIUM-POLI (12034819)', 139000.00),
(4, 'FK022827', '12', 'BEDAH', 1000000.00),
(5, 'FK022827', '5', 'CT SCAN', 741000.00);

CREATE TABLE IF NOT EXISTS `master` (
  `kode` int(11) NOT NULL AUTO_INCREMENT,
  `Nama` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `master` (`kode`, `Nama`) VALUES
(1, 'Laborat'), (2, 'ECG'), (3, 'RONTGEN'), (4, 'USG'), (5, 'CT SCAN'),
(6, 'Treadmil'), (7, 'Poliklinik'), (8, 'Poli Spesialis'), (9, 'Poli gigi'), (10, 'Gigi Palsu'),
(11, 'fisioterapi'), (12, 'bedah'), (13, 'farmasi'), (14, 'ugd'), (15, 'TU'), (16, 'Dokter');

CREATE TABLE IF NOT EXISTS `master_nomor` (
  `No_Kwt` varchar(20) NOT NULL,
  `No_Fkt` varchar(20) NOT NULL,
  PRIMARY KEY (`No_Kwt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `master_nomor` (`No_Kwt`, `No_Fkt`) VALUES
('26041', '121');
