-- Refined Database Schema for Coffee Shop (PostgreSQL)

-- Users Table
DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    id_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    extension_name VARCHAR(10),
    age INTEGER,
    sex VARCHAR(10),
    contact VARCHAR(20),
    dob DATE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    purok VARCHAR(100),
    barangay VARCHAR(100),
    city_municipality VARCHAR(100),
    province VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Philippines',
    zip_code VARCHAR(10),
    question1 TEXT,
    answer1 TEXT,
    question2 TEXT,
    answer2 TEXT,
    question3 TEXT,
    answer3 TEXT,
    role VARCHAR(20) DEFAULT 'user',
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity Logs Table
DROP TABLE IF EXISTS activity_logs CASCADE;
CREATE TABLE activity_logs (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(20),
    user_name VARCHAR(100),
    user_role VARCHAR(20),
    action TEXT NOT NULL,
    module VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table
DROP TABLE IF EXISTS menu_items CASCADE;
CREATE TABLE menu_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price NUMERIC(10, 2) NOT NULL,
    category VARCHAR(50),
    stock_quantity INTEGER DEFAULT 0,
    image_url TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Menu Items Data
INSERT INTO menu_items (name, description, price, category, stock_quantity) VALUES
('Espresso', 'Rich & bold shot', 80.00, 'Coffee', 100),
('Cappuccino', 'Espresso + steamed milk', 120.00, 'Coffee', 100),
('Caramel Latte', 'Sweet caramel twist', 150.00, 'Coffee', 100),
('Cold Brew', 'Slow-steeped perfection', 160.00, 'Cold Drinks', 100),
('Matcha Latte', 'Premium Japanese matcha', 140.00, 'Tea', 100),
('Croissant', 'Flaky, buttery delight', 95.00, 'Pastry', 50),
('Blueberry Muffin', 'Fresh-baked daily', 85.00, 'Pastry', 50),
('Iced Americano', 'Espresso over ice', 110.00, 'Cold Drinks', 100),
('Mocha', 'Chocolate espresso blend', 145.00, 'Coffee', 100),
('Cheesecake', 'Classic New York style', 165.00, 'Dessert', 20);
