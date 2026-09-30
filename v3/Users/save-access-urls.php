<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/config/api-call.php");

$PageTitle = "Save access urls";
$PageTitleFolder = "Users";

// 1. Get active users who have a role
$getuserdata = "SELECT UserID, UserRoleID, ClientID FROM `AdminLoginDetails` WHERE UserRoleID != 0 AND Status = 1 ORDER BY `AdminLoginDetails`.`ClientID` ASC";
$stmtUsers = $iconn->query($getuserdata);

if ($stmtUsers) {

    // Array to cache client DB connections so we don't reconnect hundreds of times
    $clientConnections = [];

    // 2. Loop through EVERY user
    while ($usersdata = $stmtUsers->fetch_assoc()) {

        $UserID     = $usersdata['UserID'];
        $UserRoleID = $usersdata['UserRoleID'];
        $ClientID   = $usersdata['ClientID'];

        // Fetch User Role Details
        $SqlRoles = "SELECT * FROM `AdminUserRoles` WHERE ID = '" . $iconn->real_escape_string($UserRoleID) . "'";
        $stmtRoles = $iconn->query($SqlRoles);

        $rowAccessRoles = [];

        if ($stmtRoles && $stmtRoles->num_rows > 0) {
            $rowAccessRoles = $stmtRoles->fetch_assoc();
        } else {
            continue;
        }

        $Options = !empty($rowAccessRoles['Options']) ? $rowAccessRoles['Options'] : '0';

        // Sanitize the CSV string of IDs safely for the IN clause
        $cleanOptions = implode(',', array_map('intval', explode(',', $Options)));
        if (empty($cleanOptions)) {
            $cleanOptions = '0';
        }

        $sqlQuery = "SELECT * FROM `ClientSideBarOptions` WHERE ID IN ($cleanOptions)";
        $stmtOptions = $iconn->query($sqlQuery);

        if ($stmtOptions) {

            // Setup/Fetch the client dynamic PDO database connection once per unique client
            if (!isset($clientConnections[$ClientID])) {
                $sqlGetClient = "SELECT * FROM `AdminClientDbDetails` WHERE ClientID = ? AND DbStatus = 1 LIMIT 1";
                $stmtClient = $iconn->prepare($sqlGetClient);
                $stmtClient->bind_param("s", $ClientID);
                $stmtClient->execute();
                $DbDetails = $stmtClient->get_result()->fetch_assoc();
                $stmtClient->close();

                if ($DbDetails) {
                    $host          = $DbDetails['DbHost'];
                    $database_name = $DbDetails['DbName'];
                    $username      = $DbDetails['DbUserName'];
                    $password      = $DbDetails['DbPassword'];

                    $pdoOptions = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ];

                    try {
                        $pdoConn = new PDO("mysql:host=$host;dbname=$database_name;charset=utf8", $username, $password, $pdoOptions);
                        $clientConnections[$ClientID] = $pdoConn;
                    } catch (\PDOException $e) {
                        error_log("DB Connection failed for Client $ClientID: " . $e->getMessage());
                        $clientConnections[$ClientID] = null;
                    }
                } else {
                    $clientConnections[$ClientID] = null;
                }
            }

            // If we don't have a valid connection to this client's DB, skip processing
            if (empty($clientConnections[$ClientID])) {
                continue;
            }

            $clientApi = $clientConnections[$ClientID];

            // PREPARE STATEMENTS FOR DUPLICATE CHECK & INSERTION
            $checkSql  = "SELECT COUNT(*) FROM `AdminSideBarOptions` WHERE `PathUrl` = :path_url AND `UserID` = :user_id";
            $stmtCheck = $clientApi->prepare($checkSql);

            $insertSql  = "INSERT INTO `AdminSideBarOptions` (`PathUrl`, `UserID`) VALUES (:path_url, :user_id)";
            $stmtInsert = $clientApi->prepare($insertSql);

            // Loop through options
            while ($dataRowop = $stmtOptions->fetch_assoc()) {
                $currentPageBase = $dataRowop['Directory'] . $dataRowop['FileName'];

                try {
                    // Step A: Check if the record already exists
                    $stmtCheck->execute([
                        ':path_url' => $currentPageBase,
                        ':user_id'  => $UserID
                    ]);
                    $exists = $stmtCheck->fetchColumn();

                    // Step B: Only insert if it doesn't exist
                    if ($exists == 0) {
                        $stmtInsert->execute([
                            ':path_url' => $currentPageBase,
                            ':user_id'  => $UserID
                        ]);
                    }

                } catch (\PDOException $e) {
                    error_log("Database operation failed for User $UserID: " . $e->getMessage());
                }
            }
        } else {
            continue;
        }
    }
}
?>
