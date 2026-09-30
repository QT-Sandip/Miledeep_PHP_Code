<?php
session_start();

$USERNAME = 'admin';          // Change this to your username
$PASSWORD = 'supersecret';    // Change this to your password

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    if ($_POST['username'] === $USERNAME && $_POST['password'] === $PASSWORD) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "❌ Invalid username or password.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Show login form if not logged in
if (empty($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login | API Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; height: 100vh; justify-content: center; align-items: center; }
        form { background: white; padding: 20px 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <form method="post" action="">
        <h2>🔐 Error Log Login</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <input type="text" name="username" placeholder="Username" required autofocus />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>
</body>
</html>
<?php
exit;
endif;
?>
<?php
// === CONFIG ===
$logFile = __DIR__ . '/../PHP_errors.log'; // Path to your PHP error log
//echo $logFile; 
$linesPerPage = 50;

// === Load and filter log ===
if (!file_exists($logFile)) {
    die("❌ error_log file not found.");
}

$allLines = file($logFile);
$allLines = array_reverse($allLines); // Show most recent first

// === Handle search ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $allLines = array_filter($allLines, function ($line) use ($search) {
        return stripos($line, $search) !== false;
    });
}

// === Pagination ===
$totalPages = 0;
$totalLines = count($allLines);
$totalPages = ceil($totalLines / $linesPerPage);
$page = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
$start = ($page - 1) * $linesPerPage;
$lines = array_slice($allLines, $start, $linesPerPage);

function cssClass($line) {
    if (stripos($line, 'fatal') !== false || stripos($line, 'error') !== false) return 'error';
    if (stripos($line, 'warning') !== false) return 'warning';
    if (stripos($line, 'notice') !== false) return 'notice';
    return '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Error Log Viewer</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #ddd; padding: 20px; }
        .line { white-space: pre-wrap; padding: 5px; border-bottom: 1px solid #333; }
        .error { color: #f77; }
        .warning { color: #ffa500; }
        .notice { color: #87cefa; }
        form { margin-bottom: 10px; }
        input[type="text"] { padding: 5px; width: 300px; }
        .pagination a, .pagination span {
            padding: 6px 10px;
            margin: 2px;
            background: #333;
            color: #ccc;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination .current { background: #007bff; color: #fff; }
    </style>
</head>
<body>
    <h2>📄 PHP Error Log Viewer</h2>
    <form method="get">
        <input type="text" name="search" placeholder="Search (e.g. Fatal, Warning)" value="<?= htmlspecialchars($search) ?>" />
        <button type="submit">Search</button>
    </form>
    <p>Showing <?= count($lines) ?> of <?= $totalLines ?> matching log entries.</p>

    <?php foreach ($lines as $line): ?>
        <div class="line <?= cssClass($line) ?>"><?= htmlspecialchars($line) ?></div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($totalPages > 1): ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                    $url = "?page=$i" . ($search !== '' ? "&search=" . urlencode($search) : '');
                    $class = ($i === $page) ? 'current' : '';
                ?>
                <a class="<?= $class ?>" href="<?= $url ?>"><?= $i ?></a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</body>
</html>