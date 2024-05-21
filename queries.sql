-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2024 at 07:19 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `restaurant_reservations`
--

-- --------------------------------------------------------

--
-- Table structure for table `liked_restaurants`
--

CREATE TABLE `liked_restaurants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `liked_restaurants`
--

INSERT INTO `liked_restaurants` (`id`, `user_id`, `restaurant_id`) VALUES
(1, 4, 7),
(2, 1, 7),
(3, 4, 7);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `full_name` varchar(255) NOT NULL,
  `seating` enum('outdoor','indoor') NOT NULL,
  `special_requests` text DEFAULT NULL,
  `birthdate` date NOT NULL,
  `phone` varchar(20) NOT NULL,
  `num_people` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `restaurant_id`, `reservation_date`, `reservation_time`, `status`, `full_name`, `seating`, `special_requests`, `birthdate`, `phone`, `num_people`) VALUES
(1, 5, 7, '2024-05-30', '22:00:00', 'pending', 'kevin elie halby', 'indoor', 'nothing', '2024-05-01', '', 0),
(2, 4, 7, '2024-05-08', '07:46:00', 'pending', 'maria', 'indoor', 'none', '2003-01-02', '123123123', 2),
(3, 4, 7, '2024-05-24', '21:00:00', 'pending', 'asd', 'outdoor', 'noen', '2024-05-03', '70199287', 2),
(4, 5, 7, '2024-05-25', '10:00:00', 'pending', 'kevin elie halby', 'outdoor', 'none', '2001-11-02', '70199287', 2);

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `bio` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `address`, `phone`, `bio`, `logo`, `user_id`, `location`) VALUES
(7, 'testing maps', 'skssk', '775566', 'ksks', NULL, 4, 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3021.702628276664!2d-74.0466891845932!3d40.68924927933451!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c250a5d4ef5eb5%3A0x1d8a7a2b8efbd3b0!2sStatue%20of%20Liberty!5e0!3m2!1sen!2sus!4v159890548');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` enum('user','restaurant') NOT NULL DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `birthdate`, `phone`, `email`, `password`, `account_type`, `profile_picture`) VALUES
(1, 'kevin ', '2001-06-11', '70199287', 'kevinhalby70199@gmail.com', '$2y$10$hsVhOlCePoTiCsbooDlqoeza/YrSCwLcZ0ATFtWH1mAeT.Kv4Y1Me', 'user', NULL),
(3, 'maria', '2001-11-02', '70199287', 'kevin1@hotmail.com', '$2y$10$wftKbws84OUHdNAQctTcAenUh.TqsCmxRgWvAd3O31zWtYCo3LfvG', 'restaurant', NULL),
(4, 'admin', '2001-01-11', '77', 'admin@hotmail.com', '$2y$10$9c/SfNPdIDHiISEVJpCJpuDIkM3/1Fa7IrkRKyBm0g4Kyy1yRsVK6', 'restaurant', NULL),
(5, 'testing', '2001-01-01', '774455', 'testing1@hotmail.com', '$2y$10$Ch/DDQATLax/S5pSvtUQvOy6CbRA9wKw.RQt3BlE7iKKlJs7MO0g6', 'user', NULL),
(6, 'maria', '2004-06-24', '76538828', 'maria@hotmail.com', '$2y$10$0KnA1614gn1w4V477VgFe.ykBfF6f1j67AlyAwH1Mw9S5fXv1wFb6', 'user', NULL),
(7, 'hanna issa', '2001-01-02', '70199287', 'hanna@hotmail.com', '$2y$10$Znzy49KRRIWWsTGjbBPWgueREJvpce8CZcQyJuq.SNDA2vYLUlHDK', 'user', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `liked_restaurants`
--
ALTER TABLE `liked_restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `liked_restaurants`
--
ALTER TABLE `liked_restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `liked_restaurants`
--
ALTER TABLE `liked_restaurants`
  ADD CONSTRAINT `liked_restaurants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `liked_restaurants_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`);
COMMIT;