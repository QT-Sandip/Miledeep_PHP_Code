<?php



include_once '../Global/Util.php';

include_once 'UtilController.php';
include_once __DIR__ . '/AuditLogger.php';



class RmSupplierController
{



    private $conn;

    private $db_table = "RmSuppliers";

    private $utilController;
    private $auditLogger;

    private $Error = null;

    private $Msg = null;



    // Db connection

    public function __construct($db)
    {

        $this->conn = $db;

        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');

    }

    public function postSupplierDetails($RequestBody)
    {

        $SupplierID = null;

        if (isset($RequestBody['SupplierID']) && !empty($RequestBody['SupplierID'])) {

            $SupplierID = $RequestBody['SupplierID'];

        }

        $SupplierType = $RequestBody['SupplierType'];

        $SupplierName = $RequestBody['SupplierName'];

        $PANNumber = $RequestBody['PANNumber'];

        $Address = $RequestBody['Address'];

        $EMailID = $RequestBody['EMailID'];

        $SupplierBank = $RequestBody['SupplierBank'];

        $BankAccNo = $RequestBody['BankAccNo'];

        $IFSCCode = $RequestBody['IFSCCode'];

        $GSTNumber = $RequestBody['GSTNumber'];

        $PhoneNumber = $RequestBody['PhoneNumber'];

        $ClientID = $RequestBody['ClientId']; //Mohan 20-11-2025 for getting client config

        if (isset($RequestBody['PurchaseCenter']) && !empty($RequestBody['PurchaseCenter'])) {

            $PurchaseCenter = $RequestBody['PurchaseCenter'];

        }

        if (empty($SupplierID)) {
            $existingPAN = 0;
            $RestrictDuplicatePan = $this->utilController->getClientConfig($ClientID)['RestrictDuplicatePan'];
            if ($RestrictDuplicatePan == 1) {
                $existingPAN = $this->checkDuplicatePanNumber($PANNumber, $SupplierType);
            } else {
                $existingPAN = $this->checkDuplicatePanNumberSimple($PANNumber, $SupplierType);
            }

            //$existingGST = $this->checkDuplicateGSTNumber($GSTNumber);



            if ($existingPAN) {

                $this->Error = "PAN Number already exists.";

            }

            /*if ($existingGST) {

                $this->Error = ($this->Error ? $this->Error . " " : "") . "GST Number already exists.";

            }*/



            if ($this->Error) {

                $response['Error'] = $this->Error;

                $response['Data'] = null;

                return $response;

            }

        }

        if ($SupplierID) {
            $existingSupplierData = $this->auditLogger->fetchRowById('RmSuppliers', 'ID', $SupplierID);

            $sqlBanksQuery = "UPDATE RmSuppliers SET SupplierType='$SupplierType',FinancierName='$SupplierName',PanNumber='$PANNumber',

            Address='$Address',PurchaseCenter='$PurchaseCenter',BankName='$SupplierBank',BankAccNo='$BankAccNo',

            IFSC='$IFSCCode',GstNo='$GSTNumber',PhoneNumber='$PhoneNumber',EMailID='$EMailID' WHERE ID=$SupplierID";

        } else {

            $SqlFields = "`SupplierType`, `FinancierName`,`PanNumber`, `Address` ,`PurchaseCenter`,  `BankName`, `BankAccNo`,  `IFSC`,`GstNo`,`PhoneNumber`,`EMailID`,`Status`";

            $sqlBanksQuery = "INSERT INTO RmSuppliers ($SqlFields)

            VALUES ('$SupplierType','$SupplierName','$PANNumber','$Address','$PurchaseCenter','$SupplierBank','$BankAccNo','$IFSCCode','$GSTNumber','$PhoneNumber','$EMailID',1)";

        }



        $stmt = $this->conn->prepare($sqlBanksQuery);

        $stmt->execute();

        $Data = null;

        $response = null;

        if ($stmt) {



            if ($SupplierID == null) {

                $SupplierID = $this->conn->lastInsertId();

            }





            $SqlFields = "`ID` as SupplierID,`SupplierType`, `FinancierName`,`PanNumber`, `Address` ,`PurchaseCenter`,  `BankName`, `BankAccNo`,  `IFSC`,`GstNo`,`PhoneNumber`,`EMailID`,`Status`";

            $sqlSupplierQuery = "select $SqlFields from   RmSuppliers where ID='$SupplierID'";

            $stmtSupplier = $this->conn->prepare($sqlSupplierQuery);

            $stmtSupplier->execute();



            $dataRow = $stmtSupplier->fetch(PDO::FETCH_ASSOC);
            if ($SupplierID != null && isset($existingSupplierData)) {
                $this->auditLogger->logUpdate('RmSuppliers', $SupplierID, $existingSupplierData, $dataRow, $RequestBody, [
                    'ReferenceNo' => $SupplierName,
                    'Remarks' => 'Supplier updated',
                ]);
            } else {
                $this->auditLogger->logInsert('RmSuppliers', $SupplierID, $dataRow, $RequestBody, [
                    'ReferenceNo' => $SupplierName,
                    'Remarks' => 'Supplier created',
                ]);
            }

            $this->Msg = "Success";

            // $this->Msg=$sqlBanksQuery;

        } else {

            $this->Msg = "Unable To Save Data";

        }



        $response['Error'] = $this->Error;

        $response['Msg'] = $this->Msg;

        $response['Data'] = $dataRow;

        return $response;

    }



    public function checkDuplicatePanNumberSimple($PanNumber, $SupplierType)
    {

        $sqlCheck = "SELECT ID FROM RmSuppliers WHERE PanNumber = :PanNumber";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingRecord;

    }
    public function checkDuplicatePanNumber($PanNumber, $SupplierType)
    {
        $SupplierType = strtoupper($SupplierType);

        $sqlCheck = "SELECT ID 
                    FROM RmSuppliers 
                    WHERE PanNumber = :PanNumber 
                      AND SupplierType = 'BUSINESS'";

        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->execute();
        $businessFound = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($businessFound) {
            return $businessFound;
        }

        if ($SupplierType === 'BUSINESS') {

            $sqlCheck = "SELECT ID 
                FROM RmSuppliers 
                WHERE PanNumber = :PanNumber";

            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
            $stmtCheck->execute();

            return $stmtCheck->fetch(PDO::FETCH_ASSOC);
        }

        $sqlCheck = "SELECT ID 
            FROM RmSuppliers 
            WHERE PanNumber = :PanNumber
              AND SupplierType = :SupplierType";

        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->bindParam(':SupplierType', $SupplierType, PDO::PARAM_STR);
        $stmtCheck->execute();

        return $stmtCheck->fetch(PDO::FETCH_ASSOC);
    }




    public function checkDuplicateGSTNumber($GstNo)
    {

        $sqlCheck = "SELECT ID FROM RmSuppliers WHERE GstNo = :GstNo";

        $stmtCheck = $this->conn->prepare($sqlCheck);

        $stmtCheck->bindParam(':GstNo', $GstNo, PDO::PARAM_STR);

        $stmtCheck->execute();



        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);



        return $existingRecord;

    }







    // Full texts

    // ID 

    // SupplierType 

    // FinancierName 

    // Address 

    // PurchaseCenter 

    // BankName 

    // BankAccNo 

    // GstNo 

    // IFSC 

    // PanNumber 

    // EMailID 

    // PhoneNumber 

    // Status



}





