<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = intval($_POST['ticket_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $ticketId);

    if ($stmt->execute()) {
        header("Location: admin.php?updated=1"); 
        exit;
    } else {
        echo "Chyba při aktualizaci tiketu.";
    }
} else {
    echo "Neplatný požadavek.";
}
?>
