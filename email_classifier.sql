/*
 Navicat Premium Data Transfer

 Source Server         : Local
 Source Server Type    : MySQL
 Source Server Version : 100432
 Source Host           : localhost:3306
 Source Schema         : email_classifier

 Target Server Type    : MySQL
 Target Server Version : 100432
 File Encoding         : 65001

 Date: 02/06/2025 17:58:40
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for emails
-- ----------------------------
DROP TABLE IF EXISTS `emails`;
CREATE TABLE `emails`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of emails
-- ----------------------------
INSERT INTO `emails` VALUES (1, 'I\'ve been charged twice for my monthly subscription and need a refund ASAP.\"', '[\"Billing Issue\",\"Refund Request\"]', 1, '2025-06-02 10:45:15', '2025-06-02 10:45:15');
INSERT INTO `emails` VALUES (2, 'Could you please provide me with the current status of my order? I would appreciate it if you could let me know whether the package has been shipped, and if so, provide an estimated delivery date or any relevant tracking details.', '[\"Other\"]', 1, '2025-06-02 11:29:21', '2025-06-02 11:29:26');
INSERT INTO `emails` VALUES (3, 'Could you please provide me with the current status of my order? I would appreciate it if you could let me know whether the package has been shipped, and if so, provide an estimated delivery date or any relevant tracking details.', '[\"Other\"]', 1, '2025-06-02 11:29:48', '2025-06-02 11:29:54');
INSERT INTO `emails` VALUES (4, 'Could you please provide me with the current status of my order? I would appreciate it if you could let me know whether the package has been shipped, and if so, provide an estimated delivery date or any relevant tracking details.', '[\"Other\"]', 1, '2025-06-02 11:30:32', '2025-06-02 11:30:37');
INSERT INTO `emails` VALUES (5, 'I hope this message finds you well. I’m writing to follow up on an order I placed approximately two weeks ago through your website. Unfortunately, I have not yet received the package, and there has been no update on the tracking information since the initial confirmation email.', '[\"Other\"]', 1, '2025-06-02 11:36:11', '2025-06-02 11:36:18');
INSERT INTO `emails` VALUES (6, 'Could you please provide me with the current status of my order? I would appreciate it if you could let me know whether the package has been shipped, and if so, provide an estimated delivery date or any relevant tracking details.', '[\"Other\"]', 1, '2025-06-02 11:36:18', '2025-06-02 11:36:24');
INSERT INTO `emails` VALUES (7, 'Thank you for your assistance. I look forward to your prompt response.', '[\"Other\"]', 1, '2025-06-02 11:36:24', '2025-06-02 11:36:29');
INSERT INTO `emails` VALUES (8, 'Best regards,', '[\"Other\"]', 1, '2025-06-02 11:36:29', '2025-06-02 11:36:36');
INSERT INTO `emails` VALUES (9, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!', '[\"Other\"]', 1, '2025-06-02 11:37:25', '2025-06-02 11:37:31');
INSERT INTO `emails` VALUES (10, 'I\'ve been charged twice for my monthly subscription and need a refund ASAP', '[\"Other\"]', 1, '2025-06-02 11:38:23', '2025-06-02 11:38:30');
INSERT INTO `emails` VALUES (11, 'I\'ve been charged twice for my monthly subscription and need a refund ASAP', '[\"Other\"]', 1, '2025-06-02 11:38:39', '2025-06-02 11:38:46');
INSERT INTO `emails` VALUES (12, 'Your app keeps crashing every time I try to upload a photo. This is frustrating!', '[\"Other\"]', 1, '2025-06-02 11:39:04', '2025-06-02 11:39:09');
INSERT INTO `emails` VALUES (13, 'I\'ve been charged twice for my monthly subscription and need a refund ASAP.\"', '[\"Billing Issue\",\"Refund Request\"]', 1, '2025-06-02 11:41:13', '2025-06-02 11:41:13');
INSERT INTO `emails` VALUES (14, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!\"', '[\"Bug Report\"]', 1, '2025-06-02 11:41:39', '2025-06-02 11:41:39');
INSERT INTO `emails` VALUES (15, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!\"', '[\"Other\"]', 1, '2025-06-02 11:42:56', '2025-06-02 11:43:03');
INSERT INTO `emails` VALUES (16, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!\"', '[\"Other\"]', 1, '2025-06-02 11:50:40', '2025-06-02 11:50:48');
INSERT INTO `emails` VALUES (17, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!\"', '[\"Bug Report\"]', 1, '2025-06-02 11:52:01', '2025-06-02 11:52:01');
INSERT INTO `emails` VALUES (18, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!\"', '[\"Bug Report\"]', 1, '2025-06-02 11:57:05', '2025-06-02 11:57:05');
INSERT INTO `emails` VALUES (19, '\"Your app keeps crashing every time I try to upload a photo. This is frustrating!\"', '[\"Other\"]', 1, '2025-06-02 11:57:23', '2025-06-02 11:57:29');

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '2014_10_12_100000_create_password_reset_tokens_table', 1);
INSERT INTO `migrations` VALUES (3, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` VALUES (4, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (5, '2025_06_02_100905_create_emails_table', 1);

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp(0) NULL DEFAULT NULL,
  `expires_at` timestamp(0) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token`) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp(0) NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
