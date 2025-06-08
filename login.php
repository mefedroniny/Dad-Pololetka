<?php
session_start();

$correctUsername = "admin";
$correctPassword = "1234";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === $correctUsername && $password === $correctPassword) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    header("Location: index.php");
    exit;
} else {
    echo "<h2>Chyba: Neplatné přihlašovací údaje</h2>";
    echo "<a href='index.php'>Zpět</a>";
}
?>
