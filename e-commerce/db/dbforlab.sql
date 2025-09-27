-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 13, 2022 at 02:42 PM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 8.0.7

-- Database: `laundry_platform`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
 
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE laundry_platform;
USE laundry_platform;

-- --------------------------------------------------------
-- Categories (types of laundry services)
-- --------------------------------------------------------
CREATE TABLE categories (
  cat_id INT AUTO_INCREMENT PRIMARY KEY,
  cat_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Suppliers (laundries & individuals)
-- --------------------------------------------------------
CREATE TABLE suppliers (
  supplier_id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_name VARCHAR(150) NOT NULL,
  supplier_email VARCHAR(100) UNIQUE NOT NULL,
  supplier_contact VARCHAR(20) NOT NULL,
  supplier_address VARCHAR(255) NOT NULL,
  supplier_type ENUM('laundry','individual') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Services (instead of products)
-- --------------------------------------------------------
CREATE TABLE services (
  service_id INT AUTO_INCREMENT PRIMARY KEY,
  service_cat INT NOT NULL,
  supplier_id INT NOT NULL,
  service_name VARCHAR(200) NOT NULL,
  service_price DOUBLE NOT NULL,
  service_desc VARCHAR(500) DEFAULT NULL,
  service_image VARCHAR(150) DEFAULT NULL,
  service_keywords VARCHAR(100) DEFAULT NULL,
  FOREIGN KEY (service_cat) REFERENCES categories(cat_id) ON DELETE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Customer (unchanged from original)
-- --------------------------------------------------------
CREATE TABLE customer (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(50) NOT NULL UNIQUE,
  customer_pass VARCHAR(150) NOT NULL,
  customer_country VARCHAR(30) NOT NULL,
  customer_city VARCHAR(30) NOT NULL,
  customer_contact VARCHAR(15) NOT NULL,
  customer_image VARCHAR(100) DEFAULT NULL,
  user_role INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Cart (optional but useful for multi-service orders)
-- --------------------------------------------------------
CREATE TABLE cart (
  service_id INT NOT NULL,
  customer_id INT NOT NULL,
  qty INT NOT NULL,
  PRIMARY KEY (service_id, customer_id),
  FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
  FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Orders
-- --------------------------------------------------------
CREATE TABLE orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  order_status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Order Details
-- --------------------------------------------------------
CREATE TABLE orderdetails (
  order_id INT NOT NULL,
  service_id INT NOT NULL,
  qty INT NOT NULL,
  PRIMARY KEY (order_id, service_id),
  FOREIGN KEY (order_id) REFERENCES orders(order_id),
  FOREIGN KEY (service_id) REFERENCES services(service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Payment (unchanged from original)
-- --------------------------------------------------------
CREATE TABLE payment (
  pay_id INT AUTO_INCREMENT PRIMARY KEY,
  amt DOUBLE NOT NULL,
  customer_id INT NOT NULL,
  order_id INT NOT NULL,
  currency TEXT NOT NULL,
  payment_date DATE NOT NULL,
  FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
  FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

 
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
