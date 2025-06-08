<?php
require 'db.php';
session_start();

// Zobraz chyby
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$zprava = '';
$zprava_typ = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($email) || empty($password)) {
        $zprava = "Vyplňte všechna pole.";
        $zprava_typ = "error";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $zprava = "Tento uživatel již existuje.";
            $zprava_typ = "error";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $hash, $email);
            if ($stmt->execute()) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $conn->insert_id;
                header("Location: index.php");
                exit();
            } else {
                $zprava = "Chyba při registraci.";
                $zprava_typ = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrace účtu</title>
  <link rel="stylesheet" href="css/registrace.css">
  <style>
    input[type="email"] {
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #374151;
      background-color: #1f2937;
      color: #f9fafb;
      font-size: 1rem;
      transition: border-color 0.2s;
    }

    input[type="email"]:focus {
      border-color: #3b82f6;
      outline: none;
      background-color: #111827;
    }
  </style>
</head>
<body>
  <form method="POST">
    <?php if ($zprava): ?>
      <div class="zprava <?php echo $zprava_typ; ?>">
        <?php echo htmlspecialchars($zprava); ?>
      </div>
    <?php endif; ?>

    <label for="username">Uživatelské jméno:</label>
    <input type="text" name="username" required>

    <label for="email">E-mail:</label>
    <input type="email" name="email" required>

    <label for="password">Heslo:</label>
    <input type="password" name="password" required>

    <button type="submit">Registrovat</button>

    <div class="form-links">
      <p>Už máš účet? <a href="index.php">Přihlášení</a></p>
      <p><a href="index.php">← Zpět na hlavní stránku</a></p>
    </div>
  </form>
</body>
</html>