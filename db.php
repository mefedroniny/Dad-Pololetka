<?php
$host = "localhost";
$user = "root"; // výchozí u XAMPP
$password = ""; // výchozí heslo je prázdné
$dbname = "ticket_system"; // název databáze

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Chyba připojení k databázi: " . $conn->connect_error);
}
?>
