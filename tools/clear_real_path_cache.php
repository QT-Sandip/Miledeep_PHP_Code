<?php
session_start();

$USERNAME = 'mdwcamaroo';          
$PASSWORD = 'camaroo@mdw';    

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
    <title>Migration tool</title>
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
        <h2>🔐 Migration tool Login</h2>
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
$cache = realpath_cache_get();

if ($cache && is_array($cache)) {
    echo "Number of entries: " . count($cache) . "<br>";
    echo "<h2>Realpath cache contents:</h2>";
    echo "<pre>";
    print_r($cache);
    echo "</pre>";
} else {
    echo "Realpath cache is empty or not available.";
}
?>
