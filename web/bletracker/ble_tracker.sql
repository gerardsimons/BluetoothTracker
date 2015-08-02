-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 02, 2015 at 09:50 PM
-- Server version: 5.6.25
-- PHP Version: 5.5.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ble_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `ble_tags`
--

CREATE TABLE IF NOT EXISTS `ble_tags` (
  `ID` int(11) NOT NULL,
  `Company_ID` int(11) NOT NULL,
  `Last_Updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Name` varchar(128) NOT NULL,
  `Mac_Address` varchar(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ble_tags`
--

INSERT INTO `ble_tags` (`ID`, `Company_ID`, `Last_Updated`, `Name`, `Mac_Address`) VALUES
(1, 1, '2015-07-27 09:50:47', 'whereAt T', 'ED779659D1F1'),
(2, 1, '2015-07-27 09:53:59', 'whereAt T', 'C5E51459A0A7');

-- --------------------------------------------------------

--
-- Table structure for table `ble_trackers`
--

CREATE TABLE IF NOT EXISTS `ble_trackers` (
  `ID` int(11) NOT NULL,
  `Device_ID` varchar(103) NOT NULL,
  `Install_ID` varchar(36) NOT NULL,
  `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ble_trackers`
--

INSERT INTO `ble_trackers` (`ID`, `Device_ID`, `Install_ID`, `Created`) VALUES
(4, 'testDevice', 'testInstall', '2015-07-28 17:32:19'),
(5, 'LGD855c16e0b3', '1f1d7644-6973-49d3-afa9-fd434855457f', '2015-07-28 17:32:43'),
(6, 'testDevice', 'testInstall2', '2015-07-29 18:16:37');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE IF NOT EXISTS `companies` (
  `ID` int(11) NOT NULL,
  `Subscription_ID` int(11) NOT NULL,
  `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Name` varchar(128) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`ID`, `Subscription_ID`, `Created`, `Name`) VALUES
(1, 0, '2015-07-25 14:38:58', 'DB Schenker');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE IF NOT EXISTS `customers` (
  `ID` int(11) NOT NULL,
  `Location_ID` int(11) NOT NULL,
  `Name` varchar(128) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`ID`, `Location_ID`, `Name`) VALUES
(127860, 1, 'whereAt Industries');

-- --------------------------------------------------------

--
-- Table structure for table `gps_data`
--

CREATE TABLE IF NOT EXISTS `gps_data` (
  `ID` int(11) NOT NULL,
  `Time_Read` datetime NOT NULL,
  `Route_ID` int(11) NOT NULL,
  `Latitude` float(10,6) NOT NULL,
  `Longitude` float(10,6) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `ID` int(11) NOT NULL,
  `Name` varchar(128) NOT NULL,
  `Latitude` decimal(18,9) NOT NULL,
  `Longitude` decimal(18,9) NOT NULL,
  `Street` varchar(128) NOT NULL,
  `Street_Number` smallint(6) NOT NULL,
  `City` varchar(128) NOT NULL,
  `Zip_Code` varchar(10) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`ID`, `Name`, `Latitude`, `Longitude`, `Street`, `Street_Number`, `City`, `Zip_Code`) VALUES
(1, 'Yes!Delft', '51.993161900', '4.386655900', 'Molengraaffsingel', 12, 'Delft', '2629JD');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `ID` int(11) NOT NULL,
  `Customer_ID` int(11) NOT NULL,
  `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=30033001 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ID`, `Customer_ID`, `Created`) VALUES
(30033000, 127860, '2015-08-02 17:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `order_cases`
--

CREATE TABLE IF NOT EXISTS `order_cases` (
  `ID` int(11) NOT NULL,
  `Order_ID` int(11) NOT NULL DEFAULT '0',
  `BLE_Tag_ID` int(11) NOT NULL,
  `Route_ID` int(11) DEFAULT NULL,
  `Bar_Code` varchar(32) NOT NULL,
  `Status` enum('EN_ROUTE','FINISHED','IDLE') NOT NULL DEFAULT 'IDLE',
  `Start` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `End` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_cases`
--

INSERT INTO `order_cases` (`ID`, `Order_ID`, `BLE_Tag_ID`, `Route_ID`, `Bar_Code`, `Status`, `Start`, `End`) VALUES
(20, 30033000, 1, NULL, '1678127860003003300020', 'IDLE', '2015-08-02 17:52:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE IF NOT EXISTS `routes` (
  `ID` int(11) NOT NULL,
  `BLE_Tracker_ID` int(11) NOT NULL,
  `Start` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `End` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rssi_data`
--

CREATE TABLE IF NOT EXISTS `rssi_data` (
  `ID` int(11) NOT NULL,
  `Route_ID` int(11) NOT NULL,
  `BLE_Tag_ID` int(11) NOT NULL,
  `Time_Read` datetime NOT NULL,
  `RSSI` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `ID` int(11) NOT NULL,
  `API_Key` varchar(64) NOT NULL,
  `State` enum('ACTIVE','SUSPENDED','','') NOT NULL DEFAULT 'ACTIVE',
  `Start_Date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Expiry_Date` datetime NOT NULL,
  `Max_Users` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`ID`, `API_Key`, `State`, `Start_Date`, `Expiry_Date`, `Max_Users`) VALUES
(0, ']wv2Np:c@e8V9@>r37g){18u.32lY', 'ACTIVE', '2015-07-26 01:04:51', '2015-09-01 00:00:00', 25);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL,
  `Company_ID` int(11) NOT NULL,
  `Username` varchar(64) NOT NULL,
  `Password` varchar(64) NOT NULL,
  `Email` varchar(64) NOT NULL,
  `Created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `waypoints`
--

CREATE TABLE IF NOT EXISTS `waypoints` (
  `ID` int(11) NOT NULL,
  `Location_ID` int(11) NOT NULL,
  `Rank` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ble_tags`
--
ALTER TABLE `ble_tags`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Mac_Address` (`Mac_Address`),
  ADD KEY `Company_ID` (`Company_ID`);

--
-- Indexes for table `ble_trackers`
--
ALTER TABLE `ble_trackers`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Install_ID` (`Install_ID`,`Device_ID`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Subscription` (`Subscription_ID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Location_ID` (`Location_ID`);

--
-- Indexes for table `gps_data`
--
ALTER TABLE `gps_data`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Route_ID` (`Route_ID`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Customer_ID` (`Customer_ID`),
  ADD KEY `Customer_ID_2` (`Customer_ID`);

--
-- Indexes for table `order_cases`
--
ALTER TABLE `order_cases`
  ADD PRIMARY KEY (`ID`,`Order_ID`),
  ADD UNIQUE KEY `Bar_Code` (`Bar_Code`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `BLE_Tag_ID` (`BLE_Tag_ID`),
  ADD KEY `Route_ID` (`Route_ID`),
  ADD KEY `BLE_Tag_ID_2` (`BLE_Tag_ID`),
  ADD KEY `Route_ID_2` (`Route_ID`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `BLE_Tracker_ID` (`BLE_Tracker_ID`);

--
-- Indexes for table `rssi_data`
--
ALTER TABLE `rssi_data`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Route_ID` (`Route_ID`),
  ADD KEY `BLE_Tag_ID` (`BLE_Tag_ID`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `API_Key` (`API_Key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `ToCompany` (`Company_ID`);

--
-- Indexes for table `waypoints`
--
ALTER TABLE `waypoints`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Location_ID` (`Location_ID`),
  ADD KEY `Location_ID_2` (`Location_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ble_tags`
--
ALTER TABLE `ble_tags`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `ble_trackers`
--
ALTER TABLE `ble_trackers`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `gps_data`
--
ALTER TABLE `gps_data`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=95;
--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30033001;
--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=55;
--
-- AUTO_INCREMENT for table `rssi_data`
--
ALTER TABLE `rssi_data`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `waypoints`
--
ALTER TABLE `waypoints`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `gps_data`
--
ALTER TABLE `gps_data`
  ADD CONSTRAINT `to_routes` FOREIGN KEY (`Route_ID`) REFERENCES `routes` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `order_cases`
--
ALTER TABLE `order_cases`
  ADD CONSTRAINT `to_order` FOREIGN KEY (`Order_ID`) REFERENCES `orders` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `to_ble_trackers` FOREIGN KEY (`BLE_Tracker_ID`) REFERENCES `ble_trackers` (`ID`);

--
-- Constraints for table `rssi_data`
--
ALTER TABLE `rssi_data`
  ADD CONSTRAINT `rssi_to_ble_tag` FOREIGN KEY (`BLE_Tag_ID`) REFERENCES `ble_tags` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `rssi_to_routes` FOREIGN KEY (`Route_ID`) REFERENCES `routes` (`ID`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
