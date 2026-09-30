<?php

session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PlantId = $_SESSION['UnitID'];
$ClientId = $_SESSION["ClientID"];



$ClientPath = '/uploads/' . $ClientId . '/' . $PlantId . '/'; // Base directory where folders are stored
$today = new DateTime(); // Current date
$cutoffDate = $today->sub(new DateInterval('P6D'));

$basePath = $_SERVER['DOCUMENT_ROOT'] . $ClientPath;


function deleteOldFiles($directory, $cutoffDate)
{
    if (is_dir($directory)) {

        $folders = scandir($directory); // Scan the directory for folders and files

        foreach ($folders as $folder) {
            // Skip current and parent directory references
            if ($folder === '.' || $folder === '..') {
                continue;
            }

            // Match folder names with date in 'DD-MMM-YYYY' format
            if (preg_match('/\d{2}-[A-Za-z]{3}-\d{4}/', $folder, $matches)) {
                $folderDate = DateTime::createFromFormat('d-M-Y', $matches[0]);
                if ($folderDate && $folderDate < $cutoffDate) {
                    $folderPath = $directory . '/' . $folder;

                    // Recursively delete folder and its contents
                    deleteFolder($folderPath);
                    // echo "Deleted folder: $folderPath\n";
                }
            }
        }
    }
}

function deleteFolder($folderPath)
{
    $files = array_diff(scandir($folderPath), array('.', '..')); // List files and directories except "." and ".."

    foreach ($files as $file) {
        $filePath = $folderPath . '/' . $file;
        if (is_dir($filePath)) {
            deleteFolder($filePath); // Recursively delete subfolders
        } else {
            unlink($filePath); // Delete file
        }
    }

    rmdir($folderPath); // Remove the empty folder
}

// Call the function
echo deleteOldFiles($basePath, $cutoffDate);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ProcessingPlantId = $PlantId ?: '';
    $ProcessingPlantIdQuery = "`Date` BETWEEN DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND CURDATE()";
    if (isset($_POST['processingPlant']) && !empty($_POST['processingPlant'])) {
        $ProcessingPlantId = $_POST['processingPlant'];
        $ProcessingPlantIdQuery .= " and FU.PlantID = $ProcessingPlantId";
    }
    // Deleting older files
    $iconn->query('DELETE FROM FileUploadAssets WHERE `Date` < DATE_SUB(CURDATE(), INTERVAL 6 DAY)');
    legacyAuditLogDeleteById('FileUploadAssets', 'ID', 0, null, $_POST, ['Remarks' => 'Bulk delete old file uploads (older than 6 days)'], 'ColdStore');
    $sqlQuery = "SELECT FU.*, PRC.StationName FROM `FileUploadAssets` FU LEFT JOIN `RmProcessingStations` PRC ON FU.PlantID = PRC.ProcessingStationID WHERE $ProcessingPlantIdQuery ORDER BY FU.Date DESC";

    $result = $iconn->query($sqlQuery);
    if ($result) {
        if (!isset($error) || empty($error)) { ?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Plant</th>
                        <th>File Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $result->fetch_array()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($item['Date']))); ?></td>
                            <td><?php echo htmlspecialchars($item['StationName']); ?></td>
                            <td><?php echo htmlspecialchars($item['FileName']); ?></td>
                            <td class="text-center">
                                <a target="_blank" href="<?php echo htmlspecialchars($item['FilePath']); ?>" class="mr-3 text-primary"
                                    data-toggle="tooltip" data-placement="top" title="" data-original-title="View">
                                    <i class="mdi mdi-eye font-size-18"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php }
        $stmt->close(); // Close the statement
    } else {
        // Handle the error during statement preparation
        echo "Error preparing statement: " . $iconn->error;
    }
}
?>
