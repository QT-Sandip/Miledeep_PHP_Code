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
    <title>Login | Slow Request Log Monitor</title>
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
        <h2>🔐 Slow Request Log Monitor</h2>
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

$logFile = 'slow_scripts.log';
$numLines = 50;
$toEmails = [
    'msreenivas.mdw@gmail.com',
    'jagadeesh.mdw@gmail.com'
];

// Validate recipient email
foreach ($toEmails as $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address: $email\n");
    }
}

$to = implode(', ', $toEmails); // Now safe to use in mail()

$subject = "Slow log – Last $numLines lines @ " . date('Y-m-d H:i:s');

// Function to get last N lines of a file efficiently
function tailFile($filepath, $lines = 50) {
    $f = fopen($filepath, "rb");
    if (!$f) {
        return false;
    }
    $buffer = '';
    $chunkSize = 4096; // bytes
    fseek($f, 0, SEEK_END);
    $pos = ftell($f);
    $lineCount = 0;

    while ($pos > 0 && $lineCount <= $lines) {
        $readSize = ($pos - $chunkSize) > 0 ? $chunkSize : $pos;
        $pos -= $readSize;
        fseek($f, $pos);
        $chunk = fread($f, $readSize);
        $buffer = $chunk . $buffer;
        $lineCount = substr_count($buffer, "\n");
    }
    fclose($f);

    $linesArr = explode("\n", trim($buffer));
    return array_slice($linesArr, -$lines);
}

if (!file_exists($logFile)) {
    die("Log file not found: $logFile\n");
}

if (!is_readable($logFile)) {
    die("No permission to read $logFile\n");
}

$lastLines = tailFile($logFile, $numLines);

if ($lastLines === false) {
    die("Failed to read log file\n");
}
$message = "";
$message .= "<table border='1' cellpadding='6'>
    <tr>
        <th>Timestamp</th>
        <th>Duration (sec)</th>
        <th>Client</th>
        <th>URL</th>
    </tr>";

foreach ($lastLines as $log) {
    preg_match('/\[(.*?)\].*?([\d\.]+).*?URL: (.*?)(?: ClientID: (\w+))?$/', $log, $matches);
    $message .= "<tr>";
    $message .= "<td>{$matches[1]}</td>";
    $message .= "<td>{$matches[2]}</td>";
    $message .= "<td>{$matches[4]}</td>";
    $message .= "<td>{$matches[3]}</td>";
    $message .= "</tr>";
}

$message .= "</table>";
echo $message;
$from = 'support@camaroo.in';
$eol = "\r\n";
$headers = "From: $from" . $eol;
$headers .= "Reply-To: $from" . $eol;
$headers .= "MIME-Version: 1.0" . $eol;
$headers .= "Content-Type: text/html; charset=UTF-8" . $eol;

$message = "<table border='1' cellpadding='6'><tr><th>Timestamp</th><th>Duration (sec)</th><th>URL</th></tr>";

foreach ($lastLines as $log) {
    preg_match('/\[(.*?)\].*?([\d\.]+).*?URL: (.*)/', $log, $matches);
    if (!empty($matches)) {
        $message .= "<tr>";
        $message .= "<td>" . htmlspecialchars($matches[1]) . "</td>";
        $message .= "<td>" . htmlspecialchars($matches[2]) . "</td>";
        $message .= "<td>" . htmlspecialchars($matches[3]) . "</td>";
        $message .= "</tr>";
    }
}
$message .= "</table>";

// Send email
if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully to $to\n";
} else {
    echo "Failed to send email\n";
}
?>