-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Sep 28, 2025 at 02:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_class_scheduler`
--
CREATE DATABASE IF NOT EXISTS `student_class_scheduler` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `student_class_scheduler`;

-- --------------------------------------------------------

--
-- Table structure for table `tblclasses`
--

CREATE TABLE `tblclasses` (
  `cid` int(11) NOT NULL,
  `className` varchar(254) NOT NULL,
  `professorID` int(11) DEFAULT NULL,
  `dayOfWeek` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `slots` int(11) NOT NULL DEFAULT 1,
  `semester` enum('Fall','Winter','Spring') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblclasses`
--

INSERT INTO `tblclasses` (`cid`, `className`, `professorID`, `dayOfWeek`, `slots`, `semester`) VALUES
(1, 'Math 101', 1, 'Monday', 3, 'Fall'),
(2, 'Math 101', 2, 'Wednesday', 2, 'Fall'),
(3, 'Math 101', 1, 'Tuesday', 3, 'Spring'),
(4, 'Math 101', 3, 'Thursday', 2, 'Winter'),
(5, 'Math 102', 2, 'Thursday', 2, 'Winter'),
(6, 'Math 102', 5, 'Friday', 3, 'Spring'),
(7, 'English 101', 3, 'Monday', 2, 'Fall'),
(8, 'English 101', 4, 'Friday', 2, 'Fall'),
(9, 'English 101', 3, 'Friday', 2, 'Spring'),
(10, 'English 101', 5, 'Wednesday', 3, 'Winter'),
(11, 'History 101', 4, 'Tuesday', 3, 'Fall'),
(12, 'History 101', 5, 'Thursday', 2, 'Winter'),
(13, 'History 101', 1, 'Monday', 3, 'Spring'),
(14, 'Computer Science 101', 5, 'Wednesday', 3, 'Fall'),
(15, 'Computer Science 101', 2, 'Friday', 3, 'Spring'),
(16, 'Computer Science 101', 4, 'Tuesday', 2, 'Winter'),
(17, 'Physics 101', 1, 'Monday', 2, 'Winter'),
(18, 'Physics 101', 2, 'Thursday', 2, 'Spring'),
(19, 'Biology 101', 2, 'Thursday', 2, 'Spring'),
(20, 'Biology 101', 3, 'Wednesday', 3, 'Fall'),
(21, 'Chemistry 101', 4, 'Tuesday', 2, 'Fall'),
(22, 'Chemistry 101', 5, 'Friday', 2, 'Spring'),
(23, 'Psychology 101', 5, 'Thursday', 2, 'Winter'),
(24, 'Psychology 101', 1, 'Tuesday', 3, 'Spring'),
(25, 'Economics 101', 3, 'Wednesday', 2, 'Fall'),
(26, 'Economics 101', 4, 'Friday', 2, 'Winter');

-- --------------------------------------------------------

--
-- Table structure for table `tblenrollment`
--

CREATE TABLE `tblenrollment` (
  `eid` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `classID` int(11) NOT NULL,
  `status` enum('enrolled','waitlist') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `waitlist_position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblenrollment`
--

INSERT INTO `tblenrollment` (`eid`, `studentID`, `classID`, `status`, `created_at`, `waitlist_position`) VALUES
(1, 1, 1, 'enrolled', '2025-09-27 23:54:33', NULL),
(2, 1, 8, 'enrolled', '2025-09-27 23:54:33', NULL),
(3, 1, 20, 'enrolled', '2025-09-27 23:54:34', NULL),
(4, 1, 21, 'enrolled', '2025-09-27 23:54:34', NULL),
(5, 1, 25, 'enrolled', '2025-09-27 23:54:34', NULL),
(6, 2, 8, 'enrolled', '2025-09-27 23:58:03', NULL),
(7, 2, 11, 'enrolled', '2025-09-27 23:58:03', NULL),
(8, 2, 21, 'enrolled', '2025-09-27 23:58:03', NULL),
(9, 2, 25, 'enrolled', '2025-09-27 23:58:03', NULL),
(122, 2, 4, 'enrolled', '2025-09-28 00:15:13', NULL),
(123, 2, 16, 'enrolled', '2025-09-28 00:15:13', NULL),
(125, 5, 14, 'enrolled', '2025-09-28 00:17:09', NULL),
(126, 5, 21, 'waitlist', '2025-09-28 00:17:09', 1),
(127, 5, 25, 'waitlist', '2025-09-28 00:17:09', 1),
(129, 5, 1, 'enrolled', '2025-09-28 00:18:47', NULL),
(130, 6, 3, 'enrolled', '2025-09-28 00:20:06', NULL),
(131, 6, 9, 'enrolled', '2025-09-28 00:20:06', NULL),
(132, 6, 13, 'enrolled', '2025-09-28 00:20:06', NULL),
(133, 6, 15, 'enrolled', '2025-09-28 00:20:06', NULL),
(134, 6, 19, 'enrolled', '2025-09-28 00:20:06', NULL),
(135, 6, 22, 'enrolled', '2025-09-28 00:20:06', NULL),
(136, 6, 14, 'enrolled', '2025-09-28 00:20:28', NULL),
(137, 6, 20, 'enrolled', '2025-09-28 00:20:28', NULL),
(138, 6, 21, 'waitlist', '2025-09-28 00:20:28', 2),
(139, 6, 25, 'waitlist', '2025-09-28 00:20:28', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tblnotifications`
--

CREATE TABLE `tblnotifications` (
  `id` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `classID` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblprofessor`
--

CREATE TABLE `tblprofessor` (
  `professorID` int(11) NOT NULL,
  `profFName` varchar(254) NOT NULL,
  `profLName` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblprofessor`
--

INSERT INTO `tblprofessor` (`professorID`, `profFName`, `profLName`) VALUES
(1, 'Sarah', 'Johnson'),
(2, 'Daniel', 'Kim'),
(3, 'Emily', 'Martinez'),
(4, 'Robert', 'Davis'),
(5, 'Hannah', 'Thompson');

-- --------------------------------------------------------

--
-- Table structure for table `tblstudent`
--

CREATE TABLE `tblstudent` (
  `id` int(11) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(254) NOT NULL,
  `lastName` varchar(254) NOT NULL,
  `address` varchar(254) NOT NULL,
  `phone` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblstudent`
--

INSERT INTO `tblstudent` (`id`, `email`, `password`, `firstName`, `lastName`, `address`, `phone`) VALUES
(1, 'olivia.reed@example.edu', '$2y$10$KMgV47euq7Z7r9H3PYC0f.uLFMOwH87D9et9lxHfyG5WU/TveuIfm', 'Olivia', 'Reed', '128 Willow St, Springfield, IL 62704', '2175550143'),
(2, 'liam.garcia@example.edu', '$2y$10$tu.l0CdQntvhlhhT0HAf1.2ALdtROrgmFqqR4RZZWFa5xlqcPBpAC', 'Liam', 'Garcia', '402 Pine Ave, Portland, OR 97205', '5035550192'),
(5, 'emma.lopez@example.edu', '$2y$10$NSmcHx4C2V1x1ClsbYVP9OcrHfqE9gkAjSxvesz44Jsd3CKPMc9xW', 'Emma', 'Lopez', '57 Oak Drive, Austin, TX 78701', '5125550128'),
(6, 'noah.patel@example.edu', '$2y$10$6fcit4186TqOkeJpbDilN.Ugd1m.LOw4/UabxrTyaCs0egvHEaCBW', 'Noah', 'Patel', '880 Maple Rd, Columbus, OH 43215', '6145550176');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblclasses`
--
ALTER TABLE `tblclasses`
  ADD PRIMARY KEY (`cid`),
  ADD KEY `professorID` (`professorID`);

--
-- Indexes for table `tblenrollment`
--
ALTER TABLE `tblenrollment`
  ADD PRIMARY KEY (`eid`),
  ADD UNIQUE KEY `studentID` (`studentID`,`classID`),
  ADD KEY `classID` (`classID`);

--
-- Indexes for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `classID` (`classID`);

--
-- Indexes for table `tblprofessor`
--
ALTER TABLE `tblprofessor`
  ADD PRIMARY KEY (`professorID`);

--
-- Indexes for table `tblstudent`
--
ALTER TABLE `tblstudent`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblclasses`
--
ALTER TABLE `tblclasses`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tblenrollment`
--
ALTER TABLE `tblenrollment`
  MODIFY `eid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblprofessor`
--
ALTER TABLE `tblprofessor`
  MODIFY `professorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblstudent`
--
ALTER TABLE `tblstudent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblclasses`
--
ALTER TABLE `tblclasses`
  ADD CONSTRAINT `tblclasses_ibfk_1` FOREIGN KEY (`professorID`) REFERENCES `tblprofessor` (`professorID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tblenrollment`
--
ALTER TABLE `tblenrollment`
  ADD CONSTRAINT `tblenrollment_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `tblstudent` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblenrollment_ibfk_2` FOREIGN KEY (`classID`) REFERENCES `tblclasses` (`cid`) ON DELETE CASCADE;

--
-- Constraints for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  ADD CONSTRAINT `tblnotifications_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `tblstudent` (`id`),
  ADD CONSTRAINT `tblnotifications_ibfk_2` FOREIGN KEY (`classID`) REFERENCES `tblclasses` (`cid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
