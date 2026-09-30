<?php
if (isset($_GET['file'])) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/" . basename($_GET['file']);

    if (file_exists($filePath)) {

        if (ob_get_length()) ob_end_clean();
        // Set headers to trigger download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Read the file and send it to the client
        readfile($filePath);

        // After sending the file, delete it
        unlink($filePath);

        exit;
    } else {
        http_response_code(404);
        echo "File not found.";
    }
}
?>
