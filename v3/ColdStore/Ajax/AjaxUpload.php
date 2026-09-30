<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$PlantId = $_SESSION['UnitID'];
$ClientId = $_SESSION["ClientID"];

$ClientPath = '/uploads/' . $ClientId . '/' . $PlantId . '/' . date("d-M-Y") . '/';
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . $ClientPath;

if (!is_dir($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
	$file = $_FILES['file'];
	$Type = $_POST['Type'] ? $_POST['Type'] : '';
	$FileName = $_POST['FileName'] ? $_POST['FileName'] : '';

	if ($file['error'] !== UPLOAD_ERR_OK) {
		die("Error during file upload: " . $file['error']);
	}

	$allowedTypes = ['application/pdf'];
	$fileMimeType = mime_content_type($file['tmp_name']);
	if (!in_array($fileMimeType, $allowedTypes)) {
		die("Invalid file type. Only PDF files are allowed.");
	}

	$maxFileSize = 2 * 1024 * 1024;
	if ($file['size'] > $maxFileSize) {
		die("File size exceeds the 2MB limit.");
	}

	$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
	$newFileName = !empty($FileName)
		? $FileName . '.' . $fileExtension
		: uniqid('file_', true) . '.' . $fileExtension;

	$targetFilePath = $uploadDir . $newFileName;
	$ClientPath .= $newFileName;

	if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
		$Date = date("Y-m-d");
		$UserId = $_SESSION['UserID'];

		$sql = "INSERT INTO `FileUploadAssets`(`Date`, `PlantID`, `FileName`, `FilePath`, `UserName`, `Type`) 
        VALUES (CURDATE(), '$PlantId', '$FileName', '$ClientPath', '$UserId', '$Type')";

		$stmt = $iconn->query($sql);
		if($stmt){
			legacyAuditLogInsertById('FileUploadAssets', 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'File uploaded'], 'ColdStore');
		}
		echo "File uploaded successfully";
	} else {
		die("Failed to move uploaded file.");
	}
} else {
	die("No file uploaded.");
}
?>
