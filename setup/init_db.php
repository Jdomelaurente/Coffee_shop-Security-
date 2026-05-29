<?php
require_once dirname(__FILE__) . '/../includes/db.php';

try {
    $conn->beginTransaction();

    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            id_number VARCHAR(20) UNIQUE NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
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
            role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'superadmin')),
            status VARCHAR(20) DEFAULT 'pending',
            last_login TIMESTAMP NULL,
            last_logout TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS activity_logs (
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
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS pending_actions (
            id SERIAL PRIMARY KEY,
            target_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            action_type VARCHAR(20) NOT NULL CHECK (action_type IN ('update_role', 'delete_user')),
            new_data JSONB,
            requested_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
            status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS menu_items (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price NUMERIC(10, 2) NOT NULL,
            category VARCHAR(50),
            stock_quantity INTEGER DEFAULT 0,
            image_url TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->commit();
    echo "Database initialized successfully.";
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Database initialization failed: " . $e->getMessage();
    exit(1);
}
