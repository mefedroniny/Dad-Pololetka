<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $category = $conn->real_escape_string($_POST['category']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $problem = $conn->real_escape_string($_POST['problem']);

    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = basename($_FILES['attachment']['name']);
        $targetPath = $uploadDir . time() . "_" . $filename;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
            $attachment = $targetPath;
        }
    }

    $stmt = $conn->prepare("INSERT INTO tickets (fullname, email, category, priority, problem, attachment, status) VALUES (?, ?, ?, ?, ?, ?, 'open')");
    $stmt->bind_param("ssssss", $fullname, $email, $category, $priority, $problem, $attachment);
    $stmt->execute();

    header("Location: index.php?success=1");
    exit();
}
?>
