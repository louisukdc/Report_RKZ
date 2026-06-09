<?php
$host = "localhost";
$db_name = "rkz_db";
$username = "root";
$password = "123456789";

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    // Suppress echo to prevent breaking layout or headers. Store in global or log.
    $db_connection_error = $exception->getMessage();
}
?>
