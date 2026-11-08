<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "workshop_database";

//create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

//check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>