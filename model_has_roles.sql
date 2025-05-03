/*
 Navicat Premium Data Transfer

 Source Server         : Localhost 3336
 Source Server Type    : MySQL
 Source Server Version : 80030
 Source Host           : localhost:3336
 Source Schema         : sarana_v12

 Target Server Type    : MySQL
 Target Server Version : 80030
 File Encoding         : 65001

 Date: 01/05/2025 16:41:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles`  (
  `role_id` bigint(0) UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(0) UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_roles_model_id_model_type_index`(`model_id`, `model_type`) USING BTREE,
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of model_has_roles
-- ----------------------------
INSERT INTO `model_has_roles` VALUES (6, 'App\\Models\\User', 1);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 3);
INSERT INTO `model_has_roles` VALUES (9, 'App\\Models\\User', 4);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 5);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 6);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 7);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 8);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 9);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 11);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 12);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 13);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 14);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 17);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 18);
INSERT INTO `model_has_roles` VALUES (8, 'App\\Models\\User', 19);

SET FOREIGN_KEY_CHECKS = 1;
