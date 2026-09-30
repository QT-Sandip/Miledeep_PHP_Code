-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 25, 2022 at 08:09 AM
-- Server version: 10.5.12-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u177334400_serp`
--

-- --------------------------------------------------------

--
-- Table structure for table `AdminClientBasicDetails`
--

CREATE TABLE `AdminClientBasicDetails` (
  `BDetailsID` int(9) NOT NULL,
  `LogoUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `AppLogoUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThemeUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `BGImageUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ContactPerson` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MailID` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PhoneNumber` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ClientID` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminClientBasicDetails`
--

INSERT INTO `AdminClientBasicDetails` (`BDetailsID`, `LogoUrl`, `AppLogoUrl`, `ThemeUrl`, `BGImageUrl`, `ContactPerson`, `MailID`, `PhoneNumber`, `ClientID`) VALUES
(1, 'https://serp.silverlights.in/Images/logo.jpg', 'https://serp.silverlights.in/Images/sls_logo_trns.png', 'https://uiwrapper.silverlights.in/Basewrapper/Nazox_v1.0/Admin/Vertical/dist/', 'https://serp.silverlights.in/Images/sls_bg.png', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `AdminClientDbDetails`
--

CREATE TABLE `AdminClientDbDetails` (
  `DbID` int(9) NOT NULL,
  `ClientID` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DbHost` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DbName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DbUserName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DbPassword` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DbStatus` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminClientDbDetails`
--

INSERT INTO `AdminClientDbDetails` (`DbID`, `ClientID`, `DbHost`, `DbName`, `DbUserName`, `DbPassword`, `DbStatus`) VALUES
(1, 'serp', 'localhost', 'u177334400_serp', 'u177334400_serp', '@Silver@119@Serp', 1),
(2, 'SLS2021001', 'localhost', 'u177334400_jme_live', 'u177334400_jme_live', '_0d21gjF', 1),
(3, 'SLS2021005', 'localhost', 'u177334400_SaiMarineDev', 'u177334400_SaiMarineDev', '8u&A3CAQfIN', 1),
(4, 'SLS2021004', 'localhost', 'u177334400_jlsf', 'u177334400_jlsf', '2u*H:U4ooN2:', 1),
(5, 'SLS2021006', 'localhost', 'u177334400_Siri', 'u177334400_Siri', 'b2RWR8Y;', 1),
(6, 'SLS2021002', 'localhost', 'u177334400_moury', 'u177334400_moury', '_0d21gjF', 1),
(7, 'SLS2021007', 'localhost', 'u177334400_SatishMarine', 'u177334400_SatishMarine', ';egikX!gk9I', 1),
(8, 'SLS2021003', 'localhost', 'u177334400_sme', 'u177334400_sme', '_0d21gjF', 1),
(10, 'SilverLights', 'localhost', 'u177334400_SlssBilling', 'u177334400_SlssBilling', 'zyRK6c>s7x|S', 1);

-- --------------------------------------------------------

--
-- Table structure for table `AdminClientDetails`
--

CREATE TABLE `AdminClientDetails` (
  `ClientID` int(9) NOT NULL,
  `PurchaseCode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ShortName` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `LogoUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `AppLogoUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThemeUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `BGImageUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ContactPerson` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MailID` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PhoneNumber` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `InstallationDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrailStartDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrailEndDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `OpearationMode` int(1) NOT NULL COMMENT '0-Trail/1-Premimum',
  `ExpiryDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminClientDetails`
--

INSERT INTO `AdminClientDetails` (`ClientID`, `PurchaseCode`, `Name`, `ShortName`, `Address`, `LogoUrl`, `AppLogoUrl`, `ThemeUrl`, `BGImageUrl`, `ContactPerson`, `MailID`, `PhoneNumber`, `InstallationDate`, `TrailStartDate`, `TrailEndDate`, `OpearationMode`, `ExpiryDate`) VALUES
(1, '1', 'S-ERP', 'S_ERP', '', 'https://serp.silverlights.in/Images/logo.jpg', 'https://serp.silverlights.in/Images/sls_logo_trns.png', 'https://uiwrapper.silverlights.in/Basewrapper/Nazox_v1.0/Admin/Vertical/dist/', 'https://serp.silverlights.in/Images/sls_bg.png', '', '', '', '', '', '', 0, '2023-08-31');

-- --------------------------------------------------------

--
-- Table structure for table `AdminClientLicenseDetails`
--

CREATE TABLE `AdminClientLicenseDetails` (
  `licenseID` int(9) NOT NULL,
  `PurchaseCode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `InstallationDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrailStartDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrailEndDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `OpearationMode` int(1) NOT NULL COMMENT '0-Trail/1-Premimum',
  `ExpiryDate` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ClientID` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminClientLicenseDetails`
--

INSERT INTO `AdminClientLicenseDetails` (`licenseID`, `PurchaseCode`, `InstallationDate`, `TrailStartDate`, `TrailEndDate`, `OpearationMode`, `ExpiryDate`, `ClientID`, `Status`) VALUES
(1, '1', '', '', '', 0, '2023-08-31', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `AdminConfigDetails`
--

CREATE TABLE `AdminConfigDetails` (
  `ConfigID` int(9) NOT NULL,
  `ClientPath` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CurrentProjectPath` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ServerUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ApiUrl` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminConfigDetails`
--

INSERT INTO `AdminConfigDetails` (`ConfigID`, `ClientPath`, `CurrentProjectPath`, `ServerUrl`, `ApiUrl`) VALUES
(1, 'Clients', 'JME', 'https://serp.silverlights.in/', '');

-- --------------------------------------------------------

--
-- Table structure for table `AdminDepartments`
--

CREATE TABLE `AdminDepartments` (
  `DeptID` int(9) NOT NULL,
  `DepartmentName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Directory` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SideBarHeaderTbl` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SideBarOptionTbl` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminDepartments`
--

INSERT INTO `AdminDepartments` (`DeptID`, `DepartmentName`, `Directory`, `SideBarHeaderTbl`, `SideBarOptionTbl`) VALUES
(0, 'QUALITY CONTROL', 'QualityControl/', 'QcSideBarHeaders', 'QcSideBarOptions'),
(1, 'ADMINISTRATION', 'Admin/', 'AdminSideBarHeaders', 'AdminSideBarOptions'),
(2, 'QUALITY CONTROL', 'QualityControl/', 'QcSideBarHeaders', 'QcSideBarOptions'),
(3, 'PURCHASE', 'Purchase/', 'RmPurchaseSideBarHeaders', 'RmPurchaseSideBarOptions'),
(4, 'PRE PROCESSING', 'PreProcessing/', 'PreProSideBarHeaders', 'PreProSideBarOptions'),
(5, 'PRODUCTION', 'Production/', 'ProdSideBarHeaders', 'ProdSideBarOptions'),
(6, 'PACKING', 'Packing/DashBoard_Pur.php', 'PckSideBarHeaders', 'PckSideBarOptions'),
(7, 'COLD STORE', 'ColdStore/', 'CsSideBarHeaders', 'CsSideBarOptions'),
(8, 'Accounts', 'Accounts/', 'CostingSideBarHeaders', 'CostingSideBarOptions'),
(9, 'SALES', 'Sales/', 'ScrmSideBarHeaders', 'ScrmSideBarOptions'),
(10, 'HRM', 'Hrm/', 'AdminSideBarHeaders', 'AdminSideBarOptions');

-- --------------------------------------------------------

--
-- Table structure for table `AdminLoginDetails`
--

CREATE TABLE `AdminLoginDetails` (
  `UserID` int(9) NOT NULL,
  `DeptID` int(9) NOT NULL,
  `SubDeptID` int(9) NOT NULL,
  `DesignationID` varchar(35) NOT NULL,
  `UserName` varchar(250) NOT NULL,
  `AccessType` varchar(250) NOT NULL COMMENT '1// SUPER ADMIN 2//ADMIN, 3 USER',
  `DeviceID` text NOT NULL,
  `UserLoginID` varchar(250) NOT NULL,
  `UserPassword` varchar(250) NOT NULL,
  `UnitID` int(9) NOT NULL,
  `PhoneNumber` varchar(15) NOT NULL,
  `IsSe` varchar(10) NOT NULL DEFAULT '0',
  `LastLogin` varchar(30) NOT NULL,
  `Status` int(9) NOT NULL DEFAULT 1,
  `IsDev` int(3) NOT NULL COMMENT '1-Dev,0-Live',
  `ClientID` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `AdminLoginDetails`
--

INSERT INTO `AdminLoginDetails` (`UserID`, `DeptID`, `SubDeptID`, `DesignationID`, `UserName`, `AccessType`, `DeviceID`, `UserLoginID`, `UserPassword`, `UnitID`, `PhoneNumber`, `IsSe`, `LastLogin`, `Status`, `IsDev`, `ClientID`) VALUES
(1, 1, 0, 'Serp Admin', 'Silverlights', '1', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9642444934', '9642444934', 1, '', '0', '2022-11-25  12:54:31', 1, 0, 'serp'),
(2, 3, 0, 'Purchase Incharge', 'Rama Krishna', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9676591728', 'prk@1728', 1, '', '0', '2022-11-01  15:59:48', 1, 0, 'SLS2021001'),
(3, 3, 0, 'Purchase Manager', 'Madhu', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9848032969', 'Madhu$969', 1, '', '0', '2022-11-24  16:16:48', 1, 0, 'SLS2021001'),
(4, 3, 0, 'Purchase Manager', 'PanduRanga Rao', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '7098888999', 'pandu$1269', 1, '', '0', '2022-11-01  15:59:48', 1, 0, 'SLS2021001'),
(5, 7, 0, 'QC Documentation', 'DocUser2', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'saiCs', 'saiCs', 1, '', '0', '2022-01-21  23:34:16', 1, 0, 'SLS2021005'),
(6, 9, 0, 'Sales', 'SalesAdmin', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'SmeSalesAdmin', '$Suryasa1819$', 1, '', '0', '2022-11-25  11:58:05', 1, 0, 'SLS2021003'),
(7, 2, 0, 'QC Documentation', 'QcAdmin', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'jlsfQc', 'jlsfQc', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021004'),
(8, 10, 0, 'QC Documentation', 'SiriHr', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'SiriHr', 'SiriHr', 1, '', '0', '2022-11-25  10:44:33', 1, 0, 'SLS2021006'),
(9, 0, 0, 'QC Documentation', 'QcAdmin', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'JmQc', 'JmQc', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021001'),
(10, 10, 0, 'HR', 'HRM', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9494255041', 'hrjlsf', 1, '', '0', '2022-11-25  12:40:32', 1, 0, 'SLS2021004'),
(11, 10, 0, 'QC Documentation', 'JmHr', '3', 'dK3qSgLoxug:APA91bEeBYmKNeCAJaJoH5ECm50LllMvvzJs5GEsIL7uOx13Spagm_05T9VgrlMWf78ghygd2XZ23776KQDlBkJb2Q1Lh_SybkeOb5GY3mn0_ESgWIV-_oH_BRKanY24S1zFgjRAXCPc', 'JmHr', 'JmHr', 1, '', '0', '2022-11-25  10:30:22', 1, 0, 'SLS2021001'),
(12, 10, 0, 'HR', 'MouryaHr', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'MouryaHr', 'MouryaHr', 1, '', '0', '2022-11-25  10:40:53', 1, 0, 'SLS2021002'),
(13, 10, 0, 'HR', 'SatishHr', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'SatishHr', 'SatishHr', 1, '', '0', '2022-11-25  10:09:24', 1, 0, 'SLS2021007'),
(14, 10, 0, 'Admin', 'Bharath', '1', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9705111116', '9705111116', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021004'),
(15, 10, 0, 'Management', 'K Seetharamaiah', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9848177464', '9848177464', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021004'),
(16, 10, 0, 'Admin', 'Mahinadh', '1', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9848171665', '9848171665', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021004'),
(17, 10, 0, 'HR', 'HRM', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'jlsfhr', 'jlsfhr', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021004'),
(18, 2, 2, 'QC Documentation', 'Doc User', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'SmeDocUser', '$Suryadu1819$', 1, '', '0', '2022-11-25  13:05:45', 1, 0, 'SLS2021003'),
(19, 2, 1, 'QC Lab', 'Lab User', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'SmeLabUser', '$Suryalu901$', 1, '', '0', '2022-11-24  15:40:02', 1, 0, 'SLS2021003'),
(20, 9, 0, 'Sales', 'Super Admin', '1', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'SmeSADMIN2', '$Suryas2n901$', 1, '', '0', '2022-10-11  15:28:52', 1, 0, 'SLS2021003'),
(21, 3, 0, 'Purchase Incharge', 'YESHAYA', '3', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9676830959', 'Yesh@959', 1, '', '0', '2022-11-25  08:30:56', 1, 0, 'SLS2021001'),
(22, 2, 0, 'QC MANAGER', 'Raghu Babu', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', 'tqm', 'Mourya@2744', 1, '', '0', '2022-11-24  16:53:51', 1, 0, 'SLS2021001'),
(23, 2, 0, 'OnLine Qc', 'Pavan', '3', 'esWFp_lKHH8:APA91bEYz2QEDbbEjAdLKjJy3NeKYW3wMvDQwaq0qDJOyVNS0QoGUfTFkoT6SeWyp2HB1XhByOBhasn0NQ2TdaCwEMKDFnJjgagnNGko2weq7plMn20FUeV2vanpF_BqSAKSCkbEVfPL', '8179475763', '$Jmermc763', 1, '8179475763', '0', '2022-11-16 16:18:26', 1, 0, 'SLS2021001'),
(24, 2, 0, 'OnLine Qc', 'I Satya Murthy', '3', 'cEUSo7zsGfg:APA91bFQ1UJojqdwcNbInpgS9gDgGRWpv1pKsl4n6mHYst8w68ZMSy1HiHLAARzZGLszY1L4gXcw7LP_2gyHxZvq0FPLuHmqPq7ckpaTPV55DXBA0xN8olCxODPwRMMUoV9biHuyecrZ', '7995675597', '$Qc5597', 1, '7995675597', '0', '2022-11-17 15:15:06', 1, 0, 'SLS2021001'),
(25, 2, 0, 'QC Documentation', 'Babu ', '3', 'dBEu1hPYPQY:APA91bGbGeBNBxloxcKLocGitLiBPaD084X_rJpxQPdkVQVt_DQ0Zn4T0CV-B4diH_mZR2nWoFk-MoFbTUPd3jnJBzwRsOq-lrWxZVRO2ijp5FpHGAdsdVm8U40OmvoG9yDcW3_yyxau', '9014224410', '$Qcd9014', 1, '9014224410', '0', '2022-11-17  14:59:59', 1, 0, 'SLS2021001'),
(26, 2, 0, 'OnLine Qc', 'Ajay', '3', 'c9C2OVU9FNs:APA91bFQc73MQQG0a1wHdaDmkYRaWB7-k74E5qwY0PwOxdt0FIWjWqqAD1XZKfEPlPD-01MxQqBuDkn0F4GwvMXxbhyNzW0Os5FsceRYLTSagLEiSp_TcrzWh1jKz6E-_MCxOXHF2pdG', '6302342959', '$Jq2342', 1, '6302342959', '0', '2022-02-18  10:05:55', 1, 0, 'SLS2021001'),
(27, 2, 0, 'QC Documentation', 'Rajesh', '3', 'dBEu1hPYPQY:APA91bGbGeBNBxloxcKLocGitLiBPaD084X_rJpxQPdkVQVt_DQ0Zn4T0CV-B4diH_mZR2nWoFk-MoFbTUPd3jnJBzwRsOq-lrWxZVRO2ijp5FpHGAdsdVm8U40OmvoG9yDcW3_yyxau', '9908760940', '$Qcd60940', 1, '9908760940', '0', '2022-11-16  09:27:08', 1, 0, 'SLS2021001'),
(28, 2, 0, 'OnLine Qc', 'R Akhil', '3', 'c9C2OVU9FNs:APA91bFQc73MQQG0a1wHdaDmkYRaWB7-k74E5qwY0PwOxdt0FIWjWqqAD1XZKfEPlPD-01MxQqBuDkn0F4GwvMXxbhyNzW0Os5FsceRYLTSagLEiSp_TcrzWh1jKz6E-_MCxOXHF2pdG', '9703745354', '$JL37453', 1, '9703745354', '0', '2022-11-17  11:56:00', 1, 0, 'SLS2021001'),
(29, 2, 0, 'QC Documentation', 'V.Hema', '2', 'dBEu1hPYPQY:APA91bGbGeBNBxloxcKLocGitLiBPaD084X_rJpxQPdkVQVt_DQ0Zn4T0CV-B4diH_mZR2nWoFk-MoFbTUPd3jnJBzwRsOq-lrWxZVRO2ijp5FpHGAdsdVm8U40OmvoG9yDcW3_yyxau', '7075933683', '$Qcd93368', 1, '7075933683', '0', '2022-11-16  09:39:03', 1, 0, 'SLS2021001'),
(30, 2, 0, 'QC Documentation', 'M.JOY NEWTON', '3', 'dBEu1hPYPQY:APA91bGbGeBNBxloxcKLocGitLiBPaD084X_rJpxQPdkVQVt_DQ0Zn4T0CV-B4diH_mZR2nWoFk-MoFbTUPd3jnJBzwRsOq-lrWxZVRO2ijp5FpHGAdsdVm8U40OmvoG9yDcW3_yyxau', '9515831811', 'Abanalyst', 1, '7075933683', '0', '2022-11-25  10:37:38', 1, 0, 'SLS2021001'),
(31, 9, 0, 'Sales', 'SalesAdmin', '2', 'dK3qSgLoxug:APA91bEeBYmKNeCAJaJoH5ECm50LllMvvzJs5GEsIL7uOx13Spagm_05T9VgrlMWf78ghygd2XZ23776KQDlBkJb2Q1Lh_SybkeOb5GY3mn0_ESgWIV-_oH_BRKanY24S1zFgjRAXCPc', 'JmSales', 'JmSales', 1, '', '0', '2022-11-03  23:17:45', 1, 0, 'SLS2021001'),
(32, 8, 0, 'Accounts Manager', 'AccountsAdmin', '2', '', 'accounts', 'gm@jmexports.com', 1, '', '0', '2022-11-03  23:17:45', 1, 0, 'SLS2021001'),
(33, 4, 0, 'Preprocessing Incharge', 'Viswanath', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9490687575', '$V68757', 1, '', '0', '2022-11-01  15:59:48', 1, 0, 'SLS2021001'),
(34, 5, 0, 'Production Manager', 'Varma', '2', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '9949544195', '$Jvarma195', 1, '', '0', '2022-11-01  15:59:48', 1, 0, 'SLS2021001'),
(35, 9, 0, 'Sales Manager', 'Sales', '2', '', 'jlsf_sales', 'jlsf_sales', 1, '', '0', '2022-11-24  15:38:29', 1, 0, 'SLS2021004'),
(37, 7, 0, 'Cold Store Manager', 'Cold Strore', '2', '', 'jlsf_coldstore', 'jlsf_coldstore', 1, '', '0', '2022-11-01  15:55:54', 1, 0, 'SLS2021004'),
(38, 8, 0, 'Accounts Manager', 'Jagan', '2', '', '9848430432', 'Jme@112233@', 1, '', '0', '2022-11-25  11:22:20', 1, 0, 'SLS2021001'),
(39, 8, 0, 'Accounts User', 'Prasad', '3', '', '9347696640', '$P69664', 1, '', '0', '2022-11-25  11:09:39', 1, 0, 'SLS2021001'),
(40, 8, 0, 'Accounts User', 'Ravi Teja', '3', '', '9010107784', 'raviteja@123', 1, '', '0', '2022-11-25  11:18:56', 1, 0, 'SLS2021001'),
(41, 1, 0, 'Billing Admin', 'Silverlights', '1', 'cY1F2F7FGLA:APA91bFFDazAbh-sWVD63bgrNe3qrwBcIeAqxdacG6Muxi9g4sHarjuN1HVzVYSdctH2GMqdvNPNBv3iL3hdV_TYflYMBXAaLWTlm5hSP39LsPQBA2Vutlvn5EWxwbR8GAm5ZP3NaqnM', '8297154123', '8297154123', 1, '', '0', '2022-11-24  16:57:43', 1, 0, 'SilverLights');

-- --------------------------------------------------------

--
-- Table structure for table `AdminRegisteredClients`
--

CREATE TABLE `AdminRegisteredClients` (
  `ClientID` int(9) NOT NULL,
  `ClientAccountID` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ClientName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ClientAddress` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ClientShortName` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Path` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `StartYear` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminRegisteredClients`
--

INSERT INTO `AdminRegisteredClients` (`ClientID`, `ClientAccountID`, `ClientName`, `ClientAddress`, `ClientShortName`, `Path`, `StartYear`) VALUES
(1, 'SLS2021001', 'Jagadesh Marine Exports', 'Bhimavaram', 'JME', 'Clients/JME/', '2019'),
(2, 'SLS2021002', 'Mourya Exim Pvt Ltd', 'Bhimavaram', 'Mourya', 'Clients/Mourya/', '2019'),
(3, 'SLS2021003', 'SuryaMitra Exim Pvt Ltd', 'Bhimavaram', 'Sme', 'Clients/Sme/', '2021'),
(4, 'SLS2021004', 'Jaya Lakshmi Sea Foods Pvt Ltd', 'Bhimavaram', 'Jlsf', 'Clients/Jlsf/', '2022'),
(5, 'SLS2021005', 'Sai Marine Exports', 'Vizag', 'SaiMarine', 'Clients/SaiMarine/', '2021'),
(6, 'SLS2021006', 'Siri Aqua', 'Narasapuram', 'Siri', 'Clients/Siri/', '2022'),
(8, 'SLS2021007', 'Satish Marine', 'Bapatla', '0', 'Clients/SatishMarine/', '2022'),
(9, 'serp', 'Serp Test', 'Palakollu', '0', 'Clients/Serp/', '2019'),
(10, 'SilverLights', 'Silverlights', 'Palakollu', '0', 'Clients/SlsBiling/', '2019');

-- --------------------------------------------------------

--
-- Table structure for table `AdminSideBarHeaders`
--

CREATE TABLE `AdminSideBarHeaders` (
  `Sid` int(11) NOT NULL,
  `HeaderID` int(5) NOT NULL,
  `Header` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` int(11) NOT NULL,
  `DeptID` int(3) NOT NULL,
  `AccessType` int(3) NOT NULL,
  `SortOrder` int(11) NOT NULL,
  `SideMenuIcon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminSideBarHeaders`
--

INSERT INTO `AdminSideBarHeaders` (`Sid`, `HeaderID`, `Header`, `Status`, `DeptID`, `AccessType`, `SortOrder`, `SideMenuIcon`) VALUES
(1, 1, 'Dashboard', 1, 2, 3, 1, 'mdi  mdi-view-dashboard'),
(2, 0, 'General', 1, 2, 3, 1, 'mdi mdi-wallet-plus'),
(3, 0, 'Masters', 1, 2, 3, 1, 'mdi mdi-view-split-vertical'),
(4, 0, 'Reports', 1, 2, 3, 1, 'mdi mdi-cloud-print'),
(5, 1, 'Dashboard', 1, 9, 3, 1, 'mdi  mdi-view-dashboard'),
(6, 0, 'General', 1, 9, 3, 1, 'mdi mdi-wallet-plus'),
(7, 0, 'Masters', 1, 9, 3, 1, 'mdi mdi-view-split-vertical'),
(8, 0, 'Reports', 1, 9, 3, 1, 'mdi mdi-cloud-print'),
(9, 1, 'Dashboard', 1, 1, 3, 1, 'mdi  mdi-view-dashboard'),
(10, 0, 'General', 1, 1, 3, 1, 'mdi mdi-wallet-plus'),
(11, 11, 'Masters', 1, 1, 3, 1, 'mdi mdi-view-split-vertical'),
(12, 0, 'Reports', 1, 1, 3, 1, 'mdi mdi-cloud-print');

-- --------------------------------------------------------

--
-- Table structure for table `AdminSideBarOptions`
--

CREATE TABLE `AdminSideBarOptions` (
  `ID` int(9) NOT NULL,
  `HeaderId` int(1) NOT NULL DEFAULT 0,
  `Name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ServerUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `PathUrl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `DeptID` int(3) NOT NULL,
  `SubDeptId` int(3) NOT NULL DEFAULT 0,
  `AccessType` int(1) NOT NULL,
  `SortOrder` int(3) NOT NULL,
  `Status` int(3) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminSideBarOptions`
--

INSERT INTO `AdminSideBarOptions` (`ID`, `HeaderId`, `Name`, `ServerUrl`, `PathUrl`, `DeptID`, `SubDeptId`, `AccessType`, `SortOrder`, `Status`) VALUES
(1, 16, 'Create Department', 'https://serp.silverlights.in/', 'DepartmentCreate.php', 11, 0, 3, 2, 1),
(2, 16, 'Create SubDepartment', 'https://serp.silverlights.in/', 'SubDepartmentCreate.php', 11, 0, 3, 2, 1),
(3, 16, 'Create Designation', 'https://serp.silverlights.in/', 'DesignationCreate.php', 11, 0, 3, 2, 1),
(4, 15, 'Create Employee', 'https://serp.silverlights.in/', 'EmployeeCreate.php', 11, 0, 3, 1, 1),
(5, 16, 'Create Holidays', 'https://serp.silverlights.in/', 'CreateHolidays.php', 11, 0, 3, 2, 1),
(6, 16, 'Create Schedule', 'https://serp.silverlights.in/', 'CreateShedule.php', 11, 0, 3, 2, 1),
(7, 16, 'Add Biometric Device', 'https://serp.silverlights.in/', 'NewBioMetricMachine.php', 11, 0, 3, 2, 1),
(8, 3, 'Device Data', 'https://serp.silverlights.in/', 'BioDeviceList.php', 11, 0, 3, 3, 1),
(9, 15, 'Employee List', 'https://serp.silverlights.in/', 'EmployeeList.php', 11, 0, 3, 2, 1),
(10, 15, 'Employee On Duty', 'https://serp.silverlights.in/', 'EmployeeOnDuty.php', 11, 0, 3, 6, 1),
(11, 22, 'Employee Leaves', 'https://serp.silverlights.in/', 'EmployeeLeaves.php', 11, 0, 3, 3, 1),
(12, 21, 'Daily Attendance', 'https://serp.silverlights.in/', 'BioMetricPunchingReport.php', 11, 0, 3, 3, 1),
(13, 21, 'Manual Attendance', 'https://serp.silverlights.in/', 'ManualAttendance.php', 11, 0, 3, 3, 1),
(14, 21, 'Monthly Attendance', 'https://serp.silverlights.in/', 'MonthlyAttendness.php', 11, 0, 3, 3, 1),
(15, 16, 'View Departments', 'https://serp.silverlights.in/', 'HrmDepartments.php', 11, 0, 3, 3, 1),
(16, 16, 'View Holidays', 'https://serp.silverlights.in/', 'Holidays.php', 11, 0, 3, 3, 1),
(17, 16, 'View Schedule', 'https://serp.silverlights.in/', 'Schedule.php', 11, 0, 3, 3, 1),
(18, 3, 'View Cash Advance', 'https://serp.silverlights.in/', 'CashAdvance.php', 11, 0, 3, 3, 1),
(19, 3, 'View Deductions', 'https://serp.silverlights.in/', 'deductions.php', 11, 0, 3, 3, 1),
(20, 3, 'Monthly Salaries', 'https://serp.silverlights.in/', 'HrmFinalAttendnce.php', 11, 0, 3, 3, 1),
(21, 21, 'Time Card', 'https://serp.silverlights.in/', 'TimeCard.php', 11, 0, 3, 4, 1),
(22, 4, 'Salaries', 'https://serp.silverlights.in/', 'salaries.php', 11, 0, 3, 4, 1),
(23, 4, 'Position', 'https://serp.silverlights.in/', 'position.php', 11, 0, 3, 4, 1),
(24, 4, 'Payroll', 'https://serp.silverlights.in/', 'payroll.php', 11, 0, 3, 4, 1),
(25, 4, 'Devices Data', 'https://serp.silverlights.in/', 'schedule_employee.php', 11, 0, 3, 4, 1),
(26, 1, 'DashBoard', 'https://serp.silverlights.in/', 'QualityControl/DocPoList.php', 2, 0, 3, 1, 1),
(27, 15, 'Employee Salary Details', 'https://serp.silverlights.in/', 'EmployeeSalaryDetails.php', 11, 0, 3, 5, 1),
(28, 15, 'Employee Bank Details', 'https://serp.silverlights.in/', 'EmployeeBankDetails.php', 11, 0, 3, 3, 1),
(29, 15, 'Employee Personal Details', 'https://serp.silverlights.in/', 'EmployeePersonalDetails.php', 11, 0, 3, 4, 1),
(32, 21, 'BioMetric Data', 'https://serp.silverlights.in/', 'BioMetricDeviceList.php', 11, 0, 3, 3, 1),
(34, 2, 'Create Traceability', 'https://serp.silverlights.in/', 'QualityControl/CreateTraceability.php', 2, 0, 3, 1, 1),
(35, 2, 'Create Purchase Orders', 'https://serp.silverlights.in/', 'QualityControl/DocPoCreate.php', 2, 0, 3, 1, 1),
(36, 2, 'Change Password', 'https://serp.silverlights.in/', 'Admin/ChangePassword.php', 2, 0, 3, 1, 1),
(37, 1, 'DashBoard', 'https://serp.silverlights.in/', 'Admin/ChangePassword.php', 9, 0, 3, 1, 1),
(38, 6, 'Change Password', 'https://serp.silverlights.in/', 'Admin/ChangePassword.php', 9, 0, 3, 1, 1),
(39, 7, 'View PO List', 'https://serp.silverlights.in/', 'DocPoList.php', 9, 0, 3, 1, 1),
(40, 3, 'Purchase Orders', 'https://serp.silverlights.in/', 'QualityControl/DocPoList.php', 2, 0, 3, 1, 1),
(41, 2, 'Create Consignee', 'https://serp.silverlights.in/', 'QualityControl/CreateConsignee.php', 2, 0, 3, 1, 1),
(42, 3, 'Traceability List', 'https://serp.silverlights.in/', 'QualityControl/TraceabilityList.php', 2, 0, 3, 1, 1),
(43, 3, 'Consignee List', 'https://serp.silverlights.in/', 'QualityControl/ConsigneeList.php', 2, 0, 3, 1, 1),
(44, 2, 'Create Packing Styles', 'https://serp.silverlights.in/', 'QualityControl/CreatePackingStyles.php', 2, 0, 3, 1, 1),
(45, 3, 'Packing Styles List', 'https://serp.silverlights.in/', 'QualityControl/PackingStyleList.php', 2, 0, 3, 1, 1),
(46, 2, 'Create Variety', 'https://serp.silverlights.in/', 'QualityControl/CreateVariety.php', 2, 0, 3, 1, 1),
(47, 2, 'Create Analysis Parameter', 'https://serp.silverlights.in/', 'QualityControl/CreateAnalysisParameter.php', 2, 0, 3, 1, 1),
(48, 1, 'DashBoard', 'https://serp.silverlights.in/', 'Admin/index.php', 1, 0, 3, 1, 1),
(49, 11, 'Clients List', 'https://serp.silverlights.in/', 'Admin/AdminClientsList.php', 1, 0, 1, 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AdminClientBasicDetails`
--
ALTER TABLE `AdminClientBasicDetails`
  ADD PRIMARY KEY (`BDetailsID`);

--
-- Indexes for table `AdminClientDbDetails`
--
ALTER TABLE `AdminClientDbDetails`
  ADD PRIMARY KEY (`DbID`),
  ADD UNIQUE KEY `ClientID` (`ClientID`);

--
-- Indexes for table `AdminClientDetails`
--
ALTER TABLE `AdminClientDetails`
  ADD PRIMARY KEY (`ClientID`);

--
-- Indexes for table `AdminClientLicenseDetails`
--
ALTER TABLE `AdminClientLicenseDetails`
  ADD PRIMARY KEY (`licenseID`);

--
-- Indexes for table `AdminConfigDetails`
--
ALTER TABLE `AdminConfigDetails`
  ADD PRIMARY KEY (`ConfigID`);

--
-- Indexes for table `AdminDepartments`
--
ALTER TABLE `AdminDepartments`
  ADD PRIMARY KEY (`DeptID`);

--
-- Indexes for table `AdminLoginDetails`
--
ALTER TABLE `AdminLoginDetails`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `UserLoginID` (`UserLoginID`);

--
-- Indexes for table `AdminRegisteredClients`
--
ALTER TABLE `AdminRegisteredClients`
  ADD PRIMARY KEY (`ClientID`),
  ADD UNIQUE KEY `ClientAccountID` (`ClientAccountID`);

--
-- Indexes for table `AdminSideBarHeaders`
--
ALTER TABLE `AdminSideBarHeaders`
  ADD PRIMARY KEY (`Sid`);

--
-- Indexes for table `AdminSideBarOptions`
--
ALTER TABLE `AdminSideBarOptions`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AdminClientBasicDetails`
--
ALTER TABLE `AdminClientBasicDetails`
  MODIFY `BDetailsID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `AdminClientDbDetails`
--
ALTER TABLE `AdminClientDbDetails`
  MODIFY `DbID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `AdminClientDetails`
--
ALTER TABLE `AdminClientDetails`
  MODIFY `ClientID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `AdminClientLicenseDetails`
--
ALTER TABLE `AdminClientLicenseDetails`
  MODIFY `licenseID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `AdminConfigDetails`
--
ALTER TABLE `AdminConfigDetails`
  MODIFY `ConfigID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `AdminDepartments`
--
ALTER TABLE `AdminDepartments`
  MODIFY `DeptID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `AdminLoginDetails`
--
ALTER TABLE `AdminLoginDetails`
  MODIFY `UserID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `AdminRegisteredClients`
--
ALTER TABLE `AdminRegisteredClients`
  MODIFY `ClientID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `AdminSideBarHeaders`
--
ALTER TABLE `AdminSideBarHeaders`
  MODIFY `Sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `AdminSideBarOptions`
--
ALTER TABLE `AdminSideBarOptions`
  MODIFY `ID` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
