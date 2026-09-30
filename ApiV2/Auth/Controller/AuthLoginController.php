<?php

include_once '../Global/Util.php';
include_once '../Config/Token.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
class AuthLoginController
{

    private $conn;
    private $auditLogger;
    private $db_table = "AuthLoginDetails";

    public $id;
    public $name;
    public $email;
    public $age;
    public $designation;
    public $created;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Auth');
    }
    // GET ALL
    public function getEmployees()
    {
        $sqlQuery = "SELECT * FROM " . $this->db_table . "";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt;
    }
    // CREATE
    public function createEmployee()
    {
        $sqlQuery = "INSERT INTO
                        " . $this->db_table . "
                    SET
                        name = :name,
                        email = :email,
                        age = :age,
                        designation = :designation,
                        created = :created";

        $stmt = $this->conn->prepare($sqlQuery);

        // sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->designation = htmlspecialchars(strip_tags($this->designation));
        $this->created = htmlspecialchars(strip_tags($this->created));

        // bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":designation", $this->designation);
        $stmt->bindParam(":created", $this->created);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    // READ single
    public function postAuthorization($RequestBody)
    {

        /*    $SqlFields = "ClientName,AuthAccessIPs,AuthUserID,
                AuthUserName,AuthAccessType,Ad.AppDepartmentID,
                UnitID,IsDev,AuthUserRoleID,
                CDB.ClientID,StartYear,
                Path as CurrentClientPath,AuthUserPassword,
                AppSideBarHeaderTable,AppSideBarOptionTable,AuthUserStatus";

            $sqlQuery = "SELECT $SqlFields
            FROM AuthLoginDetails as LoginDtls
            left join AppDepartments as Ad on Ad.AppDepartmentID=LoginDtls.DepartmentID
            left join AuthClientDbDetails as CDB on CDB.ClientID=LoginDtls.ClientID
            left join AppRegisteredClients As ARC on ARC.ClientAccountID=LoginDtls.ClientID
            WHERE  AuthUserLoginID= :LoginID";*/

        $sqlQuery = "SELECT ARC.ClientName,AccessIPs AS AuthAccessIPs,UserID AS AuthUserID,Ad.DeptID,Ad.Directory,
        UserName AS AuthUserName,AccessType AS AuthAccessType,
        UnitID,StoreIDs,IsDev,UserRoleID AS AuthUserRoleID,
        CDB.ClientID,StartYear,
        Path as CurrentClientPath,UserPassword AS AuthUserPassword,
        Status AS AuthUserStatus,SignURL
        FROM AdminLoginDetails as LoginDtls
         left join AdminDepartments as Ad on Ad.DeptID=LoginDtls.DeptID
        left join AdminClientDbDetails as CDB on CDB.ClientID=LoginDtls.ClientID
        left join AdminRegisteredClients As ARC on ARC.ClientAccountID=LoginDtls.ClientID
         WHERE  UserLoginID=:LoginID";


        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':LoginID', $RequestBody['LoginID']);

        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


        $AryAccessIps = explode(',', $dataRow['AuthAccessIPs']);
        $LoginIP = $_SERVER['REMOTE_ADDR'];
        $dataRow['AuthAccessIPs'];
        $dataRow['AuthUserPassword'];


        if ($dataRow['AuthAccessIPs'] == '') {
            $Unauthrised = false;
        } else if (in_array($LoginIP, $AryAccessIps)) {
            $Unauthrised = false;
        } else if ($dataRow['AuthUserPassword'] != $RequestBody['Password']) {
            $Unauthrised = false;
        } else {
            $Unauthrised = true;
        }

        if (!$Unauthrised) {
            $authToken = encryptData($dataRow['AuthUserID']);
            $TokenData['UserID'] = $dataRow['AuthUserID'];
            $TokenData['ClientID'] = $dataRow['ClientID'];
            $TokenData['DeptID'] = $dataRow['DeptID'];
            $authToken = Token::getEncryptedToken($TokenData);
            $this->updateToken($dataRow['AuthUserID'], $authToken);
        }

        if ($dataRow['AuthUserPassword'] != $RequestBody['Password']) {
            $Data['Error'] = 'Incorrect Password';
            $Data['Msg'] = 'Incorrect Password';
        } else if ($Unauthrised) {
            $Data['Error'] = 'Unauthrised Access';
            $Data['Msg'] = 'Unauthrised Access';
        } else {
            $Data['Error'] = null;
            $Data['Msg'] = 'Login Successful';
            $Data['Token'] = $authToken;
            $Data['SessionTime'] = '20';
            $Data['TokenTime'] = '20';
            unset($dataRow['AuthUserPassword']);
            $Data['Data'] = $dataRow;
        }
        return $Data;
    }

    public function updateToken($authUserID, $authToken)
    {
        $existingUser = $this->auditLogger->fetchRowById('AuthLoginDetails', 'AuthUserID', $authUserID);

        $sqlQuery = "UPDATE AuthLoginDetails SET
                        LastLogin = :LastLogin,
                        LastAccess = :LastAccess,
                        AuthToken = :AuthToken
                    WHERE
                        AuthUserID = :AuthUserID";

        $stmt = $this->conn->prepare($sqlQuery);
        $LastLogin = getTimeStamp();
        $LastAccess = getTimeStamp();
        // bind data
        $stmt->bindParam(":LastLogin", $LastLogin);
        $stmt->bindParam(":LastAccess", $LastAccess);
        $stmt->bindParam(":AuthToken", $authToken);
        $stmt->bindParam(":AuthUserID", $authUserID);

        if ($stmt->execute()) {
            $updatedUser = $this->auditLogger->fetchRowById('AuthLoginDetails', 'AuthUserID', $authUserID);
            $this->auditLogger->logUpdate('AuthLoginDetails', $authUserID, $existingUser, $updatedUser, ['AuthUserID' => $authUserID], [
                'Remarks' => 'User token updated on login',
            ]);
            return true;
        } else {
            return false;
        }
    }










    public function updateEmployee()
    {
        $sqlQuery = "UPDATE
                        " . $this->db_table . "
                    SET
                        name = :name, 
                        email = :email, 
                        age = :age, 
                        designation = :designation, 
                        created = :created
                    WHERE 
                        id = :id";

        $stmt = $this->conn->prepare($sqlQuery);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->designation = htmlspecialchars(strip_tags($this->designation));
        $this->created = htmlspecialchars(strip_tags($this->created));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":designation", $this->designation);
        $stmt->bindParam(":created", $this->created);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    // DELETE
    function deleteEmployee()
    {
        $sqlQuery = "DELETE FROM " . $this->db_table . " WHERE id = ?";
        $stmt = $this->conn->prepare($sqlQuery);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
