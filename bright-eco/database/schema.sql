CREATE DATABASE IF NOT EXISTS bright_eco CHARACTER SET utf8mb4;
USE bright_eco;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,username VARCHAR(50) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,role ENUM('admin','owner','staff') NOT NULL,status TINYINT NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE categories(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL UNIQUE);
CREATE TABLE products(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150) NOT NULL,category_id INT NOT NULL,specs VARCHAR(255) DEFAULT '',price DECIMAL(12,2) NOT NULL,quantity INT NOT NULL DEFAULT 0,reorder_level INT NOT NULL DEFAULT 5,status TINYINT NOT NULL DEFAULT 1,FOREIGN KEY(category_id) REFERENCES categories(id));
CREATE TABLE bills(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,customer_name VARCHAR(120) NOT NULL,subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,discount DECIMAL(12,2) NOT NULL DEFAULT 0,total DECIMAL(12,2) NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id));
CREATE TABLE bill_items(id INT AUTO_INCREMENT PRIMARY KEY,bill_id INT NOT NULL,product_id INT NOT NULL,quantity INT NOT NULL,unit_price DECIMAL(12,2) NOT NULL,FOREIGN KEY(bill_id) REFERENCES bills(id),FOREIGN KEY(product_id) REFERENCES products(id));
CREATE TABLE activity_log(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,action VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO categories(name) VALUES('Solar Panels'),('Inverters'),('Batteries'),('Mounting'),('Cables'),('Charge Controllers');
-- Default admin: username "admin", password "admin123" (change it after first login)
INSERT INTO users(name,username,password_hash,role) VALUES('System Admin','admin','$2y$10$WzG5MmKv5kdrp1sOgJRrKeWCNHBqlZ.qeakaYc4YGaJZqFPLJT3Ua','admin');
