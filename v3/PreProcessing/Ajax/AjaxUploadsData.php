<?php

session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
// $methodType = trim($_POST["methodType"]);

$PlantId = $_SESSION['UnitID'];
$ClientId = $_SESSION["ClientID"];
$AccessType = $_SESSION['AccessType'];
$ClientPath = '/uploads/' . $ClientId . '/' . $PlantId . '/'; // Base directory
$basePath = $_SERVER['DOCUMENT_ROOT'] . $ClientPath;

// Fetch expired files based on ExpiryDate rule
$sqlExpiredFilesQuery = "SELECT FilePath FROM FileUploadAssets WHERE ExpiryDate < CURDATE()";
$sqlExpiredFiles = $iconn->query($sqlExpiredFilesQuery);

if ($sqlExpiredFiles) {
    while ($row = $sqlExpiredFiles->fetch_assoc()) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $row['FilePath']; // Convert DB path to server path
        if (file_exists($filePath)) {
            unlink($filePath); // Delete file
        }
    }
}

// Now, delete expired records from the database
$deleteQuery = "DELETE FROM FileUploadAssets WHERE ExpiryDate < CURDATE()";
$iconn->query($deleteQuery);

// Function to clean empty folders
function deleteEmptyFolders($directory)
{
    if (!is_dir($directory)) {
        return;
    }

    $items = array_diff(scandir($directory), array('.', '..'));

    foreach ($items as $item) {
        $itemPath = $directory . '/' . $item;

        if (is_dir($itemPath)) {
            deleteEmptyFolders($itemPath); // Recursively clean subfolders

            // If folder is empty after cleaning, delete it
            if (count(scandir($itemPath)) == 2) {
                rmdir($itemPath);
            }
        }
    }
}

// Call the function to remove empty directories
deleteEmptyFolders($basePath);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ProcessingPlantId = $PlantId ?: '';
    $ProcessingPlantIdQuery = "`ExpiryDate` >= CURDATE()"; // Show only valid files

    if (isset($_POST['processingPlant']) && !empty($_POST['processingPlant'])) {
        $ProcessingPlantId = $_POST['processingPlant'];
        $ProcessingPlantIdQuery .= " AND FU.PlantID = $ProcessingPlantId";
    }

    $sqlQuery = "SELECT FU.*, PRC.StationName 
                 FROM `FileUploadAssets` FU 
                 LEFT JOIN `RmProcessingStations` PRC ON FU.PlantID = PRC.ProcessingStationID 
                 WHERE $ProcessingPlantIdQuery 
                 ORDER BY FU.Date DESC";

    $result = $iconn->query($sqlQuery);
    if ($result) {
        if (!isset($error) || empty($error)) { ?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Expires On</th>
                        <th>Plant</th>
                        <th>File Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $result->fetch_array()) {

                        $fullPath = $item['FilePath']; // e.g., /uploads/DFLV3/1/29-Apr-2025/UNIT-5 STOCK - 28.04.2025.pdf
                        $dir = dirname($fullPath);
                        $file = basename($fullPath);
                        $encodedFile = rawurlencode($file);


                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($item['Date']))); ?></td>
                            <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($item['ExpiryDate']))); ?></td>
                            <td><?php echo htmlspecialchars($item['StationName']); ?></td>
                            <td><?php echo htmlspecialchars($item['FileName']); ?></td>
                            <td class="text-center">
                                <a target="_blank" href="<?php echo htmlspecialchars($dir . '/' . $encodedFile); ?>"
                                    class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" title="View">
                                    <i class="mdi mdi-eye font-size-18"></i>
                                </a>
                                <?php if ($AccessType == 1) { ?>
                                    <button class="btn btn-link text-danger btnActions btnDelete btn-delete-file" data-toggle="tooltip"
                                        data-placement="top" title="Delete" value="" data-original-title="Delete"
                                        data-id="<?php echo htmlspecialchars($item['ID']); ?>"
                                        data-file-name="<?php echo htmlspecialchars($item['FileName']); ?>">
                                        <i class="mdi mdi-trash-can font-size-16"></i></button>
                                <?php } ?>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php }
    } else {
        echo "Error: " . $iconn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $inputData = file_get_contents("php://input");
    $data = json_decode($inputData, true);

    if (isset($data['fileId'])) {
        $FileID = $data['fileId'];

        $sqlExpiredFilesQuery = "SELECT FU.*, PRC.StationName 
            FROM `FileUploadAssets` FU 
            LEFT JOIN `RmProcessingStations` PRC ON FU.PlantID = PRC.ProcessingStationID 
            WHERE ID = '$FileID'";

        $sqlExpiredFiles = $iconn->query($sqlExpiredFilesQuery);
        $oldFileData = $sqlExpiredFiles ? $sqlExpiredFiles->fetch_assoc() : null;

        if ($oldFileData) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $oldFileData['FilePath'];

            // Delete file from the server
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $deleteQuery = "DELETE FROM `FileUploadAssets` WHERE ID = $FileID";
            if ($iconn->query($deleteQuery)) {
                legacyAuditLogDeleteById('FileUploadAssets', 'ID', $FileID, $oldFileData, $data, [
                    'ReferenceNo' => $FileID,
                    'Remarks' => 'File deleted: ' . ($oldFileData['FileName'] ?: ''),
                ], 'PreProcessing');
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete file record']);
            }
        }
    }
}

?>
