<?php
$host = 'localhost';
$dbname = 'vanlife_db'; 
$username = 'root'; 
$password = ''; 

try {
    $databaseConnection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

