<?php
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "ticket_system";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Chyba připojení k databázi: " . $conn->connect_error);
}
?>
