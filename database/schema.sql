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

-- Table to track rental transactions
CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,            -- The item being rented
    renter_id INT NOT NULL,          -- The user who is borrowing the item
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10, 2),
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (renter_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample data for a rental
-- User 2 (Jane) borrows Item 1 (Drill) from User 1 (John)
INSERT INTO rentals (item_id, renter_id, start_date, end_date, total_price, status) 
VALUES (1, 2, '2024-08-01', '2024-08-03', 30.00, 'active');