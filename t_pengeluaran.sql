/*
 Navicat Premium Dump SQL

 Source Server         : Local
 Source Server Type    : MySQL
 Source Server Version : 100428 (10.4.28-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : tsi

 Target Server Type    : MySQL
 Target Server Version : 100428 (10.4.28-MariaDB)
 File Encoding         : 65001

 Date: 24/05/2025 21:30:49
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for t_pengeluaran
-- ----------------------------
DROP TABLE IF EXISTS `t_pengeluaran`;
CREATE TABLE `t_pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis_pengeluaran` varchar(20) NOT NULL COMMENT 'reff on master_code, pengeluaran',
  `deskripsi` varchar(200) NOT NULL,
  `nominal` float NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `active_status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of t_pengeluaran
-- ----------------------------
BEGIN;
INSERT INTO `t_pengeluaran` (`id`, `jenis_pengeluaran`, `deskripsi`, `nominal`, `tanggal`, `keterangan`, `active_status`, `created_at`, `updated_at`) VALUES (1, 'GS', 'Gaji Satpam bulan April', 2000000, '2025-05-31', 'Gaji Satpam', 1, '2025-05-24 20:14:48', '2025-05-24 21:29:41');
INSERT INTO `t_pengeluaran` (`id`, `jenis_pengeluaran`, `deskripsi`, `nominal`, `tanggal`, `keterangan`, `active_status`, `created_at`, `updated_at`) VALUES (3, 'SA', 'Perbaikan Pompa', 200000, '2025-05-22', ' perbaikan mesin pompa air untuk kebutuhan penyiraman taman para gardener', 1, '2025-05-24 20:45:18', '2025-05-24 20:45:18');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
