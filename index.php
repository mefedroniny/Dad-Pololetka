<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pokročilý Ticket systém - TICKIFY</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <div class="login-box">
      <?php if (!isset($_SESSION['loggedin'])): ?>
        <form action="login.php" method="POST">
          <input type="text" name="username" placeholder="Uživatelské jméno" required>
          <input type="password" name="password" placeholder="Heslo" required>
          <button type="submit">Přihlásit se</button>
        </form>
        <!-- Odkaz na registraci -->
        <p>Nemáte účet? <a href="registrace.php">Zaregistrujte se zde</a>.</p>
      <?php else: ?>
        <p>Přihlášen jako <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        <form action="logout.php" method="POST">
          <button type="submit">Odhlásit se</button>
        </form>
        <?php if ($_SESSION['username'] === 'admin'): ?>
          <div class="admin-menu">
            <a href="admin.php" class="admin-link">Admin menu</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <h1>Ticket Systém - TICKIFY</h1>
  </header>

  <main>
    <form class="ticket-form" action="submit_ticket.php" method="POST" enctype="multipart/form-data">
      <label for="fullname">Jméno a příjmení:</label>
      <input type="text" id="fullname" name="fullname" required>

      <label for="email">E-mail:</label>
      <input type="email" id="email" name="email" required>

      <label for="category">Kategorie problému:</label>
      <select id="category" name="category" required>
        <option value="software">Software</option>
        <option value="hardware">Hardware</option>
        <option value="ucet">Účet</option>
        <option value="jine">Jiné</option>
      </select>

      <label for="priority">Priorita:</label>
      <select id="priority" name="priority" required>
        <option value="nizka">Nízká</option>
        <option value="stredni">Střední</option>
        <option value="vysoka">Vysoká</option>
      </select>

      <label for="problem">Popis problému:</label>
      <textarea id="problem" name="problem" rows="6" required></textarea>

      <label for="attachment">Příloha:</label>
      <input type="file" id="attachment" name="attachment">

      <button type="submit">Odeslat tiket</button>
    </form>
  </main>

  <footer>
    <p>&copy; 2025 Ticket System. Všechna práva vyhrazena.</p>
  </footer>
</body>
</html>
