<?php
session_start();
require 'db.php';

// Zkontroluj, zda je uživatel přihlášen jako admin
if (!isset($_SESSION['loggedin']) || $_SESSION['username'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$roleFilter = $_GET['filter_role'] ?? '';
$search = $_GET['search'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Zkontroluj, jestli je user_id a new_role nastaveno v POST
    if (isset($_POST['user_id'], $_POST['new_role']) && is_numeric($_POST['user_id'])) {
        $userId = (int) $_POST['user_id'];
        $newRole = $_POST['new_role'];
        
        // Ověřte, že role je jednou z povolených
        $validRoles = ['user', 'support', 'admin'];
        if (!in_array($newRole, $validRoles)) {
            echo "Neplatná role.";
            exit();
        }

        // Ověření, že uživatel má správná oprávnění k úpravě role
        if ($userId !== $_SESSION['user_id']) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $newRole, $userId);
            $stmt->execute();
            header("Location: roles.php");
            exit();
        } else {
            echo "Nelze změnit roli pro aktuálně přihlášeného uživatele.";
        }
    }
}

// Filtrování podle role a vyhledávání
$where = [];
if (!empty($roleFilter)) {
    $roleFilterSafe = $conn->real_escape_string($roleFilter);
    $where[] = "role = '" . $roleFilterSafe . "'";
}
if (!empty($search)) {
    $searchSafe = $conn->real_escape_string($search);
    $where[] = "(username LIKE '%$searchSafe%' OR email LIKE '%$searchSafe%')";
}
$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$users = $conn->query("SELECT id, username, email, role FROM users $whereSQL");
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa rolí</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #1f2937;
            color: white;
            padding: 20px;
        }
        .login-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: #60a5fa;
            margin-right: 10px;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
        main {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
        }
        form {
            margin: 0;
        }
        button {
            background-color: #3b82f6;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2563eb;
        }
        select, input[type="text"] {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }
        label {
            margin-right: 15px;
        }
        footer {
            background: #e5e7eb;
            text-align: center;
            padding: 15px;
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<header>
    <div class="login-box">
        <p>Přihlášen jako <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        <form action="logout.php" method="POST"><button type="submit">Odhlásit se</button></form>
    </div>
    <nav style="margin-top: 10px;">
        <a href="admin.php">← Zpět do administrace</a>
    </nav>
    <h1>Správa uživatelských rolí</h1>
</header>
<main>
    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 20px; align-items: center;">
        <label>Filtrovat roli:
            <select name="filter_role" onchange="this.form.submit()">
                <option value="">-- všechny --</option>
                <option value="user" <?php if ($roleFilter === 'user') echo 'selected'; ?>>user</option>
                <option value="support" <?php if ($roleFilter === 'support') echo 'selected'; ?>>support</option>
                <option value="admin" <?php if ($roleFilter === 'admin') echo 'selected'; ?>>admin</option>
            </select>
        </label>
        <label>Hledat:
            <input type="text" name="search" placeholder="uživatel nebo email" value="<?php echo htmlspecialchars($search); ?>">
        </label>
        <noscript><button type="submit">Filtrovat</button></noscript>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Uživatelské jméno</th>
                <th>Email</th>
                <th>Role</th>
                <th>Změna role</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <select name="new_role">
                            <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>user</option>
                            <option value="support" <?php if ($user['role'] === 'support') echo 'selected'; ?>>support</option>
                            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>admin</option>
                        </select>
                        <button type="submit">Změnit</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<footer>
    <p>&copy; 2025 Ticket System. Všechna práva vyhrazena.</p>
</footer>
</body>
</html>
