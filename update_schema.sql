-- SQL Command to update table structure
-- Execute this on your web database (InfinityFree PHPMyAdmin)

DROP TABLE IF EXISTS `students_import`;
CREATE TABLE `students_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_code` varchar(20) DEFAULT NULL,    -- A. ลำดับ
  `student_id` varchar(20) DEFAULT NULL,           -- B. รหัสผู้เรียน
  `id_card` varchar(20) DEFAULT NULL,              -- C. เลขบัตรประชาชน (รองรับตัวอักษร เช่น G63...)
  `student_name` varchar(255) DEFAULT NULL,        -- D. ชื่อผู้เรียน
  `parent_name` varchar(255) DEFAULT NULL,         -- E. ชื่อผู้ปกครอง
  `address` text DEFAULT NULL,                     -- F. ที่อยู่
  `parent_phone` varchar(20) DEFAULT NULL,         -- G. โทรศัพท์ผู้ปกครอง
  `group_id` varchar(20) DEFAULT NULL,             -- H. รหัสกลุ่มเรียน
  `group_name` varchar(100) DEFAULT NULL,          -- I. ชื่อกลุ่มเรียน
  `department` varchar(100) DEFAULT NULL,          -- J. สาขาวิชา
  `status` varchar(50) DEFAULT 'ยังไม่ลงทะเบียน',
  `registered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`id_card`),
  INDEX (`student_id`),
  INDEX (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
