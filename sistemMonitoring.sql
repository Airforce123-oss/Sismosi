-- -------------------------------------------------------------
-- TablePlus 6.0.8(562)
--
-- https://tableplus.com/
--
-- Database: sistemMonitoring
-- Generation Time: 2024-07-27 07:45:59.3510
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `classes_for_student` (
  `id_kelas` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_kelas` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_kelas`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `master_mapel` (
  `id_mapel` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mapel` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_mapel`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_type_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sections_class_id_foreign` (`class_id`),
  CONSTRAINT `sections_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `section_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `students_class_id_foreign` (`class_id`),
  KEY `students_section_id_foreign` (`section_id`),
  CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `students_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `classes` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'X-1', NULL, NULL),
(2, 'X-2', NULL, NULL),
(3, 'X-3', NULL, NULL),
(4, 'X-4', NULL, NULL),
(5, 'X-5', NULL, NULL),
(6, 'X-6', NULL, NULL),
(7, 'XI-1', NULL, NULL),
(8, 'XI-2', NULL, NULL),
(9, 'XI-3', NULL, NULL),
(10, 'XI-4', NULL, NULL),
(11, 'XI-5', NULL, NULL),
(12, 'XI-6', NULL, NULL),
(13, 'XII-1', NULL, NULL),
(14, 'XII-2', NULL, NULL),
(15, 'XII-3', NULL, NULL),
(16, 'XII-4', NULL, NULL);

INSERT INTO `classes_for_student` (`id_kelas`, `nama_kelas`, `kode_kelas`, `created_at`, `updated_at`) VALUES
(1, 'Kelas 1', 'MK-1561560093', NULL, NULL),
(2, 'Kelas 2', 'MK-1561560129', NULL, NULL),
(3, 'Kelas 3', 'MK-1561871991', NULL, NULL),
(4, 'Kelas 4', 'MK-1561872004', NULL, NULL),
(5, 'Kelas 5', 'MK-1561872013', NULL, NULL),
(6, 'Kelas 6', 'MK-1561872026', NULL, NULL),
(7, 'Kelas 7', 'MK-1561872043', NULL, NULL),
(8, 'Kelas 8', 'MK-1615002340', NULL, NULL);

INSERT INTO `master_mapel` (`id_mapel`, `kode_mapel`, `mapel`, `created_at`, `updated_at`) VALUES
(1, 'MP-1561560093', 'Bahasa Indonesia', NULL, NULL),
(2, 'MP-1561560129', 'Matematika', NULL, NULL),
(3, 'MP-1561871991', 'Biologi', NULL, NULL),
(4, 'MP-1561872004', 'Sejarah', NULL, NULL),
(5, 'MP-1561872013', 'Teknologi Informasi', NULL, NULL),
(6, 'MP-1561872026', 'Seni Budaya', NULL, NULL),
(7, 'MP-1561872043', 'Bahasa Inggris', NULL, NULL),
(8, 'MP-1615002340', 'Ilmu Pengetahuan Alam', NULL, NULL);

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2024_05_15_125313_create_classes_table', 1),
(5, '2024_05_15_130724_create_sections_table', 1),
(6, '2024_05_15_154534_create_students_table', 1),
(7, '2024_05_17_020236_create_admins_table', 1),
(8, '2024_07_21_034331_update_users_table', 1),
(9, '2024_07_21_040015_update_users_table', 1),
(10, '2024_07_21_040215_update_users_table', 1),
(11, '2024_07_21_041323_update_users_table', 1),
(12, '2024_07_21_084705_create_role_type_users_table', 1),
(13, '2024_07_21_184036_create_users_table', 1),
(14, '2024_07_23_021446_create_master_mapel', 1),
(15, '2024_07_23_170438_create_classes_for_student_table', 1);

INSERT INTO `role_type_users` (`id`, `role_type`, `created_at`, `updated_at`) VALUES
(1, 'Admin', NULL, NULL),
(2, 'Teachers', NULL, NULL),
(3, 'Student', NULL, NULL),
(4, 'Parent', NULL, NULL);

INSERT INTO `sections` (`id`, `class_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'A', NULL, NULL),
(2, 1, 'B', NULL, NULL),
(3, 2, 'A', NULL, NULL),
(4, 2, 'B', NULL, NULL),
(5, 3, 'A', NULL, NULL),
(6, 3, 'B', NULL, NULL),
(7, 4, 'A', NULL, NULL),
(8, 4, 'B', NULL, NULL),
(9, 5, 'A', NULL, NULL),
(10, 5, 'B', NULL, NULL),
(11, 6, 'A', NULL, NULL),
(12, 6, 'B', NULL, NULL);

INSERT INTO `users` (`id`, `user_id`, `name`, `email`, `date_of_birth`, `join_date`, `phone_number`, `status`, `role_name`, `avatar`, `position`, `department`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, '000001', 'Aries', 'aries@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$/ZK9DYfsT99sxHVH3msDhuq5iX7M615jRUGzSUBcyNvvJb/0RTrke', NULL, '2024-07-26 23:48:42', '2024-07-26 23:48:42');



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;