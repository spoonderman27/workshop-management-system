<?php
include("include/db_connect.php");

// Add reset_token_hash column
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token_hash'");
if($check->num_rows == 0) {
    if($conn->query("ALTER TABLE users ADD COLUMN reset_token_hash VARCHAR(64) NULL DEFAULT NULL")) {
        echo "Column 'reset_token_hash' added successfully.<br>";
    } else {
        echo "Error adding 'reset_token_hash': " . $conn->error . "<br>";
    }
} else {
    echo "Column 'reset_token_hash' already exists.<br>";
}

// Add reset_token_expires_at column
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token_expires_at'");
if($check->num_rows == 0) {
    if($conn->query("ALTER TABLE users ADD COLUMN reset_token_expires_at DATETIME NULL DEFAULT NULL")) {
        echo "Column 'reset_token_expires_at' added successfully.<br>";
    } else {
        echo "Error adding 'reset_token_expires_at': " . $conn->error . "<br>";
    }
} else {
    echo "Column 'reset_token_expires_at' already exists.<br>";
}

echo "Database update check complete.";
?>
