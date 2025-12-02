-- Farm Production Management System Database

CREATE DATABASE IF NOT EXISTS farm_management;
USE farm_management;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Livestock table
CREATE TABLE livestock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    animal_type VARCHAR(50) NOT NULL, -- cow, chicken, goat, pig, etc.
    tag_number VARCHAR(50) UNIQUE NOT NULL,
    breed VARCHAR(100),
    gender ENUM('Male', 'Female') NOT NULL,
    age_months INT,
    weight DECIMAL(8, 2),
    purchase_date DATE,
    purchase_price DECIMAL(10, 2),
    health_status ENUM('healthy', 'warning', 'critical') DEFAULT 'healthy',
    location VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, animal_type)
);

-- Production records table
CREATE TABLE production (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    livestock_id INT,
    production_type VARCHAR(50) NOT NULL, -- milk, eggs, meat, wool, etc.
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(20) NOT NULL, -- liters, kg, dozens, etc.
    production_date DATE NOT NULL,
    quality_grade VARCHAR(10), -- A, B, C
    price_per_unit DECIMAL(10, 2),
    total_value DECIMAL(12, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL,
    INDEX (user_id, production_date)
);

-- Health records table
CREATE TABLE health_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    livestock_id INT NOT NULL,
    health_issue VARCHAR(200),
    treatment VARCHAR(500),
    medication VARCHAR(100),
    treatment_cost DECIMAL(10, 2),
    treatment_date DATE,
    recovery_date DATE,
    veterinarian VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE,
    INDEX (user_id, treatment_date)
);

-- Finance records table
CREATE TABLE finance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    transaction_type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(12, 2) NOT NULL,
    description VARCHAR(500),
    transaction_date DATE NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, transaction_date)
);

-- Feeding schedule table
CREATE TABLE feeding_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    livestock_id INT,
    animal_type VARCHAR(50),
    feed_type VARCHAR(100),
    quantity DECIMAL(10, 2),
    unit VARCHAR(20),
    feeding_frequency VARCHAR(50), -- daily, twice daily, etc.
    start_date DATE,
    end_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, phone, role) 
VALUES ('admin', 'admin@farm.com', '$2y$10$h8JG7hR9K8X5mN3Pq9L2X.8q7R9K5L3M0O2P4Q6S8T0V2X4Z6B2', 'System Administrator', '1234567890', 'admin');

-- Insert sample farm user (password: user123)
INSERT INTO users (username, email, password, full_name, phone, role) 
VALUES ('farmer1', 'farmer@farm.com', '$2y$10$h8JG7hR9K8X5mN3Pq9L2X.8q7R9K5L3M0O2P4Q6S8T0V2X4Z6B2', 'John Farmer', '0987654321', 'user');
