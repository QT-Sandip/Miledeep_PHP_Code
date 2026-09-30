<?php
// Start timer right at the beginning
$start = microtime(true);

// Register a shutdown function to run at the end of the script
register_shutdown_function(function () use ($start) {
    $end = microtime(true);
    $execTime = $end - $start;

    // Set your threshold here (30 seconds)
    if ($execTime > 30) {
        // Get the full requested URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // Prepare log message with timestamp, exec time, and URL
        $logMessage = sprintf(
            "[%s] Slow script detected: %.3f seconds - URL: %s ClientID: %s\n",
            date('Y-m-d H:i:s'),
            $execTime,
            $url,
            $_SESSION['ClientID']
        );

        // Log file path — change this to a writable location
        $logFile = __DIR__ . '/slow_scripts.log';

        // Append log message to the file
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
});