<?php

set_time_limit(0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*if (PHP_SAPI !== 'cli') {
    echo "This job must be run from CLI.\n";
    exit(1);
}*/

require_once __DIR__ . '/../ApiV2/Config/Database.php';
require_once __DIR__ . '/../ApiV2/RmPurchase/Controller/AuditLogger.php';


$options = getopt('', [
    'archive-days::',
    'purge-days::',
    'batch-size::',
]);

$archiveDays = isset($options['archive-days'])
    ? (int)$options['archive-days']
    : 30;

$purgeDays = isset($options['purge-days'])
    ? (int)$options['purge-days']
    : 60; // 7 years

$batchSize = isset($options['batch-size'])
    ? (int)$options['batch-size']
    : 5000;

if ($archiveDays <= 0) {
    $archiveDays = 180;
}

if ($purgeDays <= 0) {
    $purgeDays = 2555;
}

if ($batchSize <= 0) {
    $batchSize = 5000;
}
echo "testing1";
try {

    $database = new Database();

    $authConn = $database->getAuthConnection();

    if (!$authConn instanceof PDO) {
        fwrite(STDERR, "Unable to connect to auth database.\n");
        exit(1);
    }

    $authConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $authConn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $logger = new AuditLogger(
        $authConn,
        'System',
        $authConn
    );

    fwrite(
        STDOUT,
        sprintf(
            "[%s] Audit retention started (archive=%d days, purge=%d days, batch=%d)\n",
            date('Y-m-d H:i:s'),
            $archiveDays,
            $purgeDays,
            $batchSize
        )
    );

    $result = $logger->runRetentionPolicy(
        $archiveDays,
        $purgeDays,
        $batchSize
    );

    fwrite(
        STDOUT,
        sprintf(
            "[%s] Audit retention completed. RunID=%s Archived=%d Purged=%d\n",
            date('Y-m-d H:i:s'),
            $result['RunID'],
            $result['Archived'],
            $result['Purged']
        )
    );

    exit(0);

} catch (Exception $exception) {

    fwrite(
        STDERR,
        sprintf(
            "[%s] Audit retention failed: %s\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage()
        )
    );

    error_log(
        'Audit retention cron failed: ' .
        $exception->getMessage()
    );

    exit(1);
}

//Run every day at 2:00 AM:
//0 2 * * * /usr/bin/php /path/to/cron/audit_retention.php
//Run with custom values:
//0 2 * * * /usr/bin/php /path/to/cron/audit_retention.php --archive-days=365 --purge-days=3650 --batch-size=10000

//
