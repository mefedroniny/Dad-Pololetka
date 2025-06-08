<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['username'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="tikety.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['ID', 'Jméno', 'Email', 'Kategorie', 'Priorita', 'Stav', 'Popis']);

    $result = $conn->query("SELECT id, fullname, email, category, priority, status, problem FROM tickets");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ticket_id'], $_POST['status'])) {
        $ticketId = (int) $_POST['ticket_id'];
        $status = $_POST['status'];
        $assignedTo = isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : 0;

        $stmt = $conn->prepare("UPDATE tickets SET status=?, assigned_to=? WHERE id=?");
        $stmt->bind_param("sii", $status, $assignedTo, $ticketId);
        $stmt->execute();

        header("Location: admin.php");
        exit();
    }

    if (isset($_POST['ticket_id'], $_POST['admin_reply'])) {
        $ticketId = (int) $_POST['ticket_id'];
        $reply = $conn->real_escape_string($_POST['admin_reply']);

        $stmt = $conn->prepare("UPDATE tickets SET admin_reply=?, status='uzavřený' WHERE id=?");
        $stmt->bind_param("si", $reply, $ticketId);
        $stmt->execute();
        header("Location: admin.php");
        exit();
    }
}

if (isset($_GET['close']) && is_numeric($_GET['close'])) {
    $ticketId = (int) $_GET['close'];
    $stmt = $conn->prepare("UPDATE tickets SET status='uzavřený' WHERE id=?");
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

$users = $conn->query("SELECT id, username, email, role FROM users");
$roleStats = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roleSummary = [];
while ($row = $roleStats->fetch_assoc()) {
    $roleSummary[$row['role']] = $row['count'];
}

$view = $_GET['view'] ?? 'active';
$priorityFilter = $_GET['priority'] ?? '';
$search = $conn->real_escape_string($_GET['search'] ?? '');
$onlyMine = isset($_GET['mine']) ? true : false;

$whereClauses = [];
$adminId = $_SESSION['user_id'] ?? 0;

if ($view === 'history') {
    $whereClauses[] = "status = 'uzavřený'";
} else {
    $whereClauses[] = "status != 'uzavřený'";
}

if (!empty($priorityFilter)) {
    $whereClauses[] = "priority = '$priorityFilter'";
}

if (!empty($search)) {
    $whereClauses[] = "(fullname LIKE '%$search%' OR email LIKE '%$search%')";
}

if ($onlyMine) {
    $whereClauses[] = "assigned_to = $adminId";
}

$whereSQL = implode(' AND ', $whereClauses);
$tickets = $conn->query("SELECT * FROM tickets WHERE $whereSQL ORDER BY created_at DESC");
$totalTickets = $conn->query("SELECT COUNT(*) AS count FROM tickets")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Admin Zázemí</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .status-open { color: green; font-weight: bold; }
    .status-in_progress { color: orange; font-weight: bold; }
    .status-uzavřený { color: red; font-weight: bold; }
  </style>
</head>
<body>
<header>
  <div class="login-box">
    <p>Přihlášen jako <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
    <form action="logout.php" method="POST"><button type="submit">Odhlásit se</button></form>
  </div>
  <h1>Admin – Správa tiketů</h1>
  <p>Celkový počet tiketů: <strong><?php echo $totalTickets; ?></strong></p>
  <nav>
    <a href="admin.php" style="<?php echo $view === 'active' ? 'font-weight:bold;' : ''; ?>">Aktivní tikety</a> |
    <a href="admin.php?view=history" style="<?php echo $view === 'history' ? 'font-weight:bold;' : ''; ?>">Historie</a> |
    <a href="admin.php?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>">Export CSV</a> |
    <a href="roles.php" style="">Správa rolí</a> |
  </nav>
  <form method="GET" style="margin-top:10px;">
    <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
    <label>Priorita:
      <select name="priority" onchange="this.form.submit()">
        <option value="">-- všechny --</option>
        <option value="nízká" <?php echo ($priorityFilter === 'nízká') ? 'selected' : ''; ?>>Nízká</option>
        <option value="střední" <?php echo ($priorityFilter === 'střední') ? 'selected' : ''; ?>>Střední</option>
        <option value="vysoká" <?php echo ($priorityFilter === 'vysoká') ? 'selected' : ''; ?>>Vysoká</option>
      </select>
    </label>
    <label>Hledat:
      <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="jméno nebo e-mail">
    </label>
    <label><input type="checkbox" name="mine" onchange="this.form.submit()" <?php echo $onlyMine ? 'checked' : ''; ?>> Pouze moje</label>
    <noscript><button type="submit">Filtrovat</button></noscript>
  </form>
</header>
<main>
  <div class="ticket-form">
    <?php while($ticket = $tickets->fetch_assoc()): ?>
      <?php
        $priorityClass = match ($ticket['priority']) {
          'vysoká' => 'priority-high',
          'střední' => 'priority-medium',
          'nízká' => 'priority-low',
          default => ''
        };
        $statusIcon = match ($ticket['status']) {
          'open' => '✅',
          'in_progress' => '⏳',
          'uzavřený' => '❌',
          default => '❔'
        };
      ?>
      <div class="ticket-entry">
        <p><strong>Jméno:</strong> <?php echo htmlspecialchars($ticket['fullname']); ?></p>
        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($ticket['email']); ?></p>
        <p><strong>Kategorie:</strong> <?php echo htmlspecialchars($ticket['category']); ?></p>
        <p><strong>Priorita:</strong> <span class="<?php echo $priorityClass; ?>"><?php echo htmlspecialchars($ticket['priority']); ?></span></p>
        <p><strong>Popis:</strong> <?php echo nl2br(htmlspecialchars($ticket['problem'])); ?></p>
        <p><strong>Stav:</strong> <span class="status-<?php echo $ticket['status']; ?>"><?php echo $statusIcon . ' ' . htmlspecialchars($ticket['status']); ?></span></p>
        <?php if (!empty($ticket['attachment'])): ?>
          <p><strong>Příloha:</strong> <a href="uploads/<?php echo htmlspecialchars($ticket['attachment']); ?>" target="_blank">Zobrazit</a></p>
        <?php endif; ?>

        <?php if ($view === 'active'): ?>
        <form method="POST" class="ticket-form">
          <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
          <label>Stav:
            <select name="status">
              <option value="open" <?php echo ($ticket['status'] === 'open') ? 'selected' : ''; ?>>Otevřený</option>
              <option value="in_progress" <?php echo ($ticket['status'] === 'in_progress') ? 'selected' : ''; ?>>V řešení</option>
              <option value="uzavřený" <?php echo ($ticket['status'] === 'uzavřený') ? 'selected' : ''; ?>>Uzavřený</option>
            </select>
          </label>
          <label>Support:
            <select name="assigned_to">
              <option value="0">Nepřiřazeno</option>
              <?php
              $result = $conn->query("SELECT id, username FROM users WHERE role = 'support'");
              while ($support = $result->fetch_assoc()) {
                echo "<option value='" . $support['id'] . "' " . ($ticket['assigned_to'] == $support['id'] ? 'selected' : '') . ">" . htmlspecialchars($support['username']) . "</option>";
              }
              ?>
            </select>
          </label>
          <button type="submit">Uložit změny</button>
        </form>

        <form method="POST" class="ticket-form">
          <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
          <label for="admin_reply">Odpověď:</label>
          <textarea name="admin_reply" rows="3"><?php echo htmlspecialchars($ticket['admin_reply'] ?? ''); ?></textarea>
          <button type="submit">Odeslat odpověď</button>
        </form>
        <p><a href="admin.php?close=<?php echo $ticket['id']; ?>">Uzavřít tiket</a></p>
        <?php else: ?>
        <p><strong>Odpověď admina:</strong><br><?php echo nl2br(htmlspecialchars($ticket['admin_reply'])); ?></p>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </div>
</main>
<footer>
  <p>&copy; 2025 Ticket System. Všechna práva vyhrazena.</p>
</footer>
</body>
</html>