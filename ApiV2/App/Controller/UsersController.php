<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'AppUtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class UsersController
{
    private $conn;
    private $appUtilController;
    private $auditLogger;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->appUtilController = new AppUtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Users');
    }

    public function getUsers($RequestBody)
    {
        $UserID = $RequestBody['UserID'];
        $AccessRoleID = $RequestBody['AccessRoleID'];
        $SalaryStructureData = $this->getUsersData($RequestBody, $AccessRoleID);

        $Data['Error'] = null;
        $Data['Msg'] = 'Posted Successful';
        $Data['Data'] = $SalaryStructureData;

        return $Data;
    }

    public function updateUser($RequestBody)
    {
        $userID = $RequestBody['userID'];
        $username = $RequestBody['username'];
        $designation = $RequestBody['designation'];
        $AccessIPs = $RequestBody['AccessIPs'];
        $ProductionPlant = $RequestBody['productionplant'];
        $AccessType = $RequestBody['AccessType'];
        $role = $RequestBody['role'];
        $status = $RequestBody['status'];

        $existingUser = $this->auditLogger->fetchRowById('AdminLoginDetails', 'UserID', $userID);

        $SqlElementIDs = "
            UPDATE `AdminLoginDetails`
            SET UserName = '$username',
                DesignationID = '$designation',
                AccessIPs = '$AccessIPs',
                UnitID = '$ProductionPlant',
                UserRoleID = '$role',
                Status = '$status',
                AccessType = '$AccessType'
            WHERE UserID = '$userID'
        ";

        $stmtElementIDs = $this->conn->prepare($SqlElementIDs);
        $stmtElementIDs->execute();

        if ($stmtElementIDs) {
            $Data['Error'] = null;

            $updatedUser = $this->auditLogger->fetchRowById('AdminLoginDetails', 'UserID', $userID);
            $this->auditLogger->logUpdate('AdminLoginDetails', $userID, $existingUser, $updatedUser, $RequestBody, [
                'ReferenceNo' => $username,
                'Remarks' => 'User profile updated',
            ]);
        } else {
            $Data['Error'] = 'Unable To Update Data';
        }

        $Data['Msg'] = 'Posted Successful';
        return $Data;
    }

    public function getUsersData($RequestBody, $AccessRoleID)
    {
        $ClientID = $RequestBody['ClientID'];
        $UserID = $RequestBody['ReqUserID'];
        $Status = $RequestBody['Status'];

        $Qry = "WHERE 1=1";

        if (!empty($UserID) && $ClientID == 'serp') {
            $Qry .= " AND ALD.UserID = '$UserID'";
        } elseif (!empty($UserID)) {
            $Qry .= " AND ALD.UserID = '$UserID' AND ALD.ClientID = '$ClientID'";
        } elseif (!empty($ClientID)) {
            $Qry .= " AND ALD.ClientID = '$ClientID'";
        }

        if( isset($RequestBody['Edituser']) && $RequestBody['Edituser'] ){

        }else{

            if ($Status !== '' && $Status !== null) {
                $Qry .= " AND ALD.Status = '$Status'";
            } else {
                $Qry .= " AND ALD.Status = '1'";
            }
        }

        $SqlElementIDs = "
            SELECT *
            FROM `AdminLoginDetails` AS ALD
            LEFT JOIN AdminDepartments AS AD ON ALD.DeptID = AD.DeptID
            LEFT JOIN AdminUserRoles AS AUR ON AUR.ID = ALD.UserRoleID
            LEFT JOIN AdminRegisteredClients AS ARC ON ARC.ClientAccountID = ALD.ClientID
            $Qry
        ";

        $stmtElementIDs = $this->conn->prepare($SqlElementIDs);
        $stmtElementIDs->execute();

        $AryData = [];

        while ($dataElementIDs = $stmtElementIDs->fetch(PDO::FETCH_ASSOC)) {
            $dataElementIDs['UserPassword'] = '';
            $dataElementIDs['Inactive'] = $dataElementIDs['Inactive'];
            $dataElementIDs['Active'] = $dataElementIDs['Inactive'];
            $AryData[] = $dataElementIDs;
        }

        return $AryData;
    }
}
