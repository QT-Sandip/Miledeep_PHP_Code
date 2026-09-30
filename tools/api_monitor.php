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
        <h2>🔐 API Monitor Login</h2>
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
include_once ROOT.'config/config.php';
require_once __DIR__.'/../config/Dotenv.php';

function get_db_connection() {
    $db_host = (getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST'];
    $db_user = (getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
    $db_pass = (getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];
    $db_name = (getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name, $db_user, $db_pass, [
            PDO::ATTR_PERSISTENT => true, // persistent connection
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
    return $pdo;
}

$pdo = get_db_connection(); 

$start = $_GET['start']?$_GET['start']:date('Y-m-d', strtotime('-1 day'));
$end = $_GET['end']?$_GET['end']:date('Y-m-d');

$filter = $pdo->prepare("
    SELECT * FROM api_logs 
    WHERE created_at BETWEEN :start AND :end 
    ORDER BY created_at DESC
    LIMIT 100
");
$filter->execute([
    ':start' => $start . ' 00:00:00',
    ':end' => $end . ' 23:59:59'
]);

$logs = $filter->fetchAll(PDO::FETCH_ASSOC);

// Stats
$summary = $pdo->query("
    SELECT 
        COUNT(*) total,
        SUM(http_code BETWEEN 200 AND 299) success,
        SUM(http_code >= 400) errors,
        MAX(response_time_ms) max_time,
        AVG(response_time_ms) avg_time
    FROM api_logs 
    WHERE created_at BETWEEN '{$start} 00:00:00' AND '{$end} 23:59:59'
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Monitor</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f4f4f4; }
        h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 8px #ccc; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; font-size: 13px; }
        th { background: #444; color: #fff; }
        .ok { color: green; }
        .error { color: red; }
        form { margin-bottom: 10px; }
        input, button { padding: 5px 10px; }
    </style>
</head>
<body><p style="text-align: right;"><a href="?logout=1">🚪 Logout</a></p>

    <h2>📡 API Monitor Dashboard</h2>

    <form method="get">
        <label>Date Range: </label>
        <input type="date" name="start" value="<?= $start ?>"> to 
        <input type="date" name="end" value="<?= $end ?>">
        <button type="submit">Filter</button>
    </form>

    <h3>🔍 Summary (<?= $start ?> → <?= $end ?>)</h3>
    <ul>
        <li><strong>Total Requests:</strong> <?= $summary['total'] ?></li>
        <li><strong>Success:</strong> <?= $summary['success'] ?></li>
        <li><strong>Errors:</strong> <?= $summary['errors'] ?></li>
        <li><strong>Max Time:</strong> <?= $summary['max_time'] ?> ms</li>
        <li><strong>Avg Time:</strong> <?= round($summary['avg_time'], 2) ?> ms</li>
    </ul>

    <table>
        <tr>
            <th>Date</th>
            <th>Script</th>
            <th>Method</th>
            <th>Endpoint</th>
            <th>Status</th>
            <th>Time (ms)</th>
            <th>Size (B)</th>
        </tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['created_at'] ?></td>
            <td><?= basename($log['script_name']) ?></td>
            <td><?= $log['method'] ?></td>
            <td style="max-width: 300px; overflow: hidden;"><?= htmlspecialchars(substr($log['endpoint'], 0, 80)) ?>...</td>
            <td class="<?= $log['http_code'] >= 400 ? 'error' : 'ok' ?>"><?= $log['http_code'] ?></td>
            <td><?= $log['response_time_ms'] ?></td>
            <td><?= $log['response_size'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
