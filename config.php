<?php
session_start();

$host = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "smart_study_planner";
$port = 3307;

$conn = new mysqli($host, $db_username, $db_password, $db_name, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
