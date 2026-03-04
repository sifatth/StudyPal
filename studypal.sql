-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 07:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studypal`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminaction`
--

CREATE TABLE `adminaction` (
  `ActionID` int(9) NOT NULL,
  `AdminID` int(9) NOT NULL,
  `ReportID` int(9) NOT NULL,
  `ActionType` enum('delete','warn','ban_user','ignore') NOT NULL,
  `ActionTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminaction`
--

INSERT INTO `adminaction` (`ActionID`, `AdminID`, `ReportID`, `ActionType`, `ActionTime`) VALUES
(45, 1, 23, 'delete', '2025-09-02 03:23:05'),
(46, 1, 24, 'delete', '2025-10-11 22:06:55');

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE `answer` (
  `AnswerID` int(9) NOT NULL,
  `QuestionID` int(9) NOT NULL,
  `AnsweredBy` int(9) NOT NULL,
  `Content` text NOT NULL,
  `PostedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`AnswerID`, `QuestionID`, `AnsweredBy`, `Content`, `PostedAt`) VALUES
(12, 6, 16, 'To reduce data redundancy and improve data integrity by organizing columns and tables to ensure that dependencies are properly enforced by database integrity constraints.', '2025-09-02 02:30:03'),
(13, 6, 1, 'To increase the speed of all database queries by denormalizing the data, which adds redundant information to speed up specific types of queries.', '2025-09-02 02:30:33'),
(14, 7, 15, 'To uniquely identify each record in a table.', '2025-09-02 02:35:33'),
(15, 7, 16, 'To link two tables together by referencing the primary key of another table, thereby enforcing referential integrity.', '2025-09-02 02:35:58'),
(16, 7, 1, 'To create an index on a table to speed up query performance.', '2025-09-02 02:36:20');

-- --------------------------------------------------------

--
-- Table structure for table `groupmembership`
--

CREATE TABLE `groupmembership` (
  `MembershipID` int(9) NOT NULL,
  `UserID` int(9) NOT NULL,
  `GroupID` int(9) NOT NULL,
  `IsModerator` tinyint(1) NOT NULL DEFAULT 0,
  `JoinedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groupmembership`
--

INSERT INTO `groupmembership` (`MembershipID`, `UserID`, `GroupID`, `IsModerator`, `JoinedAt`) VALUES
(11, 1, 7, 0, '2025-09-02 00:39:21'),
(12, 1, 8, 0, '2025-09-02 00:40:51'),
(13, 15, 7, 0, '2025-09-02 00:47:02'),
(14, 15, 8, 0, '2025-09-02 00:47:09'),
(15, 15, 9, 0, '2025-09-02 00:47:44'),
(16, 15, 10, 0, '2025-09-02 00:55:06'),
(17, 16, 9, 0, '2025-09-02 00:57:18'),
(19, 16, 8, 0, '2025-09-02 00:57:28'),
(20, 16, 7, 0, '2025-09-02 00:57:35'),
(21, 1, 9, 0, '2025-09-02 02:31:07'),
(22, 1, 10, 0, '2025-09-02 02:31:12'),
(23, 17, 7, 0, '2025-09-02 02:34:17'),
(24, 17, 10, 0, '2025-09-02 02:34:26'),
(25, 17, 8, 0, '2025-09-02 02:34:37'),
(26, 17, 9, 0, '2025-09-02 02:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `grouptag`
--

CREATE TABLE `grouptag` (
  `GroupTagID` int(9) NOT NULL,
  `GroupID` int(9) NOT NULL,
  `TagID` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material`
--

CREATE TABLE `material` (
  `MaterialID` int(9) NOT NULL,
  `GroupID` int(9) NOT NULL,
  `UploadedBy` int(9) NOT NULL,
  `MaterialType` enum('Link','PDF','PPT','DOC','Image','Video') NOT NULL,
  `Title` varchar(255) NOT NULL,
  `LinkURL` varchar(255) DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL,
  `UploadedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material`
--

INSERT INTO `material` (`MaterialID`, `GroupID`, `UploadedBy`, `MaterialType`, `Title`, `LinkURL`, `FilePath`, `UploadedAt`) VALUES
(24, 7, 1, 'PDF', 'DBMS Introduction', NULL, 'uploads/68b6040c05b27-1. Introduction_Ch1.pdf', '2025-09-02 02:37:32'),
(25, 7, 1, 'PDF', 'Basic SQL', NULL, 'uploads/68b60ae1598fa-11. Basic SQL_Ch6.pdf', '2025-09-02 03:06:41'),
(27, 7, 1, 'Link', 'B Tree & B+ Tree', 'https://youtu.be/aZjYr87r1b8?si=GHEsvOGStVOJTB7N', NULL, '2025-09-02 03:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `otpverification`
--

CREATE TABLE `otpverification` (
  `OTP_ID` int(9) NOT NULL,
  `UserID` int(9) NOT NULL,
  `OTPCode` int(9) NOT NULL,
  `GeneratedAt` datetime DEFAULT current_timestamp(),
  `IsUsed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otpverification`
--

INSERT INTO `otpverification` (`OTP_ID`, `UserID`, `OTPCode`, `GeneratedAt`, `IsUsed`) VALUES
(2, 15, 278765, '2025-09-02 00:38:26', 1),
(3, 16, 463324, '2025-09-02 00:57:15', 1),
(4, 17, 857057, '2025-09-02 02:33:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `QuestionID` int(9) NOT NULL,
  `GroupID` int(9) NOT NULL,
  `AskedBy` int(9) NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Description` text NOT NULL,
  `PostedAt` datetime DEFAULT current_timestamp(),
  `IsClosed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`QuestionID`, `GroupID`, `AskedBy`, `Title`, `Description`, `PostedAt`, `IsClosed`) VALUES
(6, 7, 15, 'What are the primary purpose of database normalization?', '', '2025-09-02 02:23:59', 0),
(7, 7, 17, 'What is the primary function of a foreign key in a relational database?', '', '2025-09-02 02:35:03', 0);

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `ReportID` int(9) NOT NULL,
  `ReportedBy` int(9) NOT NULL,
  `ReportedUserID` int(9) NOT NULL,
  `TID` int(9) NOT NULL,
  `ReportTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('pending','resolved') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`ReportID`, `ReportedBy`, `ReportedUserID`, `TID`, `ReportTime`, `Status`) VALUES
(23, 16, 17, 30, '2025-09-01 21:20:39', 'resolved'),
(24, 16, 17, 31, '2025-10-11 16:06:32', 'resolved');

-- --------------------------------------------------------

--
-- Table structure for table `searchhistory`
--

CREATE TABLE `searchhistory` (
  `SearchID` int(9) NOT NULL,
  `UserID` int(9) NOT NULL,
  `SearchTerm` varchar(100) NOT NULL,
  `SearchType` enum('material','question','group','user') NOT NULL,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `searchhistory`
--

INSERT INTO `searchhistory` (`SearchID`, `UserID`, `SearchTerm`, `SearchType`, `Timestamp`) VALUES
(12, 15, 'os', 'group', '2025-09-02 00:51:02'),
(13, 1, 'dsa', 'group', '2025-09-02 02:31:39'),
(14, 1, 'normal', 'question', '2025-09-02 02:36:46'),
(15, 1, 'dbms', 'material', '2025-09-02 03:06:51');

-- --------------------------------------------------------

--
-- Table structure for table `studygroup`
--

CREATE TABLE `studygroup` (
  `GroupID` int(9) NOT NULL,
  `GroupName` varchar(30) NOT NULL,
  `Description` text DEFAULT NULL,
  `CreatedBy` int(9) NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `IsActive` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `studygroup`
--

INSERT INTO `studygroup` (`GroupID`, `GroupName`, `Description`, `CreatedBy`, `CreatedAt`, `IsActive`) VALUES
(7, 'Database Management Systems', 'DBMS', 1, '2025-09-02 00:39:21', 1),
(8, 'Operating System Design', 'OS Design', 1, '2025-09-02 00:40:51', 1),
(9, 'C++', 'Programming Language C++', 15, '2025-09-02 00:47:44', 1),
(10, 'Data Structure & Algorithms', 'DSA', 15, '2025-09-02 00:55:06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `TagID` int(9) NOT NULL,
  `TagName` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `target`
--

CREATE TABLE `target` (
  `TID` int(9) NOT NULL,
  `MaterialID` int(9) DEFAULT NULL,
  `QuestionID` int(9) DEFAULT NULL,
  `AnswerID` int(9) DEFAULT NULL,
  `UserID` int(9) DEFAULT NULL,
  `TargetType` enum('question','answer','material','user') NOT NULL,
  `Reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `target`
--

INSERT INTO `target` (`TID`, `MaterialID`, `QuestionID`, `AnswerID`, `UserID`, `TargetType`, `Reason`) VALUES
(30, NULL, NULL, NULL, NULL, 'question', 'spammer'),
(31, NULL, NULL, NULL, NULL, 'answer', 'irrelevent');

-- --------------------------------------------------------

--
-- Table structure for table `userprofile`
--

CREATE TABLE `userprofile` (
  `ProfileID` int(9) NOT NULL,
  `UserID` int(9) DEFAULT NULL,
  `Name` varchar(30) DEFAULT NULL,
  `Email` varchar(30) DEFAULT NULL,
  `Gender` enum('male','female','other') NOT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `University` varchar(30) DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userprofile`
--

INSERT INTO `userprofile` (`ProfileID`, `UserID`, `Name`, `Email`, `Gender`, `DateOfBirth`, `University`, `UpdatedAt`) VALUES
(1, 1, 'Husain Kabir', 'husainkabir27@gmail.com', 'male', '2004-06-27', 'North South University', '2025-08-28 02:50:39'),
(7, 15, 'Rafi Ahmed', 'rafiahmed@gmail.com', 'male', '2001-05-26', 'North South University', '2025-09-02 00:51:51'),
(8, 16, 'Wakil Mahmud', 'wakilmahmud@gmail.com', 'male', '2000-11-06', 'North South University', '2025-09-02 00:57:15'),
(9, 17, 'Abid Shahriar', 'abidshahriar@gmail.com', 'male', '2001-02-13', 'North South University', '2025-09-02 02:33:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(9) NOT NULL,
  `Email` varchar(30) NOT NULL,
  `Passwords` varchar(30) NOT NULL,
  `IsAdmin` tinyint(1) DEFAULT 0,
  `IsActive` tinyint(1) DEFAULT 1,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `LastLogin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Email`, `Passwords`, `IsAdmin`, `IsActive`, `CreatedAt`, `LastLogin`) VALUES
(1, 'husainkabir27@gmail.com', 'zaxscdvf', 1, 1, '2025-08-28 02:47:10', '2025-10-11 22:06:49'),
(15, 'rafiahmed@gmail.com', 'poilkjmn', 0, 1, '2025-09-02 00:38:26', '2025-09-02 03:17:22'),
(16, 'wakilmahmud@gmail.com', 'azxcvbnm', 0, 1, '2025-09-02 00:57:15', '2025-10-11 22:05:59'),
(17, 'abidshahriar@gmail.com', 'asdfghjkl', 0, 1, '2025-09-02 02:33:48', '2025-10-11 22:05:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminaction`
--
ALTER TABLE `adminaction`
  ADD PRIMARY KEY (`ActionID`),
  ADD KEY `ReportID` (`ReportID`),
  ADD KEY `AdminID` (`AdminID`) USING BTREE;

--
-- Indexes for table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`AnswerID`),
  ADD KEY `QuestionID` (`QuestionID`),
  ADD KEY `AnsweredBy` (`AnsweredBy`);

--
-- Indexes for table `groupmembership`
--
ALTER TABLE `groupmembership`
  ADD PRIMARY KEY (`MembershipID`),
  ADD UNIQUE KEY `UserID` (`UserID`,`GroupID`),
  ADD KEY `GroupID` (`GroupID`);

--
-- Indexes for table `grouptag`
--
ALTER TABLE `grouptag`
  ADD PRIMARY KEY (`GroupTagID`),
  ADD KEY `GroupID` (`GroupID`),
  ADD KEY `TagID` (`TagID`);

--
-- Indexes for table `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`MaterialID`),
  ADD KEY `GroupID` (`GroupID`),
  ADD KEY `UploadedBy` (`UploadedBy`);

--
-- Indexes for table `otpverification`
--
ALTER TABLE `otpverification`
  ADD PRIMARY KEY (`OTP_ID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`QuestionID`),
  ADD KEY `GroupID` (`GroupID`),
  ADD KEY `AskedBy` (`AskedBy`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `ReportedBy` (`ReportedBy`),
  ADD KEY `ReportedUserID` (`ReportedUserID`),
  ADD KEY `TID` (`TID`);

--
-- Indexes for table `searchhistory`
--
ALTER TABLE `searchhistory`
  ADD PRIMARY KEY (`SearchID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `studygroup`
--
ALTER TABLE `studygroup`
  ADD PRIMARY KEY (`GroupID`),
  ADD KEY `CreatedBy` (`CreatedBy`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`TagID`);

--
-- Indexes for table `target`
--
ALTER TABLE `target`
  ADD PRIMARY KEY (`TID`),
  ADD KEY `fk_target_mid` (`MaterialID`),
  ADD KEY `fk_target_qid` (`QuestionID`),
  ADD KEY `fk_target_aid` (`AnswerID`),
  ADD KEY `fk_target_uid` (`UserID`);

--
-- Indexes for table `userprofile`
--
ALTER TABLE `userprofile`
  ADD PRIMARY KEY (`ProfileID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `Email` (`Email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminaction`
--
ALTER TABLE `adminaction`
  MODIFY `ActionID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `answer`
--
ALTER TABLE `answer`
  MODIFY `AnswerID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `groupmembership`
--
ALTER TABLE `groupmembership`
  MODIFY `MembershipID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `grouptag`
--
ALTER TABLE `grouptag`
  MODIFY `GroupTagID` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material`
--
ALTER TABLE `material`
  MODIFY `MaterialID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `otpverification`
--
ALTER TABLE `otpverification`
  MODIFY `OTP_ID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `QuestionID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `ReportID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `searchhistory`
--
ALTER TABLE `searchhistory`
  MODIFY `SearchID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `studygroup`
--
ALTER TABLE `studygroup`
  MODIFY `GroupID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `TagID` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `target`
--
ALTER TABLE `target`
  MODIFY `TID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `userprofile`
--
ALTER TABLE `userprofile`
  MODIFY `ProfileID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adminaction`
--
ALTER TABLE `adminaction`
  ADD CONSTRAINT `adminaction_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `adminaction_ibfk_2` FOREIGN KEY (`ReportID`) REFERENCES `report` (`ReportID`) ON DELETE CASCADE;

--
-- Constraints for table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `question` (`QuestionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `answer_ibfk_2` FOREIGN KEY (`AnsweredBy`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `groupmembership`
--
ALTER TABLE `groupmembership`
  ADD CONSTRAINT `groupmembership_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupmembership_ibfk_2` FOREIGN KEY (`GroupID`) REFERENCES `studygroup` (`GroupID`) ON DELETE CASCADE;

--
-- Constraints for table `grouptag`
--
ALTER TABLE `grouptag`
  ADD CONSTRAINT `grouptag_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `studygroup` (`GroupID`),
  ADD CONSTRAINT `grouptag_ibfk_2` FOREIGN KEY (`TagID`) REFERENCES `tag` (`TagID`);

--
-- Constraints for table `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `material_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `studygroup` (`GroupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_ibfk_2` FOREIGN KEY (`UploadedBy`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `otpverification`
--
ALTER TABLE `otpverification`
  ADD CONSTRAINT `otpverification_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `studygroup` (`GroupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_ibfk_2` FOREIGN KEY (`AskedBy`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`ReportedBy`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`ReportedUserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_ibfk_3` FOREIGN KEY (`TID`) REFERENCES `target` (`TID`) ON DELETE CASCADE;

--
-- Constraints for table `searchhistory`
--
ALTER TABLE `searchhistory`
  ADD CONSTRAINT `searchhistory_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `studygroup`
--
ALTER TABLE `studygroup`
  ADD CONSTRAINT `studygroup_ibfk_1` FOREIGN KEY (`CreatedBy`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `target`
--
ALTER TABLE `target`
  ADD CONSTRAINT `fk_target_aid` FOREIGN KEY (`AnswerID`) REFERENCES `answer` (`AnswerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_target_mid` FOREIGN KEY (`MaterialID`) REFERENCES `material` (`MaterialID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_target_qid` FOREIGN KEY (`QuestionID`) REFERENCES `question` (`QuestionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_target_uid` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `userprofile`
--
ALTER TABLE `userprofile`
  ADD CONSTRAINT `userprofile_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `userprofile_ibfk_2` FOREIGN KEY (`Email`) REFERENCES `users` (`Email`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
