-- ShareNest Database Schema
CREATE DATABASE IF NOT EXISTS sharenest_db;
USE sharenest_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for items being rented out
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,           -- The user who owns the item
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),           -- e.g., Power Tools, Camping, Kitchen
    price_per_day DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),          -- Link to the product image
    status ENUM('available', 'rented') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample Data for Testing
INSERT INTO users (first_name, last_name, email, password) VALUES 
('Test', 'User', 'test@sharenest.com', 'password123');

INSERT INTO items (owner_id, item_name, description, category, price_per_day) VALUES 
(1, 'Heavy Duty Drill', 'Professional Grade DeWalt drill with extra batteries.', 'Tools', 15.00),
(1, 'Camping Tent', 'Large 4-person tent, waterproof and easy to set up.', 'Outdoor', 25.00);