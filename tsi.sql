/*
 Navicat Premium Data Transfer

 Source Server         : MYSQL-LARAGON
 Source Server Type    : MySQL
 Source Server Version : 80403 (8.4.3)
 Source Host           : localhost:3306
 Source Schema         : tsi

 Target Server Type    : MySQL
 Target Server Version : 80403 (8.4.3)
 File Encoding         : 65001

 Date: 20/05/2025 14:28:48
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for master_admin
-- ----------------------------
DROP TABLE IF EXISTS `master_admin`;
CREATE TABLE `master_admin`  (
  `id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `login_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of master_admin
-- ----------------------------
INSERT INTO `master_admin` VALUES (1, 'Admin Perum TSI', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', '2025-05-20 07:18:14');

-- ----------------------------
-- Table structure for master_agenda
-- ----------------------------
DROP TABLE IF EXISTS `master_agenda`;
CREATE TABLE `master_agenda`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `lokasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `dibuat_oleh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of master_agenda
-- ----------------------------
INSERT INTO `master_agenda` VALUES (7, 'Sosialisasi AD/ART', '2025-05-18', '08:34:00', '09:33:00', 'Rumah pak mulyono', 'Pembahasan terkait ADART', 'sekertaris', '2025-05-18 07:34:15', '2025-05-18 07:34:15');

-- ----------------------------
-- Table structure for master_anggota_keluarga
-- ----------------------------
DROP TABLE IF EXISTS `master_anggota_keluarga`;
CREATE TABLE `master_anggota_keluarga`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `keluarga_id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nik` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `agama` enum('Islam','Kristen Protestan','Katolik','Hindu','Buddha','Konghucu','Lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status_perkawinan` enum('Belum Kawin','Kawin','Cerai Hidup','Cerai Mati') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `hubungan` enum('Kepala Keluarga','Istri','Anak','Orang Tua','Lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tgl_lahir` date NOT NULL,
  `jenis_kelamin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pekerjaan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `golongan_darah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `keluarga_id`(`keluarga_id` ASC) USING BTREE,
  CONSTRAINT `master_anggota_keluarga_ibfk_1` FOREIGN KEY (`keluarga_id`) REFERENCES `master_keluarga` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_anggota_keluarga
-- ----------------------------
INSERT INTO `master_anggota_keluarga` VALUES (11, 6, 'Testing ABC HHHH', '3318828773747575', 'Islam', 'Kawin', 'Kepala Keluarga', '2025-05-19', 'Laki-laki', 'Lainnya', 'O');
INSERT INTO `master_anggota_keluarga` VALUES (12, 6, 'Testing A', '3318828773747575', 'Islam', 'Kawin', 'Kepala Keluarga', '2025-05-20', 'Laki-laki', 'Petani', 'AB');
INSERT INTO `master_anggota_keluarga` VALUES (13, 7, 'Muhammad Firdausi', '3318828773747575', 'Islam', 'Belum Kawin', 'Kepala Keluarga', '2025-05-20', 'Perempuan', 'Ibu Rumah Tangga', 'B');
INSERT INTO `master_anggota_keluarga` VALUES (14, 7, 'Muhammad Firdausi Anak', '44331989813983918391', 'Islam', 'Belum Kawin', 'Kepala Keluarga', '2025-05-20', 'Laki-laki', 'Ibu Rumah Tangga', 'AB');
INSERT INTO `master_anggota_keluarga` VALUES (15, 7, 'minyak Goreng', '233114141', 'Kristen Protestan', 'Kawin', 'Kepala Keluarga', '2025-05-20', 'Laki-laki', 'Ibu Rumah Tangga', 'AB');

-- ----------------------------
-- Table structure for master_cicilan_pelunasan_bulan
-- ----------------------------
DROP TABLE IF EXISTS `master_cicilan_pelunasan_bulan`;
CREATE TABLE `master_cicilan_pelunasan_bulan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `cicilan_id` int NOT NULL,
  `bulan_tunggakan` date NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `cicilan_id`(`cicilan_id` ASC) USING BTREE,
  CONSTRAINT `master_cicilan_pelunasan_bulan_ibfk_1` FOREIGN KEY (`cicilan_id`) REFERENCES `master_detail_cicilan` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_cicilan_pelunasan_bulan
-- ----------------------------

-- ----------------------------
-- Table structure for master_detail_cicilan
-- ----------------------------
DROP TABLE IF EXISTS `master_detail_cicilan`;
CREATE TABLE `master_detail_cicilan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `pembayaran_id` int NOT NULL,
  `lama_cicilan` int NOT NULL,
  `total_cicilan` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pembayaran_id`(`pembayaran_id` ASC) USING BTREE,
  CONSTRAINT `master_detail_cicilan_ibfk_1` FOREIGN KEY (`pembayaran_id`) REFERENCES `master_pembayaran` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_detail_cicilan
-- ----------------------------

-- ----------------------------
-- Table structure for master_detail_pembayaran_bulanan
-- ----------------------------
DROP TABLE IF EXISTS `master_detail_pembayaran_bulanan`;
CREATE TABLE `master_detail_pembayaran_bulanan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `pembayaran_id` int NOT NULL,
  `bulan` date NOT NULL,
  `nominal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `master_detail_pembayaran_bulanan_ibfk_1`(`pembayaran_id` ASC) USING BTREE,
  CONSTRAINT `master_detail_pembayaran_bulanan_ibfk_1` FOREIGN KEY (`pembayaran_id`) REFERENCES `master_pembayaran` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_detail_pembayaran_bulanan
-- ----------------------------
INSERT INTO `master_detail_pembayaran_bulanan` VALUES (14, 9, '2025-05-01', '125000');

-- ----------------------------
-- Table structure for master_keluarga
-- ----------------------------
DROP TABLE IF EXISTS `master_keluarga`;
CREATE TABLE `master_keluarga`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomor_rumah` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `no_kk` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status_rumah` enum('Rumah Sendiri','Sewa/Kontrak','Musiman') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `provinsi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kota` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kecamatan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kelurahan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_kk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_keluarga
-- ----------------------------
INSERT INTO `master_keluarga` VALUES (6, 'Ruha 1', '3578240102160006', 'Sewa/Kontrak', 'DUKUH PAKIS 1/10', 'JAWA TIMUR', 'KOTA SURABAYA', 'GAYUNGAN', 'GAYUNGAN', '3578240102160006.png', '085748496135', '2025-05-19 08:45:40');
INSERT INTO `master_keluarga` VALUES (7, 'TSI Blok II‐3| TSI Blok II‐3A', '3578140101085638', 'Sewa/Kontrak', 'AMPEL RAHMAD 22', 'SUMATERA BARAT', 'KABUPATEN SOLOK', 'HILIRAN GUMANTI', 'SARIAK ALAHAN TIGO', '3578140101085638.jpeg', '085748496135', '2025-05-19 11:09:23');

-- ----------------------------
-- Table structure for master_koordinator_blok
-- ----------------------------
DROP TABLE IF EXISTS `master_koordinator_blok`;
CREATE TABLE `master_koordinator_blok`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_koordinator_blok
-- ----------------------------
INSERT INTO `master_koordinator_blok` VALUES (1, 'PAK DENNY (Raya 1‐8, Blok I‐II)', 'denny', NULL);
INSERT INTO `master_koordinator_blok` VALUES (2, 'KOORD PAK GALUH (Blok III‐IIIB)', 'galuh', NULL);
INSERT INTO `master_koordinator_blok` VALUES (3, 'KOORD PAK ARIS (Raya 9‐20, Blok IV)', 'aris', NULL);
INSERT INTO `master_koordinator_blok` VALUES (4, 'PAK AGUS PRI (Raya 21‐23, Blok V)', 'agus_pri', NULL);
INSERT INTO `master_koordinator_blok` VALUES (5, 'KOORD PAK RONI (Raya 23A‐30, Blok VI)', 'roni', NULL);
INSERT INTO `master_koordinator_blok` VALUES (6, 'PAK ZAINAL (Raya 31‐36, Blok VII)', 'zainal', NULL);
INSERT INTO `master_koordinator_blok` VALUES (7, 'PAK JULAIKAN (Raya 37‐50, Blok VIII)', 'julaikan', NULL);
INSERT INTO `master_koordinator_blok` VALUES (8, ' PAK ADI (Raya 51‐61, Blok IX‐XI)', 'adi', NULL);
INSERT INTO `master_koordinator_blok` VALUES (9, 'PAK ERIC (Blok XB)', 'eric', NULL);

-- ----------------------------
-- Table structure for master_partisipant
-- ----------------------------
DROP TABLE IF EXISTS `master_partisipant`;
CREATE TABLE `master_partisipant`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `agenda_id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ttd_base64` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hadir_pada` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_agenda_partisipan`(`agenda_id` ASC) USING BTREE,
  CONSTRAINT `fk_agenda_partisipan` FOREIGN KEY (`agenda_id`) REFERENCES `master_agenda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 27 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of master_partisipant
-- ----------------------------

-- ----------------------------
-- Table structure for master_pembayaran
-- ----------------------------
DROP TABLE IF EXISTS `master_pembayaran`;
CREATE TABLE `master_pembayaran`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `metode` enum('1_bulan','2_bulan','3_bulan','4_bulan','5_bulan','6_bulan','7_tahun','cicilan') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `bulan_mulai` date NULL DEFAULT NULL,
  `jumlah_bayar` int NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `pembayaran_via` enum('koordinator','transfer') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'koordinator',
  `bukti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status` enum('pending','verified','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `master_pembayaran_ibfk_1`(`user_id` ASC) USING BTREE,
  CONSTRAINT `master_pembayaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_pembayaran
-- ----------------------------
INSERT INTO `master_pembayaran` VALUES (9, 36, '1_bulan', '2025-05-01', 125000, 'Bayar bulan maret', '2025-05-19 14:25:26', 'koordinator', NULL, 'verified', NULL);

-- ----------------------------
-- Table structure for master_rumah
-- ----------------------------
DROP TABLE IF EXISTS `master_rumah`;
CREATE TABLE `master_rumah`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_koordinator` bigint NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 199 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of master_rumah
-- ----------------------------
INSERT INTO `master_rumah` VALUES (1, 1, 'Hikmah Store', 'Ruha 1');
INSERT INTO `master_rumah` VALUES (2, 1, NULL, 'Ruha 3a');
INSERT INTO `master_rumah` VALUES (3, 1, 'Alfa madura', 'Ruha 5');
INSERT INTO `master_rumah` VALUES (4, 1, 'Dee shop', 'Ruha 6');
INSERT INTO `master_rumah` VALUES (5, 1, 'Dee shop', 'Ruha 7');
INSERT INTO `master_rumah` VALUES (6, 1, 'Hilmi Hanif', 'TSI Raya 1');
INSERT INTO `master_rumah` VALUES (7, 1, 'Ratih Rahmasari', 'TSI Raya 2');
INSERT INTO `master_rumah` VALUES (8, 1, 'Warno', 'TSI Raya 3');
INSERT INTO `master_rumah` VALUES (9, 1, 'Wahi rical/Jantje Wairisal', 'TSI Raya 3a');
INSERT INTO `master_rumah` VALUES (10, 1, NULL, 'TSI Raya 5');
INSERT INTO `master_rumah` VALUES (11, 1, NULL, 'TSI Raya 6');
INSERT INTO `master_rumah` VALUES (12, 1, 'Ari Kurniawan', 'TSI Raya 7');
INSERT INTO `master_rumah` VALUES (13, 1, 'Afdolu Nasikin', 'TSI Raya 8');
INSERT INTO `master_rumah` VALUES (14, 1, 'Anang Abramzah', 'TSI Blok I‐1');
INSERT INTO `master_rumah` VALUES (15, 1, 'Rio Yudhoprawiro', 'TSI Blok II‐1');
INSERT INTO `master_rumah` VALUES (16, 1, 'Syaifudin Dzulkifli', 'TSI Blok II‐2');
INSERT INTO `master_rumah` VALUES (17, 1, 'Nur Qumaidah', 'TSI Blok II‐3');
INSERT INTO `master_rumah` VALUES (18, 1, 'Wiko Ferdiansyah', 'TSI Blok II‐3A');
INSERT INTO `master_rumah` VALUES (19, 1, 'Alfonsus De Deo', 'TSI Blok II‐5');
INSERT INTO `master_rumah` VALUES (20, 1, 'Henry Faisal', 'TSI Blok II‐6');
INSERT INTO `master_rumah` VALUES (21, 1, 'Edy Susanto', 'TSI Blok II‐7');
INSERT INTO `master_rumah` VALUES (22, 1, 'Anang Abramzah', 'TSI Blok II‐8');
INSERT INTO `master_rumah` VALUES (23, 1, 'Denny Aditya P', 'TSI Blok II‐9');
INSERT INTO `master_rumah` VALUES (24, 1, 'Koko Anantyo', 'TSI Blok II‐10');
INSERT INTO `master_rumah` VALUES (25, 1, 'Andri Susanto', 'TSI Blok II‐11');
INSERT INTO `master_rumah` VALUES (26, 1, 'M Masrufun', 'TSI Blok II‐11A');
INSERT INTO `master_rumah` VALUES (27, 1, 'Eko Bagus P', 'TSI Blok II‐12');
INSERT INTO `master_rumah` VALUES (28, 1, 'Oki Prayogo', 'TSI Blok II‐12A');
INSERT INTO `master_rumah` VALUES (29, 1, 'Rizky Dwi', 'TSI Blok II‐15');
INSERT INTO `master_rumah` VALUES (30, 1, NULL, 'TSI Blok II‐16');
INSERT INTO `master_rumah` VALUES (31, 2, 'Sukma', 'TSI Blok III‐1');
INSERT INTO `master_rumah` VALUES (32, 2, 'Septian Ardiansyah', 'TSI Blok III‐2');
INSERT INTO `master_rumah` VALUES (33, 2, 'Galuh Fandy A', 'TSI Blok III‐3');
INSERT INTO `master_rumah` VALUES (34, 2, 'Aji Irawan', 'TSI Blok III‐3A');
INSERT INTO `master_rumah` VALUES (35, 2, 'Dena', 'TSI Blok III‐5');
INSERT INTO `master_rumah` VALUES (36, 2, 'Galuh Putra Bagus', 'TSI Blok III‐6');
INSERT INTO `master_rumah` VALUES (37, 2, 'Nur Nindyatama', 'TSI Blok III‐7');
INSERT INTO `master_rumah` VALUES (38, 2, 'Adnan', 'TSI Blok III‐8');
INSERT INTO `master_rumah` VALUES (39, 2, 'Adieb', 'TSI Blok III‐9');
INSERT INTO `master_rumah` VALUES (40, 2, 'Indra Irawan', 'TSI Blok IIIB‐1');
INSERT INTO `master_rumah` VALUES (41, 2, 'Rama Prakoso', 'TSI Blok IIIB‐2');
INSERT INTO `master_rumah` VALUES (42, 2, 'Raditya Erlangga', 'TSI Blok IIIB‐3');
INSERT INTO `master_rumah` VALUES (43, 2, 'Gunawan', 'TSI Blok IIIB‐3A');
INSERT INTO `master_rumah` VALUES (44, 2, 'Oky', 'TSI Blok IIIB‐5');
INSERT INTO `master_rumah` VALUES (45, 2, 'Andi Tri Haryanto', 'TSI Blok IIIB‐6');
INSERT INTO `master_rumah` VALUES (46, 2, 'Arif Rachmansyah', 'TSI Blok IIIB‐7');
INSERT INTO `master_rumah` VALUES (47, 2, 'Christian Agave', 'TSI Blok IIIB‐8');
INSERT INTO `master_rumah` VALUES (48, 3, 'Junior Sunyafian', 'TSI Raya 9');
INSERT INTO `master_rumah` VALUES (49, 3, 'Aris Choirul', 'TSI Raya 10');
INSERT INTO `master_rumah` VALUES (50, 3, 'Reza Permana', 'TSI Raya 11');
INSERT INTO `master_rumah` VALUES (51, 3, 'Yopi Ramdhani', 'TSI Raya 11A');
INSERT INTO `master_rumah` VALUES (52, 3, 'Prihatmoro', 'TSI Raya 11B');
INSERT INTO `master_rumah` VALUES (53, 3, 'Defit Sugianto/vivi', 'TSI Raya 12');
INSERT INTO `master_rumah` VALUES (54, 3, 'Giovanni Anggasta', 'TSI Raya 12A');
INSERT INTO `master_rumah` VALUES (55, 3, 'Firman Pambudi', 'TSI Raya 12B');
INSERT INTO `master_rumah` VALUES (56, 3, 'Solikin', 'TSI Raya 15');
INSERT INTO `master_rumah` VALUES (57, 3, 'Achmad Choirul A', 'TSI Raya 15B');
INSERT INTO `master_rumah` VALUES (58, 3, 'M Nugrah', 'TSI Raya 16');
INSERT INTO `master_rumah` VALUES (59, 3, 'Miftahul Faridi', 'TSI Raya 16B');
INSERT INTO `master_rumah` VALUES (60, 3, 'Satriya', 'TSI Raya 17');
INSERT INTO `master_rumah` VALUES (61, 3, 'Arif', 'TSI Raya 18');
INSERT INTO `master_rumah` VALUES (62, 3, '‐', 'TSI Raya 19');
INSERT INTO `master_rumah` VALUES (63, 3, 'Hendi Sandi Putra', 'TSI Raya 20');
INSERT INTO `master_rumah` VALUES (64, 3, 'Alex Suhariyanto', 'TSI Blok IV‐1');
INSERT INTO `master_rumah` VALUES (65, 3, 'R Hanindyo Sasongko', 'TSI Blok IV‐2');
INSERT INTO `master_rumah` VALUES (66, 3, 'Aris Nashari', 'TSI Blok IV‐3');
INSERT INTO `master_rumah` VALUES (67, 3, 'Adnyana Satrio', 'TSI Blok IV‐3A');
INSERT INTO `master_rumah` VALUES (68, 3, 'Argi Yudha P', 'TSI Blok IV‐5');
INSERT INTO `master_rumah` VALUES (69, 3, 'Cahya Eka P', 'TSI Blok IV‐6');
INSERT INTO `master_rumah` VALUES (70, 3, 'Arif Syaifudin', 'TSI Blok IV‐7');
INSERT INTO `master_rumah` VALUES (71, 3, 'Rafles E Alexander', 'TSI Blok IV‐8');
INSERT INTO `master_rumah` VALUES (72, 3, '‐', 'TSI Blok IV‐9');
INSERT INTO `master_rumah` VALUES (73, 4, 'Eko Marsudi', 'TSI Raya 21');
INSERT INTO `master_rumah` VALUES (74, 4, 'Dwi Yulianto', 'TSI Raya 22');
INSERT INTO `master_rumah` VALUES (75, 4, 'Wahyu Agung Prasetyo', 'TSI Raya 23');
INSERT INTO `master_rumah` VALUES (76, 4, 'Firdha', 'TSI Blok V‐1');
INSERT INTO `master_rumah` VALUES (77, 4, 'Dimas Maulana', 'TSI Blok V‐2');
INSERT INTO `master_rumah` VALUES (78, 4, 'Fuad Hidayatullah', 'TSI Blok V‐3');
INSERT INTO `master_rumah` VALUES (79, 4, 'Aditya Panji', 'TSI Blok V‐3A');
INSERT INTO `master_rumah` VALUES (80, 4, 'Fahrudin Wahabi', 'TSI Blok V‐3B');
INSERT INTO `master_rumah` VALUES (81, 4, 'Erlina Wijayanti', 'TSI Blok V‐5');
INSERT INTO `master_rumah` VALUES (82, 4, 'M. Fikrie Ramadhan', 'TSI Blok V‐6');
INSERT INTO `master_rumah` VALUES (83, 4, 'Rudi Kodriansyah', 'TSI Blok V‐7');
INSERT INTO `master_rumah` VALUES (84, 4, 'Sugeng Maulana', 'TSI Blok V‐8');
INSERT INTO `master_rumah` VALUES (85, 4, 'Pang Erga Panghary', 'TSI Blok V‐9');
INSERT INTO `master_rumah` VALUES (86, 4, 'Dimas W', 'TSI Blok V‐10');
INSERT INTO `master_rumah` VALUES (87, 4, 'Erwin Eko', 'TSI Blok V‐11');
INSERT INTO `master_rumah` VALUES (88, 4, 'M. Aris Tri Yunianto', 'TSI Blok V‐11A');
INSERT INTO `master_rumah` VALUES (89, 4, 'Endro', 'TSI Blok V‐12');
INSERT INTO `master_rumah` VALUES (90, 4, 'Yoppi', 'TSI Blok V‐12a');
INSERT INTO `master_rumah` VALUES (91, 4, 'Agus Priyono', 'TSI Blok V‐15');
INSERT INTO `master_rumah` VALUES (92, 5, 'Yoyok Bagus P', 'TSI Raya 23A');
INSERT INTO `master_rumah` VALUES (93, 5, 'M Arief Noor Putra', 'TSI Raya 25');
INSERT INTO `master_rumah` VALUES (94, 5, 'Alpan Irpandi', 'TSI Raya 26');
INSERT INTO `master_rumah` VALUES (95, 5, 'Ellen Pratiwi', 'TSI Raya 27');
INSERT INTO `master_rumah` VALUES (96, 5, 'Akhmad Syakhibul F Azhar', 'TSI Raya 28');
INSERT INTO `master_rumah` VALUES (97, 5, 'Rony Deky Arfianto', 'TSI Raya 29');
INSERT INTO `master_rumah` VALUES (98, 5, 'Agung Wahyu', 'TSI Raya 30');
INSERT INTO `master_rumah` VALUES (99, 5, 'Firmansyah Hadi', 'TSI Blok VI‐1');
INSERT INTO `master_rumah` VALUES (100, 5, 'Sulistiawan', 'TSI Blok VI‐2');
INSERT INTO `master_rumah` VALUES (101, 5, 'Sugiono', 'TSI Blok VI‐3');
INSERT INTO `master_rumah` VALUES (102, 5, 'Angger', 'TSI Blok VI‐3A');
INSERT INTO `master_rumah` VALUES (103, 5, 'Riza Armansyah', 'TSI Blok VI‐3B');
INSERT INTO `master_rumah` VALUES (104, 5, 'Gatot', 'TSI Blok VI‐5');
INSERT INTO `master_rumah` VALUES (105, 5, 'Yogi Andi Nata', 'TSI Blok VI‐6');
INSERT INTO `master_rumah` VALUES (106, 5, 'Niko Permana Kusuma', 'TSI Blok VI‐7');
INSERT INTO `master_rumah` VALUES (107, 5, 'M. Saefulloh R', 'TSI Blok VI‐8');
INSERT INTO `master_rumah` VALUES (108, 5, 'Rizal R', 'TSI Blok VI‐9');
INSERT INTO `master_rumah` VALUES (109, 5, 'David Lukito Dedi', 'TSI Blok VI‐10');
INSERT INTO `master_rumah` VALUES (110, 5, 'Singgih Satriyo W', 'TSI Blok VI‐11');
INSERT INTO `master_rumah` VALUES (111, 5, '‐', 'TSI Blok VI‐11a');
INSERT INTO `master_rumah` VALUES (112, 5, 'Alifian Akbar', 'TSI Blok VI‐12');
INSERT INTO `master_rumah` VALUES (113, 5, 'Rian Cahya', 'TSI Blok VI‐12A');
INSERT INTO `master_rumah` VALUES (114, 5, 'M. Ridfan Alief', 'TSI Blok VI‐15');
INSERT INTO `master_rumah` VALUES (115, 5, 'Rangga Hendradita', 'TSI Blok VI‐16');
INSERT INTO `master_rumah` VALUES (116, 6, 'Umar/ wahyu', 'TSI Raya 31');
INSERT INTO `master_rumah` VALUES (117, 6, 'Hendra Keswanto', 'TSI Raya 32');
INSERT INTO `master_rumah` VALUES (118, 6, 'Suluh', 'TSI Raya 33');
INSERT INTO `master_rumah` VALUES (119, 6, 'Abdul Latif', 'TSI Raya 33A');
INSERT INTO `master_rumah` VALUES (120, 6, 'Dedik Setiawan', 'TSI Raya 35');
INSERT INTO `master_rumah` VALUES (121, 6, 'Gabriel Meze Bela', 'TSI Raya 36');
INSERT INTO `master_rumah` VALUES (122, 6, 'Eko Jalu Purnomo', 'TSI Blok VII‐1');
INSERT INTO `master_rumah` VALUES (123, 6, 'Setyo Adi Swasono', 'TSI Blok VII‐2');
INSERT INTO `master_rumah` VALUES (124, 6, 'Riski Marta', 'TSI Blok VII‐3');
INSERT INTO `master_rumah` VALUES (125, 6, 'Moch Ridwan', 'TSI Blok VII‐3A');
INSERT INTO `master_rumah` VALUES (126, 6, 'Eko Galih Prasetyo', 'TSI Blok VII‐5');
INSERT INTO `master_rumah` VALUES (127, 6, 'Dedy Triwibowo', 'TSI Blok VII‐6');
INSERT INTO `master_rumah` VALUES (128, 6, 'Rizky Briantasyahdita P', 'TSI Blok VII‐7');
INSERT INTO `master_rumah` VALUES (129, 6, 'Arsad Yanuar Haryanto', 'TSI Blok VII‐8');
INSERT INTO `master_rumah` VALUES (130, 6, 'Mars Anggi', 'TSI Blok VII‐9');
INSERT INTO `master_rumah` VALUES (131, 6, 'Tri Adi Setiawan', 'TSI Blok VII‐10');
INSERT INTO `master_rumah` VALUES (132, 6, 'Anas Hidayat', 'TSI Blok VII‐11');
INSERT INTO `master_rumah` VALUES (133, 6, 'Dedhy Wahyu Aryadi', 'TSI Blok VII‐11A');
INSERT INTO `master_rumah` VALUES (134, 6, 'Mochamad Harits', 'TSI Blok VII‐12');
INSERT INTO `master_rumah` VALUES (135, 6, 'Panca Yully Ahmadi', 'TSI Blok VII‐12A');
INSERT INTO `master_rumah` VALUES (136, 6, 'Arief Rahman', 'TSI Blok VII‐15');
INSERT INTO `master_rumah` VALUES (137, 6, 'Zainul Anshor', 'TSI Blok VII‐16');
INSERT INTO `master_rumah` VALUES (138, 6, 'David Anggara', 'TSI Blok VII‐17');
INSERT INTO `master_rumah` VALUES (139, 6, 'Ragita Jati Purbokusumo', 'TSI Blok VII‐18');
INSERT INTO `master_rumah` VALUES (140, 6, 'Zainal Septiadi Baktiana', 'TSI Blok VII‐19');
INSERT INTO `master_rumah` VALUES (141, 6, 'Suhartono', 'TSI Blok VII‐20');
INSERT INTO `master_rumah` VALUES (142, 7, 'Bayu Angga', 'TSI Raya 37');
INSERT INTO `master_rumah` VALUES (143, 7, 'Arta Dian', 'TSI Raya 38');
INSERT INTO `master_rumah` VALUES (144, 7, 'Miftahul Huda', 'TSI Raya 39');
INSERT INTO `master_rumah` VALUES (145, 7, 'M. Sholeh', 'TSI Raya 50');
INSERT INTO `master_rumah` VALUES (146, 7, 'Budiyana', 'TSI Blok VIII‐1');
INSERT INTO `master_rumah` VALUES (147, 7, 'Fernando Sufriadi Sitohang', 'TSI Blok VIII‐2');
INSERT INTO `master_rumah` VALUES (148, 7, 'Rukimah', 'TSI Blok VIII‐3');
INSERT INTO `master_rumah` VALUES (149, 7, 'Hendrik Elvian Gayuh P', 'TSI Blok VIII‐3A');
INSERT INTO `master_rumah` VALUES (150, 7, 'Mulyono', 'TSI Blok VIII‐5');
INSERT INTO `master_rumah` VALUES (151, 7, 'M Julaikan', 'TSI Blok VIII‐6');
INSERT INTO `master_rumah` VALUES (152, 7, 'Hizam', 'TSI Blok VIII‐7');
INSERT INTO `master_rumah` VALUES (153, 7, 'Dhani Kispananto', 'TSI Blok VIII‐8');
INSERT INTO `master_rumah` VALUES (154, 7, 'Cahyo Nugroho', 'TSI Blok VIII‐9');
INSERT INTO `master_rumah` VALUES (155, 7, 'Rizal Priambodo', 'TSI Blok VIII‐10');
INSERT INTO `master_rumah` VALUES (156, 7, 'Sonny Tjandrawan', 'TSI Blok VIII‐11');
INSERT INTO `master_rumah` VALUES (157, 7, 'Ismail Dwi Prakosa', 'TSI Blok VIII‐11A');
INSERT INTO `master_rumah` VALUES (158, 7, 'Alfandi Choirul Anwar', 'TSI Blok VIII‐12');
INSERT INTO `master_rumah` VALUES (159, 7, 'Kingkin Angga Firmana S', 'TSI Blok VIII‐12A');
INSERT INTO `master_rumah` VALUES (160, 7, 'Rahardhimas Rakhmatulloh S', 'TSI Blok VIII‐15');
INSERT INTO `master_rumah` VALUES (161, 7, 'Ferdy Putra', 'TSI Blok VIII‐16');
INSERT INTO `master_rumah` VALUES (162, 7, 'Eko Budi Cahyanto', 'TSI Blok VIII‐17');
INSERT INTO `master_rumah` VALUES (163, 7, 'Judge Eskanto', 'TSI Blok VIII‐18');
INSERT INTO `master_rumah` VALUES (164, 7, 'Agus', 'TSI Blok VIII‐19');
INSERT INTO `master_rumah` VALUES (165, 7, 'Muh Saiful Ramadhan', 'TSI Blok VIII‐20');
INSERT INTO `master_rumah` VALUES (166, 8, 'Endah', 'TSI Raya 51');
INSERT INTO `master_rumah` VALUES (167, 8, 'Rahayu', 'TSI Raya 52');
INSERT INTO `master_rumah` VALUES (168, 8, 'Tri Habsari', 'TSI Raya 53');
INSERT INTO `master_rumah` VALUES (169, 8, 'Eko Prasetya', 'TSI Raya 53A');
INSERT INTO `master_rumah` VALUES (170, 8, 'Febrian Tyahastu', 'TSI Raya 55');
INSERT INTO `master_rumah` VALUES (171, 8, 'Muhammad Saifudin Zuhri', 'TSI Raya 56');
INSERT INTO `master_rumah` VALUES (172, 8, 'Kiky Dody Prasetiono', 'TSI Raya 57');
INSERT INTO `master_rumah` VALUES (173, 8, 'Moh Irvan Fanani', 'TSI Raya 58');
INSERT INTO `master_rumah` VALUES (174, 8, 'Handoyo', 'TSI Raya 59');
INSERT INTO `master_rumah` VALUES (175, 8, 'Andi Budi Setiawan', 'TSI Raya 61');
INSERT INTO `master_rumah` VALUES (176, 8, 'Arfenila Ardiansyah', 'TSI Blok IX‐1');
INSERT INTO `master_rumah` VALUES (177, 8, 'Samsul Huda AS', 'TSI Blok IX‐2');
INSERT INTO `master_rumah` VALUES (178, 8, 'Adi Purnomo', 'TSI Blok IX‐3');
INSERT INTO `master_rumah` VALUES (179, 8, 'Dammara Perdana Oktobahari', 'TSI Blok IX‐3A');
INSERT INTO `master_rumah` VALUES (180, 8, 'Amor Haryo Legowo/ gira ryas', 'TSI Blok IX‐5');
INSERT INTO `master_rumah` VALUES (181, 8, 'Agus Setiawan', 'TSI Blok IX‐6');
INSERT INTO `master_rumah` VALUES (182, 8, 'Lina Indah Sari', 'TSI Blok IX‐7');
INSERT INTO `master_rumah` VALUES (183, 8, 'Imam Utomo', 'TSI Blok IX‐8');
INSERT INTO `master_rumah` VALUES (184, 8, 'Lustiawan', 'TSI Blok IX‐9');
INSERT INTO `master_rumah` VALUES (185, 8, 'Aditya Wahyu Saputra', 'TSI Blok IX‐10');
INSERT INTO `master_rumah` VALUES (186, 8, 'Radyan Errytianta', 'TSI Blok X‐1');
INSERT INTO `master_rumah` VALUES (187, 8, 'Selvia Tiorma', 'TSI Blok X‐2');
INSERT INTO `master_rumah` VALUES (188, 8, 'Novandi Amiludin', 'TSI Blok X‐3A');
INSERT INTO `master_rumah` VALUES (189, 8, 'Muhammad Rachmano', 'TSI Blok XI‐1');
INSERT INTO `master_rumah` VALUES (190, 8, 'Dicky Firman Rizard', 'TSI Blok XI‐2');
INSERT INTO `master_rumah` VALUES (191, 8, 'Shella Oktavia Maulida', 'TSI Blok XI‐3');
INSERT INTO `master_rumah` VALUES (192, 8, 'Randy', 'TSI Blok XI‐3A');
INSERT INTO `master_rumah` VALUES (193, 8, 'Maulana Aziz Prasetya', 'TSI Blok XI‐7');
INSERT INTO `master_rumah` VALUES (194, 8, 'Anastasia/Aditama', 'TSI Blok XI‐8');
INSERT INTO `master_rumah` VALUES (195, 9, 'Anang Subiantoro', 'TSI Blok X‐1B');
INSERT INTO `master_rumah` VALUES (196, 9, 'Aloysius Eric', 'TSI Blok X‐2B');
INSERT INTO `master_rumah` VALUES (197, 9, 'Ramadhitya Hanning', 'TSI Blok X‐3B');
INSERT INTO `master_rumah` VALUES (198, 9, 'Eko Singgih', 'TSI Blok X‐5B');

-- ----------------------------
-- Table structure for master_users
-- ----------------------------
DROP TABLE IF EXISTS `master_users`;
CREATE TABLE `master_users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rumah` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `id_koordinator` bigint NULL DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rumah` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 199 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_users
-- ----------------------------
INSERT INTO `master_users` VALUES (1, '1', 1, 'Hikmah Store', 'Ruha 1', NULL, NULL);
INSERT INTO `master_users` VALUES (2, '2', 1, '', 'Ruha 3a', NULL, NULL);
INSERT INTO `master_users` VALUES (3, '3', 1, 'Alfa madura', 'Ruha 5', NULL, NULL);
INSERT INTO `master_users` VALUES (4, '4', 1, 'Dee shop', 'Ruha 6', NULL, NULL);
INSERT INTO `master_users` VALUES (5, '5', 1, 'Dee shop', 'Ruha 7', NULL, NULL);
INSERT INTO `master_users` VALUES (6, '6', 1, 'Hilmi Hanif', 'TSI Raya 1', NULL, NULL);
INSERT INTO `master_users` VALUES (7, '7', 1, 'Ratih Rahmasari', 'TSI Raya 2', NULL, NULL);
INSERT INTO `master_users` VALUES (8, '8', 1, 'Warno', 'TSI Raya 3', NULL, NULL);
INSERT INTO `master_users` VALUES (9, '9', 1, 'Wahi rical/Jantje Wairisal', 'TSI Raya 3a', NULL, NULL);
INSERT INTO `master_users` VALUES (10, '10', 1, '', 'TSI Raya 5', NULL, NULL);
INSERT INTO `master_users` VALUES (11, '11', 1, '', 'TSI Raya 6', NULL, NULL);
INSERT INTO `master_users` VALUES (12, '12', 1, 'Ari Kurniawan', 'TSI Raya 7', NULL, NULL);
INSERT INTO `master_users` VALUES (13, '13', 1, 'Afdolu Nasikin', 'TSI Raya 8', NULL, NULL);
INSERT INTO `master_users` VALUES (14, '14', 1, 'Anang Abramzah', 'TSI Blok I‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (15, '15', 1, 'Rio Yudhoprawiro', 'TSI Blok II‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (16, '16', 1, 'Syaifudin Dzulkifli', 'TSI Blok II‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (17, '17', 1, 'Nur Qumaidah', 'TSI Blok II‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (18, '18', 1, 'Wiko Ferdiansyah', 'TSI Blok II‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (19, '19', 1, 'Alfonsus De Deo', 'TSI Blok II‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (20, '20', 1, 'Henry Faisal', 'TSI Blok II‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (21, '21', 1, 'Edy Susanto', 'TSI Blok II‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (22, '22', 1, 'Anang Abramzah', 'TSI Blok II‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (23, '23', 1, 'Denny Aditya P', 'TSI Blok II‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (24, '24', 1, 'Koko Anantyo', 'TSI Blok II‐10', NULL, NULL);
INSERT INTO `master_users` VALUES (25, '25', 1, 'Andri Susanto', 'TSI Blok II‐11', NULL, NULL);
INSERT INTO `master_users` VALUES (26, '26', 1, 'M Masrufun', 'TSI Blok II‐11A', NULL, NULL);
INSERT INTO `master_users` VALUES (27, '27', 1, 'Eko Bagus P', 'TSI Blok II‐12', NULL, NULL);
INSERT INTO `master_users` VALUES (28, '28', 1, 'Oki Prayogo', 'TSI Blok II‐12A', NULL, NULL);
INSERT INTO `master_users` VALUES (29, '29', 1, 'Rizky Dwi', 'TSI Blok II‐15', NULL, NULL);
INSERT INTO `master_users` VALUES (30, '30', 1, '', 'TSI Blok II‐16', NULL, NULL);
INSERT INTO `master_users` VALUES (31, '31', 2, 'Sukma', 'TSI Blok III‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (32, '32', 2, 'Septian Ardiansyah', 'TSI Blok III‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (33, '33', 2, 'Galuh Fandy A', 'TSI Blok III‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (34, '34', 2, 'Aji Irawan', 'TSI Blok III‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (35, '35', 2, 'Dena', 'TSI Blok III‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (36, '36', 2, 'Galuh Putra Bagus', 'TSI Blok III‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (37, '37', 2, 'Nur Nindyatama', 'TSI Blok III‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (38, '38', 2, 'Adnan', 'TSI Blok III‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (39, '39', 2, 'Adieb', 'TSI Blok III‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (40, '40', 2, 'Indra Irawan', 'TSI Blok IIIB‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (41, '41', 2, 'Rama Prakoso', 'TSI Blok IIIB‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (42, '42', 2, 'Raditya Erlangga', 'TSI Blok IIIB‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (43, '43', 2, 'Gunawan', 'TSI Blok IIIB‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (44, '44', 2, 'Oky', 'TSI Blok IIIB‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (45, '45', 2, 'Andi Tri Haryanto', 'TSI Blok IIIB‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (46, '46', 2, 'Arif Rachmansyah', 'TSI Blok IIIB‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (47, '47', 2, 'Christian Agave', 'TSI Blok IIIB‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (48, '48', 3, 'Junior Sunyafian', 'TSI Raya 9', NULL, NULL);
INSERT INTO `master_users` VALUES (49, '49', 3, 'Aris Choirul', 'TSI Raya 10', NULL, NULL);
INSERT INTO `master_users` VALUES (50, '50', 3, 'Reza Permana', 'TSI Raya 11', NULL, NULL);
INSERT INTO `master_users` VALUES (51, '51', 3, 'Yopi Ramdhani', 'TSI Raya 11A', NULL, NULL);
INSERT INTO `master_users` VALUES (52, '52', 3, 'Prihatmoro', 'TSI Raya 11B', NULL, NULL);
INSERT INTO `master_users` VALUES (53, '53', 3, 'Defit Sugianto/vivi', 'TSI Raya 12', NULL, NULL);
INSERT INTO `master_users` VALUES (54, '54', 3, 'Giovanni Anggasta', 'TSI Raya 12A', NULL, NULL);
INSERT INTO `master_users` VALUES (55, '55', 3, 'Firman Pambudi', 'TSI Raya 12B', NULL, NULL);
INSERT INTO `master_users` VALUES (56, '56', 3, 'Solikin', 'TSI Raya 15', NULL, NULL);
INSERT INTO `master_users` VALUES (57, '57', 3, 'Achmad Choirul A', 'TSI Raya 15B', NULL, NULL);
INSERT INTO `master_users` VALUES (58, '58', 3, 'M Nugrah', 'TSI Raya 16', NULL, NULL);
INSERT INTO `master_users` VALUES (59, '59', 3, 'Miftahul Faridi', 'TSI Raya 16B', NULL, NULL);
INSERT INTO `master_users` VALUES (60, '60', 3, 'Satriya', 'TSI Raya 17', NULL, NULL);
INSERT INTO `master_users` VALUES (61, '61', 3, 'Arif', 'TSI Raya 18', NULL, NULL);
INSERT INTO `master_users` VALUES (62, '62', 3, '‐', 'TSI Raya 19', NULL, NULL);
INSERT INTO `master_users` VALUES (63, '63', 3, 'Hendi Sandi Putra', 'TSI Raya 20', NULL, NULL);
INSERT INTO `master_users` VALUES (64, '64', 3, 'Alex Suhariyanto', 'TSI Blok IV‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (65, '65', 3, 'R Hanindyo Sasongko', 'TSI Blok IV‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (66, '66', 3, 'Aris Nashari', 'TSI Blok IV‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (67, '67', 3, 'Adnyana Satrio', 'TSI Blok IV‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (68, '68', 3, 'Argi Yudha P', 'TSI Blok IV‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (69, '69', 3, 'Cahya Eka P', 'TSI Blok IV‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (70, '70', 3, 'Arif Syaifudin', 'TSI Blok IV‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (71, '71', 3, 'Rafles E Alexander', 'TSI Blok IV‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (72, '72', 3, '‐', 'TSI Blok IV‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (73, '73', 4, 'Eko Marsudi', 'TSI Raya 21', NULL, NULL);
INSERT INTO `master_users` VALUES (74, '74', 4, 'Dwi Yulianto', 'TSI Raya 22', NULL, NULL);
INSERT INTO `master_users` VALUES (75, '75', 4, 'Wahyu Agung Prasetyo', 'TSI Raya 23', NULL, NULL);
INSERT INTO `master_users` VALUES (76, '76', 4, 'Firdha', 'TSI Blok V‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (77, '77', 4, 'Dimas Maulana', 'TSI Blok V‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (78, '78', 4, 'Fuad Hidayatullah', 'TSI Blok V‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (79, '79', 4, 'Aditya Panji', 'TSI Blok V‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (80, '80', 4, 'Fahrudin Wahabi', 'TSI Blok V‐3B', NULL, NULL);
INSERT INTO `master_users` VALUES (81, '81', 4, 'Erlina Wijayanti', 'TSI Blok V‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (82, '82', 4, 'M. Fikrie Ramadhan', 'TSI Blok V‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (83, '83', 4, 'Rudi Kodriansyah', 'TSI Blok V‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (84, '84', 4, 'Sugeng Maulana', 'TSI Blok V‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (85, '85', 4, 'Pang Erga Panghary', 'TSI Blok V‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (86, '86', 4, 'Dimas W', 'TSI Blok V‐10', NULL, NULL);
INSERT INTO `master_users` VALUES (87, '87', 4, 'Erwin Eko', 'TSI Blok V‐11', NULL, NULL);
INSERT INTO `master_users` VALUES (88, '88', 4, 'M. Aris Tri Yunianto', 'TSI Blok V‐11A', NULL, NULL);
INSERT INTO `master_users` VALUES (89, '89', 4, 'Endro', 'TSI Blok V‐12', NULL, NULL);
INSERT INTO `master_users` VALUES (90, '90', 4, 'Yoppi', 'TSI Blok V‐12a', NULL, NULL);
INSERT INTO `master_users` VALUES (91, '91', 4, 'Agus Priyono', 'TSI Blok V‐15', NULL, NULL);
INSERT INTO `master_users` VALUES (92, '92', 5, 'Yoyok Bagus P', 'TSI Raya 23A', NULL, NULL);
INSERT INTO `master_users` VALUES (93, '93', 5, 'M Arief Noor Putra', 'TSI Raya 25', NULL, NULL);
INSERT INTO `master_users` VALUES (94, '94', 5, 'Alpan Irpandi', 'TSI Raya 26', NULL, NULL);
INSERT INTO `master_users` VALUES (95, '95', 5, 'Ellen Pratiwi', 'TSI Raya 27', NULL, NULL);
INSERT INTO `master_users` VALUES (96, '96', 5, 'Akhmad Syakhibul F Azhar', 'TSI Raya 28', NULL, NULL);
INSERT INTO `master_users` VALUES (97, '97', 5, 'Rony Deky Arfianto', 'TSI Raya 29', NULL, NULL);
INSERT INTO `master_users` VALUES (98, '98', 5, 'Agung Wahyu', 'TSI Raya 30', NULL, NULL);
INSERT INTO `master_users` VALUES (99, '99', 5, 'Firmansyah Hadi', 'TSI Blok VI‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (100, '100', 5, 'Sulistiawan', 'TSI Blok VI‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (101, '101', 5, 'Sugiono', 'TSI Blok VI‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (102, '102', 5, 'Angger', 'TSI Blok VI‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (103, '103', 5, 'Riza Armansyah', 'TSI Blok VI‐3B', NULL, NULL);
INSERT INTO `master_users` VALUES (104, '104', 5, 'Gatot', 'TSI Blok VI‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (105, '105', 5, 'Yogi Andi Nata', 'TSI Blok VI‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (106, '106', 5, 'Niko Permana Kusuma', 'TSI Blok VI‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (107, '107', 5, 'M. Saefulloh R', 'TSI Blok VI‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (108, '108', 5, 'Rizal R', 'TSI Blok VI‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (109, '109', 5, 'David Lukito Dedi', 'TSI Blok VI‐10', NULL, NULL);
INSERT INTO `master_users` VALUES (110, '110', 5, 'Singgih Satriyo W', 'TSI Blok VI‐11', NULL, NULL);
INSERT INTO `master_users` VALUES (111, '111', 5, '‐', 'TSI Blok VI‐11a', NULL, NULL);
INSERT INTO `master_users` VALUES (112, '112', 5, 'Alifian Akbar', 'TSI Blok VI‐12', NULL, NULL);
INSERT INTO `master_users` VALUES (113, '113', 5, 'Rian Cahya', 'TSI Blok VI‐12A', NULL, NULL);
INSERT INTO `master_users` VALUES (114, '114', 5, 'M. Ridfan Alief', 'TSI Blok VI‐15', NULL, NULL);
INSERT INTO `master_users` VALUES (115, '115', 5, 'Rangga Hendradita', 'TSI Blok VI‐16', NULL, NULL);
INSERT INTO `master_users` VALUES (116, '116', 6, 'Umar/ wahyu', 'TSI Raya 31', NULL, NULL);
INSERT INTO `master_users` VALUES (117, '117', 6, 'Hendra Keswanto', 'TSI Raya 32', NULL, NULL);
INSERT INTO `master_users` VALUES (118, '118', 6, 'Suluh', 'TSI Raya 33', NULL, NULL);
INSERT INTO `master_users` VALUES (119, '119', 6, 'Abdul Latif', 'TSI Raya 33A', NULL, NULL);
INSERT INTO `master_users` VALUES (120, '120', 6, 'Dedik Setiawan', 'TSI Raya 35', NULL, NULL);
INSERT INTO `master_users` VALUES (121, '121', 6, 'Gabriel Meze Bela', 'TSI Raya 36', NULL, NULL);
INSERT INTO `master_users` VALUES (122, '122', 6, 'Eko Jalu Purnomo', 'TSI Blok VII‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (123, '123', 6, 'Setyo Adi Swasono', 'TSI Blok VII‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (124, '124', 6, 'Riski Marta', 'TSI Blok VII‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (125, '125', 6, 'Moch Ridwan', 'TSI Blok VII‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (126, '126', 6, 'Eko Galih Prasetyo', 'TSI Blok VII‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (127, '127', 6, 'Dedy Triwibowo', 'TSI Blok VII‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (128, '128', 6, 'Rizky Briantasyahdita P', 'TSI Blok VII‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (129, '129', 6, 'Arsad Yanuar Haryanto', 'TSI Blok VII‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (130, '130', 6, 'Mars Anggi', 'TSI Blok VII‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (131, '131', 6, 'Tri Adi Setiawan', 'TSI Blok VII‐10', NULL, NULL);
INSERT INTO `master_users` VALUES (132, '132', 6, 'Anas Hidayat', 'TSI Blok VII‐11', NULL, NULL);
INSERT INTO `master_users` VALUES (133, '133', 6, 'Dedhy Wahyu Aryadi', 'TSI Blok VII‐11A', NULL, NULL);
INSERT INTO `master_users` VALUES (134, '134', 6, 'Mochamad Harits', 'TSI Blok VII‐12', NULL, NULL);
INSERT INTO `master_users` VALUES (135, '135', 6, 'Panca Yully Ahmadi', 'TSI Blok VII‐12A', NULL, NULL);
INSERT INTO `master_users` VALUES (136, '136', 6, 'Arief Rahman', 'TSI Blok VII‐15', NULL, NULL);
INSERT INTO `master_users` VALUES (137, '137', 6, 'Zainul Anshor', 'TSI Blok VII‐16', NULL, NULL);
INSERT INTO `master_users` VALUES (138, '138', 6, 'David Anggara', 'TSI Blok VII‐17', NULL, NULL);
INSERT INTO `master_users` VALUES (139, '139', 6, 'Ragita Jati Purbokusumo', 'TSI Blok VII‐18', NULL, NULL);
INSERT INTO `master_users` VALUES (140, '140', 6, 'Zainal Septiadi Baktiana', 'TSI Blok VII‐19', NULL, NULL);
INSERT INTO `master_users` VALUES (141, '141', 6, 'Suhartono', 'TSI Blok VII‐20', NULL, NULL);
INSERT INTO `master_users` VALUES (142, '142', 7, 'Bayu Angga', 'TSI Raya 37', NULL, NULL);
INSERT INTO `master_users` VALUES (143, '143', 7, 'Arta Dian', 'TSI Raya 38', NULL, NULL);
INSERT INTO `master_users` VALUES (144, '144', 7, 'Miftahul Huda', 'TSI Raya 39', NULL, NULL);
INSERT INTO `master_users` VALUES (145, '145', 7, 'M. Sholeh', 'TSI Raya 50', NULL, NULL);
INSERT INTO `master_users` VALUES (146, '146', 7, 'Budiyana', 'TSI Blok VIII‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (147, '147', 7, 'Fernando Sufriadi Sitohang', 'TSI Blok VIII‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (148, '148', 7, 'Rukimah', 'TSI Blok VIII‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (149, '149', 7, 'Hendrik Elvian Gayuh P', 'TSI Blok VIII‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (150, '150', 7, 'Mulyono', 'TSI Blok VIII‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (151, '151', 7, 'M Julaikan', 'TSI Blok VIII‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (152, '152', 7, 'Hizam', 'TSI Blok VIII‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (153, '153', 7, 'Dhani Kispananto', 'TSI Blok VIII‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (154, '154', 7, 'Cahyo Nugroho', 'TSI Blok VIII‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (155, '155', 7, 'Rizal Priambodo', 'TSI Blok VIII‐10', NULL, NULL);
INSERT INTO `master_users` VALUES (156, '156', 7, 'Sonny Tjandrawan', 'TSI Blok VIII‐11', NULL, NULL);
INSERT INTO `master_users` VALUES (157, '157', 7, 'Ismail Dwi Prakosa', 'TSI Blok VIII‐11A', NULL, NULL);
INSERT INTO `master_users` VALUES (158, '158', 7, 'Alfandi Choirul Anwar', 'TSI Blok VIII‐12', NULL, NULL);
INSERT INTO `master_users` VALUES (159, '159', 7, 'Kingkin Angga Firmana S', 'TSI Blok VIII‐12A', NULL, NULL);
INSERT INTO `master_users` VALUES (160, '160', 7, 'Rahardhimas Rakhmatulloh S', 'TSI Blok VIII‐15', NULL, NULL);
INSERT INTO `master_users` VALUES (161, '161', 7, 'Ferdy Putra', 'TSI Blok VIII‐16', NULL, NULL);
INSERT INTO `master_users` VALUES (162, '162', 7, 'Eko Budi Cahyanto', 'TSI Blok VIII‐17', NULL, NULL);
INSERT INTO `master_users` VALUES (163, '163', 7, 'Judge Eskanto', 'TSI Blok VIII‐18', NULL, NULL);
INSERT INTO `master_users` VALUES (164, '164', 7, 'Agus', 'TSI Blok VIII‐19', NULL, NULL);
INSERT INTO `master_users` VALUES (165, '165', 7, 'Muh Saiful Ramadhan', 'TSI Blok VIII‐20', NULL, NULL);
INSERT INTO `master_users` VALUES (166, '166', 8, 'Endah', 'TSI Raya 51', NULL, NULL);
INSERT INTO `master_users` VALUES (167, '167', 8, 'Rahayu', 'TSI Raya 52', NULL, NULL);
INSERT INTO `master_users` VALUES (168, '168', 8, 'Tri Habsari', 'TSI Raya 53', NULL, NULL);
INSERT INTO `master_users` VALUES (169, '169', 8, 'Eko Prasetya', 'TSI Raya 53A', NULL, NULL);
INSERT INTO `master_users` VALUES (170, '170', 8, 'Febrian Tyahastu', 'TSI Raya 55', NULL, NULL);
INSERT INTO `master_users` VALUES (171, '171', 8, 'Muhammad Saifudin Zuhri', 'TSI Raya 56', NULL, NULL);
INSERT INTO `master_users` VALUES (172, '172', 8, 'Kiky Dody Prasetiono', 'TSI Raya 57', NULL, NULL);
INSERT INTO `master_users` VALUES (173, '173', 8, 'Moh Irvan Fanani', 'TSI Raya 58', NULL, NULL);
INSERT INTO `master_users` VALUES (174, '174', 8, 'Handoyo', 'TSI Raya 59', NULL, NULL);
INSERT INTO `master_users` VALUES (175, '175', 8, 'Andi Budi Setiawan', 'TSI Raya 61', NULL, NULL);
INSERT INTO `master_users` VALUES (176, '176', 8, 'Arfenila Ardiansyah', 'TSI Blok IX‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (177, '177', 8, 'Samsul Huda AS', 'TSI Blok IX‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (178, '178', 8, 'Adi Purnomo', 'TSI Blok IX‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (179, '179', 8, 'Dammara Perdana Oktobahari', 'TSI Blok IX‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (180, '180', 8, 'Amor Haryo Legowo/ gira ryas', 'TSI Blok IX‐5', NULL, NULL);
INSERT INTO `master_users` VALUES (181, '181', 8, 'Agus Setiawan', 'TSI Blok IX‐6', NULL, NULL);
INSERT INTO `master_users` VALUES (182, '182', 8, 'Lina Indah Sari', 'TSI Blok IX‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (183, '183', 8, 'Imam Utomo', 'TSI Blok IX‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (184, '184', 8, 'Lustiawan', 'TSI Blok IX‐9', NULL, NULL);
INSERT INTO `master_users` VALUES (185, '185', 8, 'Aditya Wahyu Saputra', 'TSI Blok IX‐10', NULL, NULL);
INSERT INTO `master_users` VALUES (186, '186', 8, 'Radyan Errytianta', 'TSI Blok X‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (187, '187', 8, 'Selvia Tiorma', 'TSI Blok X‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (188, '188', 8, 'Novandi Amiludin', 'TSI Blok X‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (189, '189', 8, 'Muhammad Rachmano', 'TSI Blok XI‐1', NULL, NULL);
INSERT INTO `master_users` VALUES (190, '190', 8, 'Dicky Firman Rizard', 'TSI Blok XI‐2', NULL, NULL);
INSERT INTO `master_users` VALUES (191, '191', 8, 'Shella Oktavia Maulida', 'TSI Blok XI‐3', NULL, NULL);
INSERT INTO `master_users` VALUES (192, '192', 8, 'Randy', 'TSI Blok XI‐3A', NULL, NULL);
INSERT INTO `master_users` VALUES (193, '193', 8, 'Maulana Aziz Prasetya', 'TSI Blok XI‐7', NULL, NULL);
INSERT INTO `master_users` VALUES (194, '194', 8, 'Anastasia/Aditama', 'TSI Blok XI‐8', NULL, NULL);
INSERT INTO `master_users` VALUES (195, '195', 9, 'Anang Subiantoro', 'TSI Blok X‐1B', NULL, NULL);
INSERT INTO `master_users` VALUES (196, '196', 9, 'Aloysius Eric', 'TSI Blok X‐2B', NULL, NULL);
INSERT INTO `master_users` VALUES (197, '197', 9, 'Ramadhitya Hanning', 'TSI Blok X‐3B', NULL, NULL);
INSERT INTO `master_users` VALUES (198, '198', 9, 'Eko Singgih', 'TSI Blok X‐5B', NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
