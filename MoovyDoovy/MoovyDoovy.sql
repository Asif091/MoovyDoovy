-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 01:20 PM
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
-- Database: `cinemadb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `joining_date` date NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `address` varchar(255) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `ticket_price` decimal(8,2) NOT NULL,
  `show_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_seat_no`
--

CREATE TABLE `booking_seat_no` (
  `booking_id` int(11) NOT NULL,
  `booking_seat_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

CREATE TABLE `card` (
  `transaction_id` int(11) NOT NULL,
  `card_no` varchar(20) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `card_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `user_id` int(11) NOT NULL,
  `customer_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hall`
--

CREATE TABLE `hall` (
  `hall_id` int(11) NOT NULL,
  `hall_name` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hall_seat_no`
--

CREATE TABLE `hall_seat_no` (
  `hall_id` int(11) NOT NULL,
  `hall_seat_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_wallet`
--

CREATE TABLE `mobile_wallet` (
  `transaction_id` int(11) NOT NULL,
  `phone_no` varchar(11) NOT NULL,
  `company_name` varchar(100) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `movie`
--

CREATE TABLE `movie` (
  `movie_name` varchar(100) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL,
  `release_date` date NOT NULL,
  `details` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `transaction_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `showtime`
--

CREATE TABLE `showtime` (
  `show_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `movie_name` varchar(100) NOT NULL,
  `hall_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `password` varchar(100) NOT NULL CHECK (`phone` is null or `phone` like '01%' and char_length(`phone`) = 11)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `show_id` (`show_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `booking_seat_no`
--
ALTER TABLE `booking_seat_no`
  ADD PRIMARY KEY (`booking_id`,`booking_seat_no`);

--
-- Indexes for table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`transaction_id`,`card_no`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `hall`
--
ALTER TABLE `hall`
  ADD PRIMARY KEY (`hall_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hall_seat_no`
--
ALTER TABLE `hall_seat_no`
  ADD PRIMARY KEY (`hall_id`,`hall_seat_no`);

--
-- Indexes for table `mobile_wallet`
--
ALTER TABLE `mobile_wallet`
  ADD PRIMARY KEY (`transaction_id`,`phone_no`);

--
-- Indexes for table `movie`
--
ALTER TABLE `movie`
  ADD PRIMARY KEY (`movie_name`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `showtime`
--
ALTER TABLE `showtime`
  ADD PRIMARY KEY (`show_id`),
  ADD KEY `movie_name` (`movie_name`),
  ADD KEY `hall_id` (`hall_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hall`
--
ALTER TABLE `hall`
  MODIFY `hall_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `showtime`
--
ALTER TABLE `showtime`
  MODIFY `show_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `showtime` (`show_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_seat_no`
--
ALTER TABLE `booking_seat_no`
  ADD CONSTRAINT `booking_seat_no_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `card`
--
ALTER TABLE `card`
  ADD CONSTRAINT `card_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `payment` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hall`
--
ALTER TABLE `hall`
  ADD CONSTRAINT `hall_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `hall_seat_no`
--
ALTER TABLE `hall_seat_no`
  ADD CONSTRAINT `hall_seat_no_ibfk_1` FOREIGN KEY (`hall_id`) REFERENCES `hall` (`hall_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mobile_wallet`
--
ALTER TABLE `mobile_wallet`
  ADD CONSTRAINT `mobile_wallet_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `payment` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `movie`
--
ALTER TABLE `movie`
  ADD CONSTRAINT `movie_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `showtime`
--
ALTER TABLE `showtime`
  ADD CONSTRAINT `showtime_ibfk_1` FOREIGN KEY (`movie_name`) REFERENCES `movie` (`movie_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `showtime_ibfk_2` FOREIGN KEY (`hall_id`) REFERENCES `hall` (`hall_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `showtime_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `admin` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
