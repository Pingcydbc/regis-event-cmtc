-- SQL Setup for School Register System
-- Creating Tables: students_import, reported_issues

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";
-- --------------------------------------------------------
-- 1. ตารางสำหรับเก็บข้อมูลนักศึกษาและการลงทะเบียน
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `students_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_code` varchar(20) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `id_card` varchar(20) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `group_id` varchar(20) DEFAULT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'ยังไม่ลงทะเบียน',
  `registered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_card` (`id_card`),
  KEY `student_id` (`student_id`),
  KEY `group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. ตารางสำหรับเก็บข้อความแชท (Real-time Chat)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` varchar(50) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. ตารางสำหรับเก็บข้อมูลผู้กรอกข้อมูลใหม่ (กรณีไม่มีรายชื่อ)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fix_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_code` varchar(20) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `id_card` varchar(20) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `group_id` varchar(20) DEFAULT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'รอดำเนินการ',
  `registered_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;