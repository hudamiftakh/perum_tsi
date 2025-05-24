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

 Date: 24/05/2025 21:31:02
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for master_code
-- ----------------------------
DROP TABLE IF EXISTS `master_code`;
CREATE TABLE `master_code` (
  `app_code` varchar(20) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `deskripsi` varchar(100) DEFAULT NULL,
  `order_no` int(2) DEFAULT NULL,
  `active_status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of master_code
-- ----------------------------
BEGIN;
INSERT INTO `master_code` (`app_code`, `code`, `deskripsi`, `order_no`, `active_status`) VALUES ('pengeluaran', 'GG', 'Gaji Gardener', 1, 1);
INSERT INTO `master_code` (`app_code`, `code`, `deskripsi`, `order_no`, `active_status`) VALUES ('pengeluaran', 'GS', 'Gaji Satpam', 2, 1);
INSERT INTO `master_code` (`app_code`, `code`, `deskripsi`, `order_no`, `active_status`) VALUES ('pengeluaran', 'SA', 'Servis Alat', 3, 1);
INSERT INTO `master_code` (`app_code`, `code`, `deskripsi`, `order_no`, `active_status`) VALUES ('pengeluaran', 'BO', 'Biaya Obat', 4, 1);
INSERT INTO `master_code` (`app_code`, `code`, `deskripsi`, `order_no`, `active_status`) VALUES ('pengeluaran', 'PB', 'Pembelian', 4, 1);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
