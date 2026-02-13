<?php
// Start the session on every page
session_start();

// --- DATABASE SETUP ---
try {
    $db_path = __DIR__ . '/database.sqlite';
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- AUTOMATICALLY CREATE AND UPDATE ALL NECESSARY TABLES ---

// 1. Users table
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
)");

// --- THE AUTOMATIC FIX IS HERE ---
// This code checks if the 'users' table is missing the new columns and adds them if necessary.
// This is a robust way to handle database updates.
try {
    $db->query("SELECT full_name, email FROM users LIMIT 1");
} catch (PDOException $e) {
    // If the columns don't exist, the query will fail. We catch the error and add them.
    $db->exec("ALTER TABLE users ADD COLUMN full_name TEXT");
    $db->exec("ALTER TABLE users ADD COLUMN email TEXT");
}


// 2. Items table
$db->exec("CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    type TEXT NOT NULL,
    event_date DATE,
    event_image TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 3. Registrations table
$db->exec("CREATE TABLE IF NOT EXISTS registrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    event_id INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES items(id) ON DELETE CASCADE
)");

// 4. Feedback table
$db->exec("CREATE TABLE IF NOT EXISTS feedback (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    event_id INTEGER NOT NULL,
    rating INTEGER NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES items(id) ON DELETE CASCADE
)");


// --- ADMIN CREDENTIALS ---
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'password123');

?>
