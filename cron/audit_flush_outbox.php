<?php

set_time_limit(0);

require_once __DIR__ . '/../ApiV2/Config/Database.php';
require_once __DIR__ . '/../ApiV2/RmPurchase/Controller/AuditLogger.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This job must be run from CLI.\n");
    exit(1);
}

$options = getopt('', [
    'limit::'
]);

$limit = isset($options['limit'])
    ? (int)$options['limit']
    : 500;

if ($limit <= 0) {
    $limit = 500;
}

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
            "[%s] Audit outbox flush started (limit=%d)\n",
            date('Y-m-d H:i:s'),
            $limit
        )
    );

    $processed = $logger->flushPendingOutbox($limit);

    fwrite(
        STDOUT,
        sprintf(
            "[%s] Audit outbox flush completed. Processed=%d\n",
            date('Y-m-d H:i:s'),
            $processed
        )
    );

    exit(0);

} catch (Exception $e) {

    fwrite(
        STDERR,
        sprintf(
            "[%s] Audit outbox flush failed: %s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage()
        )
    );

    error_log($e->getMessage());

    exit(1);
}
