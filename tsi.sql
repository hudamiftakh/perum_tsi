/*
 Navicat Premium Data Transfer

 Source Server         : MYSQL-LOCAL
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : tsi

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 15/05/2025 20:19:16
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
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_admin
-- ----------------------------
INSERT INTO `master_admin` VALUES (1, 'Admin Perum TSI', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', '2025-05-15 08:12:27');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_agenda
-- ----------------------------
INSERT INTO `master_agenda` VALUES (3, 'Rapat Paguyuban RT 1', '2025-05-15', '09:53:00', '09:53:00', 'Balai RW', 'Agenda', 'Sekertaris', '2025-05-15 09:53:27', '2025-05-15 09:53:27');
INSERT INTO `master_agenda` VALUES (5, 'Rapat Bulanan RT 09', '2025-05-15', '01:12:00', '13:14:00', 'fdf', 'sasas', 'Ketua RW', '2025-05-15 09:23:10', '2025-05-15 10:27:25');

-- ----------------------------
-- Table structure for master_partisipan
-- ----------------------------
DROP TABLE IF EXISTS `master_partisipan`;
CREATE TABLE `master_partisipan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `agenda_id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ttd_base64` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hadir_pada` datetime NULL DEFAULT current_timestamp,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_agenda_partisipan`(`agenda_id` ASC) USING BTREE,
  CONSTRAINT `fk_agenda_partisipan` FOREIGN KEY (`agenda_id`) REFERENCES `master_agenda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_partisipan
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
