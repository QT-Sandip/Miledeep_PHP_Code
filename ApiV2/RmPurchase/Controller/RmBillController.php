<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';
include_once __DIR__ . '/AuditLogger.php';
class RmBillController
{
    private $conn;
    private $db_table = "ScrmApplicants";
    private $utilController;
    private $auditLogger;
    // private $Util;
    private $Error = null;
    private $Msg = '';
    // Db connection
    public function __construct($db, $clientid)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn, $clientid);
        $this->appController = new AppUtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }

    public function getRmBillDetails($RequestBody)
    {
        $ArrivalID = $RequestBody['ArrivalID'];
        $BreakUpData = $this->getArrivalBreakUp($ArrivalID, $RequestBody);
        $ArrivalData = $this->getArrivalData($ArrivalID, $RequestBody);
        $GrandTotal = $this->getGrandTotal($ArrivalID);
        $BillerDetails = $this->getBillerDetails($ArrivalID);

        $Data['Error'] = null;
        $Data['Msg'] = 'Bill Details Posted Successfully';
        $Data['ArrivalData'] = $ArrivalData;
        $Data['BreakUpData'] = $BreakUpData;
        $Data['GrandTotal'] = $GrandTotal;
        $Data['BillerDetails'] = $BillerDetails;

        return $Data;
    }
    public function getBillerDetails($ArrivalID)
    {
        $SqlCheckupBrkUp = "select Name as CompanyName,LogoUrl,GSTNumber  from AdminClientDetails ";
        $stmtCheck = $this->conn->prepare($SqlCheckupBrkUp);
        $stmtCheck->execute();
        return $BillerDetails = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    }

    public function postRmBillDetails($RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        $AutoBillApproval = $this->appController->getClientConfig($ClientID)['AutoBillApproval'];
        $ArrivalID = $RequestBody['ArrivalID'];
        $Extra = $RequestBody['Extra'];
        $RmSupplierID = $RequestBody['RmSupplierID'];
        $AgentID = $RequestBody['AgentID'];
        $BillRemarks = $RequestBody['BillRemarks'];
        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $stmt = false;
        $PostedUserID = 1;
        $SqlCheckupBrkUp = "select ArrivalID  from RmBillDetails where ArrivalID='$ArrivalID'";
        $stmtCheck = $this->conn->prepare($SqlCheckupBrkUp);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() == 0 && $ArrivalID != '') {
            $sqlQuery = "INSERT INTO `RmBillDetails`
            (`ArrivalID`, `UserID`,`FinancierID`,`BillAgentID`, `CreatedDateTime`,`Deductions`,`Remarks`,`BillStatus`,`ApprovedBy`)
            VALUES ('$ArrivalID','$PostedUserID','$RmSupplierID','$AgentID','$CreatedDateTime','$Extra','$BillRemarks','$AutoBillApproval','$PostedUserID')";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            if ($stmt) {
                $newBillData = $this->auditLogger->fetchRowsByConditions('RmBillDetails', ['ArrivalID' => $ArrivalID]);
                $newBillData = !empty($newBillData) ? $newBillData[0] : null;
                $this->auditLogger->logInsert('RmBillDetails', $ArrivalID, $newBillData, $RequestBody, [
                    'ReferenceNo' => $ArrivalID,
                    'Remarks' => 'Bill created for arrival',
                ]);
            }

        } else if ($stmtCheck->rowCount() > 0 && $ArrivalID != '') {
            $SqlRmBillDetails = "update `RmBillDetails` set 
            `UserID`='$PostedUserID',
            `FinancierID`='$RmSupplierID', `BillAgentID`='$AgentID',
             `CreatedDateTime`='$CreatedDateTime',
             `Deductions`='$Extra',
             `Remarks`='$BillRemarks' where `ArrivalID`='$ArrivalID'";
            $stmt = $this->conn->prepare($SqlRmBillDetails);
            $stmt->execute();
            if ($stmt) {
                $updatedBillData = $this->auditLogger->fetchRowsByConditions('RmBillDetails', ['ArrivalID' => $ArrivalID]);
                $updatedBillData = !empty($updatedBillData) ? $updatedBillData[0] : null;
                $this->auditLogger->logUpdate('RmBillDetails', $ArrivalID, $existingBillData, $updatedBillData, $RequestBody, [
                    'ReferenceNo' => $ArrivalID,
                    'Remarks' => 'Bill updated for arrival',
                ]);
            }
        }
        if ($stmt) {
            if ($AgentID != 0) {
                $AgentPanNumber = $this->utilController->getAgentName($AgentID)['PanNumber'];
            } else {
                $AgentPanNumber = $this->utilController->getRMSuppliers($RmSupplierID)['PanNumber'];
            }
            $SupplierPanNumber = $this->utilController->getRMSuppliers($RmSupplierID)['PanNumber'];
            $SqlupdateRmPanWiseSupplier = "UPDATE RmPanWiseSuppliedAmount
            SET BillPanNumber = CASE 
                    WHEN `PanwiseArrivalID`='$ArrivalID' and `PanAmountType`='0' THEN '$SupplierPanNumber'
                    WHEN `PanwiseArrivalID`='$ArrivalID' and `PanAmountType`='1' THEN '$AgentPanNumber'
                END
            WHERE `PanwiseArrivalID`='$ArrivalID'";

            $stmtupdateRmPanWiseSupplier = $this->conn->prepare($SqlupdateRmPanWiseSupplier);
            $stmtupdateRmPanWiseSupplier->execute();

            $existingArrivalData = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
            $SqlRmArrivalDetails = "UPDATE `RmArrivalDetatils` SET `AgentID`='$AgentID',`RmSupplierID`='$RmSupplierID' 
            WHERE `RmArrivalID`='$ArrivalID'";
            $stmtRmArrivalDetails = $this->conn->prepare($SqlRmArrivalDetails);
            $stmtRmArrivalDetails->execute();
            if ($stmtRmArrivalDetails) {
                $updatedArrivalData = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
                $this->auditLogger->logUpdate('RmArrivalDetatils', $ArrivalID, $existingArrivalData, $updatedArrivalData, $RequestBody, [
                    'ReferenceNo' => isset($updatedArrivalData['ArrivalNumber']) ? $updatedArrivalData['ArrivalNumber'] : (string) $ArrivalID,
                    'Remarks' => 'Arrival supplier and agent updated during billing',
                ]);
            }
            $this->postArrivalBreakUp($ArrivalID, $RequestBody);
            $RequestBody['ArrivalID'] = $ArrivalID;

            return $this->getRmBillDetails($RequestBody);
        } else {
            return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        }
    }

    function postArrivalBreakUp($ArrivalID, $RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        $tdsLimit = 5000000;
        $AryGradingStatus = $this->utilController->getGradingStatus($ArrivalID);
        $GradingStatus = $AryGradingStatus['GradingAval'];
        $IsCalcVales = $AryGradingStatus['IsCalcVales'];

        $Extra = $RequestBody['Extra'];
        $existingArrivalHeader = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
        $SqlUpdateArrival = "UPDATE `RmArrivalDetatils` SET Extra='$Extra' where RmArrivalID=$ArrivalID";
        $StmtUpdateArrival = $this->conn->prepare($SqlUpdateArrival);
        $StmtUpdateArrival->execute();
        if ($StmtUpdateArrival) {
            $updatedArrivalHeader = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
            $this->auditLogger->logUpdate('RmArrivalDetatils', $ArrivalID, $existingArrivalHeader, $updatedArrivalHeader, $RequestBody, [
                'ReferenceNo' => isset($updatedArrivalHeader['ArrivalNumber']) ? $updatedArrivalHeader['ArrivalNumber'] : (string) $ArrivalID,
                'Remarks' => 'Arrival deductions updated during billing',
            ]);
        }

        $SqlArrival = "select  Species ,Billdate,ArrivalStationID from `RmArrivalDetatils`  where RmArrivalID=$ArrivalID";
        $StmtArrival = $this->conn->prepare($SqlArrival);
        $StmtArrival->execute();
        $ArrivaldataRow = $StmtArrival->fetch(PDO::FETCH_ASSOC);
        $Species = $ArrivaldataRow['Species'];
        $Billdate = $ArrivaldataRow['Billdate'];
        $CenterID = $ArrivaldataRow['ArrivalStationID'];

        $SqlRmBillBrkUp = "SELECT * FROM `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";
        $stmtBillBrkExec = $this->conn->prepare($SqlRmBillBrkUp);
        $stmtBillBrkExec->execute();
        $BillBreakup = $RequestBody['BillBreakup'];
        $BillBreakup = json_decode($BillBreakup, true);
        $index = 0;
        while ($dataRow = $stmtBillBrkExec->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalID = $dataRow['ArrivalID'];
            $BreakUpID = $dataRow['ArrivalBrkUpID'];
            $Description = $dataRow['Variety'];
            $Yield = 70.00;
            $Weight = $dataRow['Weight'];
            $Count = $dataRow['Count'];

            if ($IsCalcVales && $GradingStatus) {
                $AryCalcData = $this->utilController->getCalcWeight($BreakUpID);
                $Weight = $AryCalcData['HonWeight'];
                $Count = $AryCalcData['HonCount'];
                $MasterRate = $AryCalcData['BillRate'];
                $Yield = $AryCalcData['Yield'];
            }

            // $AryPriceData = $this->utilController->getRmPrice($Species, $Billdate, $Count);
            $AryPriceData = $this->utilController->getRmPrice($Species, $Billdate, $Count, $CenterID);
            $MasterRate = sprintf('%0.2f', isset($AryPriceData['RmPrice']) ? $AryPriceData['RmPrice'] : 0);
            $Rate = $BillBreakup[$index]['Rate'];

            $existingBreakUp = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $BreakUpID);
            $SqlUpdateArrival = "UPDATE `RmArrivalBreakUpDetails` SET Count=' $Count' , Weight='$Weight' ,Rate='$Rate' where ArrivalBrkUpID=$BreakUpID";
            $StmtUpdateArrival = $this->conn->prepare($SqlUpdateArrival);
            $StmtUpdateArrival->execute();
            if ($StmtUpdateArrival) {
                $updatedBreakUp = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $BreakUpID);
                $this->auditLogger->logUpdate('RmArrivalBreakUpDetails', $BreakUpID, $existingBreakUp, $updatedBreakUp, $RequestBody, [
                    'ReferenceNo' => $ArrivalID,
                    'Remarks' => 'Arrival breakup updated during billing',
                ]);
            }

            $SqlCheckupBrkUp = "select BreakUpID from RmBillBreakup where BreakUpID=$BreakUpID";
            $StmtCheckupBrkUp = $this->conn->prepare($SqlCheckupBrkUp);
            $StmtCheckupBrkUp->execute();
            $index++;
            $existingBillBreakup = $this->auditLogger->fetchRowsByConditions('RmBillBreakup', ['BreakUpID' => $BreakUpID]);
            $existingBillBreakup = !empty($existingBillBreakup) ? $existingBillBreakup[0] : null;
            if ($StmtCheckupBrkUp->rowCount() > 0) {
                $SqlRmBillBreakUp = "update  `RmBillBreakup` set
                             `Description`='$Description',
                             `Yield`='$Yield',
                             `Weight`='$Weight',
                             `MasterRate`='$MasterRate',
                             `Count`='$Count',
                             `Rate`='$Rate' where BreakUpID='$BreakUpID' ";
            } else {

                $SqlRmBillBreakUp = "INSERT INTO `RmBillBreakup`
                            (`ArrivalID`,`BreakUpID`,`Description`, `Yield`,`Weight`, `Count`,`MasterRate`, `Rate`) 
                        VALUES ('$ArrivalID','$BreakUpID','$Description','$Yield','$Weight','$Count','$MasterRate','$Rate')";
            }
            $StmtRmBillBreakUp = $this->conn->prepare($SqlRmBillBreakUp);
            $StmtRmBillBreakUp->execute();
            if ($StmtRmBillBreakUp) {
                $currentBillBreakup = $this->auditLogger->fetchRowsByConditions('RmBillBreakup', ['BreakUpID' => $BreakUpID]);
                $currentBillBreakup = !empty($currentBillBreakup) ? $currentBillBreakup[0] : null;
                if ($existingBillBreakup != null) {
                    $this->auditLogger->logUpdate('RmBillBreakup', $BreakUpID, $existingBillBreakup, $currentBillBreakup, $RequestBody, [
                        'ReferenceNo' => $ArrivalID,
                        'Remarks' => 'Bill breakup updated',
                    ]);
                } else {
                    $this->auditLogger->logInsert('RmBillBreakup', $BreakUpID, $currentBillBreakup, $RequestBody, [
                        'ReferenceNo' => $ArrivalID,
                        'Remarks' => 'Bill breakup created',
                    ]);
                }
            }
        }

        if ($StmtRmBillBreakUp) {

            $arrivalData['Billdate'] = $Billdate;
            $arrivalData['ArrivalID'] = $ArrivalID;

            $Billdate = new DateTime($Billdate);
            $TdsCalEndDate = new DateTime('2024-04-01');

            if ($Billdate < $TdsCalEndDate) {
                $tdsLimit = 0;
            }
            $AryupdateBillDetails = $this->utilController->updateBillTable($ArrivalID, $tdsLimit,$ClientID);//UMA 
            $AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);//uma 06/04/2024 approved by teja
            //$this->updateAutoOtherBills($arrivalData, $tdsLimit, $ClientID);

        }
    }
    function updateAutoOtherBills($arrivalData, $tdsLimit, $ClientID)
    {

        $isEditBill = false;

        $ArrivalID = $arrivalData['ArrivalID'];
        $Billdate = $arrivalData['Billdate'];

        $sqlSupplierDetails = "SELECT GROUP_CONCAT(ID) as supplierIDs,PanNumber from RmSuppliers 
        WHERE PanNumber in (SELECT RS.PanNumber from RmArrivalDetatils as RAD 
        left Join RmSuppliers AS RS on RAD.RmSupplierID=RS.ID WHERE RAD.RmArrivalID=$ArrivalID)";
        $stmtSupplierDetails = $this->conn->prepare($sqlSupplierDetails);
        $stmtSupplierDetails->execute();
        $dataSupplierDetails = $stmtSupplierDetails->fetch(PDO::FETCH_ASSOC);
        $supplierIDs = $dataSupplierDetails['supplierIDs'];
        $checkPAN = $dataSupplierDetails['PanNumber'];

        $dataRequest['checkPAN'] = $checkPAN;
        $dataRequest['ArrivalID'] = $ArrivalID;
        $dataRequest['supplierIDs'] = $supplierIDs;
        $dataRequest['tdsLimit'] = $tdsLimit;
        $dataRequest['isEditBill'] = $isEditBill;
        $dataRequest['BillDate'] = $Billdate;

        $aryArrivalUnderTdsLimit = $this->utilController->checkArrivalUnderTdsLimit($dataRequest);
        $isUnderTdsBill = $aryArrivalUnderTdsLimit['isUnderTdsLimit'];
        $aryArrivalID = $aryArrivalUnderTdsLimit['aryArrivalID'];

        foreach ($aryArrivalID as $ArrivalID) {
            $AryupdateBillDetails = $this->utilController->updateBillTable($ArrivalID, $tdsLimit,$ClientID);//UMA 
            $AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);//uma 06/04/2024 approved by teja   
        }

        $dataRequest['limitRecords'] = false; //give Single Record
        $aryIsTdsExcessBill = $this->utilController->isTdsLimitExceedBill($dataRequest);
        $isTdsLimitExceeBill = $aryIsTdsExcessBill['isTdsLimitExceeBill'];
        $aryArrivalID = $aryIsTdsExcessBill['aryArrivalID'];

        foreach ($aryArrivalID as $ArrivalID) {
            $AryupdateBillDetails = $this->utilController->updateBillTable($ArrivalID, $tdsLimit,$ClientID);//UMA 
            $AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);//uma 06/04/2024 approved by teja   
        }

        $dataRequest['limitRecords'] = true; // Will Give Multiple Except 50LRecord
        $aryIsTdsExcessBill = $this->utilController->isTdsLimitExceedBill($dataRequest);
        $isTdsLimitExceeBill = $aryIsTdsExcessBill['isTdsLimitExceeBill'];
        $aryArrivalID = $aryIsTdsExcessBill['aryArrivalID'];

        foreach ($aryArrivalID as $ArrivalID) {

            $AryupdateBillDetails = $this->utilController->updateBillTable($ArrivalID, $tdsLimit,$ClientID);//UMA 
            $AryupdateArrivalBillAmountandTds = $this->utilController->updateArrivalBillAmountandTds($ArrivalID);//uma 06/04/2024 approved by teja   
        }

    }
    function getArrivalData($ArrivalID, $RequestBody)
    {
        $SqlCheckupBrkUp = "SELECT * FROM `RmPaymentBreakup` WHERE ArrivalID=$ArrivalID";
        $StmtCheckupBrkUp = $this->conn->prepare($SqlCheckupBrkUp);
        $StmtCheckupBrkUp->execute();

        if ($StmtCheckupBrkUp->rowCount() > 0) {
            $PaymentStatus = true;
        } else {
            $PaymentStatus = false;
        }
        $dataRow = null;
        $sqlFields = " `ArrivalStationID`,`Species` `ProcessingStationID`,`VehicalID`, `AgentID`, `DriverID`, `GraderID`,
         `PurchaseCategoryID`, `FarmAddress`, `CreatedDateTime`, `ArrivalDate`,`BillDate`, `IsEdited`, `LotStatus`";
        $sqlQuery = "SELECT  * FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $CenterID = $dataRow['ArrivalStationID'];
        $ProcessingStationID = $dataRow['ProcessingStationID'];
        $ProcessingStationID = $dataRow['ProcessingStationID'];
        $RmCategoryID = $dataRow['PurchaseCategoryID'];
        $VehicalID = $dataRow['VehicalID'];
        $AgentID = $dataRow['AgentID'];
        $DriverID = $dataRow['DriverID'];
        $GraderID = $dataRow['GraderID'];
        $RmSupplierID = $dataRow['RmSupplierID'];
        $Extra = $dataRow['Extra'];
        $FarmAddress = $dataRow['FarmAddress'];
        $ArrivalDate = $dataRow['ArrivalDate'];
        $BillDate = $dataRow['BillDate'];
        $Remarks = $dataRow['Remarks'];
        $ArrivalNumber = $dataRow['ArrivalNumber'];
        $Species = $dataRow['Species'];
        $PurchaseCategoryID = $dataRow['PurchaseCategoryID'];
        $ClientID = $RequestBody['ClientID'];

        $AutoArrivalPrice = $this->appController->getClientConfig($ClientID)['AutoArrivalPrice'];
        $AryPurchaseStation = $this->utilController->getRmPurchaseCenters($CenterID);
        $AryProcessingCenter = $this->utilController->getRmProcessingCenters($ProcessingStationID);
        $AryPurchaseCategory = $this->utilController->getPurchaseCategory($RmCategoryID);
        $AryVehiclesDetails = $this->utilController->getVehiclesDetails($VehicalID);
        $AryAgent = $this->utilController->getUsers($AgentID);
        $AryDriver = $this->utilController->getUsers($DriverID);
        $AryGrader = $this->utilController->getUsers($GraderID);
        $ArySupplier = $this->utilController->getRMSuppliers($RmSupplierID);
        $AryBillData = $this->getBillData($ArrivalID);

        $rowData['PurchaseCategoryID'] = $PurchaseCategoryID;
        $rowData['PurchaseStation'] = $AryPurchaseStation['StationName'];
        $rowData['ProcessingStation'] = $AryProcessingCenter['StationName'];
        $rowData['PurchaseType'] = $AryPurchaseCategory['RmCategoryName'];
        $rowData['VehicalNumber'] = $AryVehiclesDetails['VehicalNumber'];
        $rowData['AgentName'] = $AryAgent['UserName'];
        $rowData['DriverName'] = $AryDriver['UserName'];
        $rowData['GraderName'] = $AryGrader['UserName'];
        $rowData['BillName'] = 'NA';
        if ($AryAgent['UserName'] == '') {
            $rowData['BillName'] = isset($ArySupplier['SupplierName']) ? $ArySupplier['SupplierName'] : null;
            $rowData['AgentPanNumber'] = $ArySupplier['PanNumber'];
        } else {
            $rowData['BillName'] = $AryAgent['UserName'];
            $rowData['AgentPanNumber'] = $AryAgent['PanNumber'];
        }
        $rowData['SupplierName'] = isset($ArySupplier['SupplierName']) ? $ArySupplier['SupplierName'] : null;
        $rowData['SupplierAddress'] = isset($ArySupplier['SupplierAddress']) ? $ArySupplier['SupplierAddress'] : null;
        $rowData['GSTNumber'] = $ArySupplier['GstNo'];
        $rowData['PanNumber'] = $ArySupplier['PanNumber'];

        $rowData['PhoneNumber'] = $ArySupplier['PhoneNumber'];
        $rowData['ArrivalNumber'] = $ArrivalNumber;
        $rowData['Extra'] = $Extra;
        $rowData['FarmAddress'] = $FarmAddress;
        $rowData['ArrivalDate'] = $ArrivalDate;
        $rowData['PurchaseRemarks'] = $Remarks;
        $rowData['BillRemarks'] = '';
        $rowData['BillDate'] = $this->utilController->getFormatedDate($BillDate);

        $rowData['Species'] = $Species;
        $rowData['RmSupplierID'] = $RmSupplierID;
        $rowData['AgentID'] = $AgentID;
        $rowData['CenterID'] = $CenterID;
        $rowData['BillStatus'] = 1;
        $AryGradingStatus = $this->utilController->getGradingStatus($ArrivalID);

        $GradingStatus = $AryGradingStatus['GradingAval'];
        $IsCalcVales = $AryGradingStatus['IsCalcVales'];

        if ($GradingStatus && $IsCalcVales) {
            $rowData['Editable'] = 1;
        } else if (!$IsCalcVales) {
            $rowData['Editable'] = 1;
        } else {
            $rowData['Editable'] = 0;
        }
        if ($PaymentStatus) {
            $rowData['Editable'] = 0;
        }
        if ($AutoArrivalPrice == 1) {
            $rowData['PriceEditable'] = 0;
        } else {
            $rowData['PriceEditable'] = 1;
        }

        if ($AryBillData != null) {
            $rowData['BillRemarks'] = $AryBillData['Remarks'];
            $rowData['Extra'] = $AryBillData['Deductions'];
        }
        return $rowData;
    }

    function getArrivalBreakUp($ArrivalID, $RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        $AutoArrivalPrice = $this->appController->getClientConfig($ClientID)['AutoArrivalPrice'];
        $AryGradingStatus = $this->utilController->getGradingStatus($ArrivalID);
        $GradingStatus = $AryGradingStatus['GradingAval'];
        $IsCalcVales = $AryGradingStatus['IsCalcVales'];
        $DefComission = $AryGradingStatus['DefComission'];

        $sqlQuery = "SELECT  HarvestCommission,BillDate FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $HarvestCommission = $dataRow['HarvestCommission'];
        $BillDate = $dataRow['BillDate'];

        $ArrivalData = $this->getArrivalData($ArrivalID, $RequestBody);
        $Species = $ArrivalData['Species'];
        $ArrivalDate = $ArrivalData['ArrivalDate'];
        $ArrivalDate = $ArrivalData['ArrivalDate'];
        $PurchaseCategoryID = $ArrivalData['PurchaseCategoryID'];
        $CenterID = $ArrivalData['CenterID'];

        $sqlArrivalWeightQuery = "select SUM(Weight) AS OverAllWeight  from `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";
        $stmtArrivalWeight = $this->conn->prepare($sqlArrivalWeightQuery);
        $stmtArrivalWeight->execute();
        $dataRow = $stmtArrivalWeight->fetch(PDO::FETCH_ASSOC);

        $TotalArrivalWeight = $dataRow['OverAllWeight'];



        $Data = null;
        $sqlFields = "
        RAB.ArrivalBrkUpID,         
 RAB.ArrivalID, 
 RAB.Variety as AVariety,
 RAB.Count as ACount, 
 RAB.Weight as AWeight,
 RAB.Rate as ARate,
 RAB.BrkUpStatus , 
  RBB.Description as BVariety,
 RBB.Count as BCount, 
 RBB.Weight as BWeight,
 RBB.Rate as BRate";

        $sqlQuery = "select $sqlFields  from `RmArrivalBreakUpDetails` as RAB
 left join RmBillBreakup as RBB on RBB.BreakUpID=RAB.ArrivalBrkUpID where RAB.ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $BreakUpData['ArrivalID'] = $dataRow['ArrivalID'];
            $BreakUpData['ArrivalBrkUpID'] = $dataRow['ArrivalBrkUpID'];
            $BreakUpData['BrkUpStatus'] = $dataRow['BrkUpStatus'];

            if ($IsCalcVales && $GradingStatus) {
                $AryCalcData = $this->utilController->getCalcWeight($BreakUpData['ArrivalBrkUpID']);
                $dataRow['BWeight'] = $AryCalcData['HonWeight'];
                $dataRow['BCount'] = $AryCalcData['HonCount'];
                $dataRow['BRate'] = $AryCalcData['BillRate'];
                $Yield = $AryCalcData['Yield'];
            }

            $Weight = isset($dataRow['BWeight']) ? $dataRow['BWeight'] : $dataRow['AWeight'];
            $Count = isset($dataRow['BCount']) ? $dataRow['BCount'] : $dataRow['ACount'];
            $Rate = isset($dataRow['BRate']) ? $dataRow['BRate'] : $dataRow['ARate'];
            $Variety = isset($dataRow['BVariety']) ? $dataRow['BVariety'] : $dataRow['AVariety'];
            $BreakUpData['Weight'] = sprintf('%0.3f', round($Weight, 3));
            $BreakUpData['Count'] = sprintf('%0.2f', round($Count, 2));
            $BreakUpData['Rate'] = sprintf('%0.2f', round($Rate, 2));
            // $AryPriceData = $this->utilController->getRmPrice($Species, $ArrivalDate, $Count);
            $AryPriceData = $this->utilController->getRmPrice($Species, $BillDate, $Count, $CenterID); //uma 20-06-2024
           

            $BreakUpData['MasterRate'] = isset($AryPriceData['RmPrice']) ? $AryPriceData['RmPrice'] : 0;
            if ($AutoArrivalPrice == 1) {
                $DeductionPrice = $this->utilController->getRmDeductionPrice($Species, $PurchaseCategoryID, $CenterID, $BillDate, $Count, $TotalArrivalWeight); //uma 10-07-2024 // sriram 20-03-25
                if ($Variety != 'HEAD ON' && $Variety != 'Domestic') {
                    $BreakUpData['MasterRate'] = $this->utilController->getDefectPrice($Species, $Variety, $BreakUpData['MasterRate']);
                    if ($BillDate >= '2026-06-16') {
                        $DeductionPrice = 0;
                    }
                }
                // $BreakUpData['Rate'] = $BreakUpData['MasterRate'] + $DefComission;
                $BreakUpData['Rate'] = $BreakUpData['MasterRate'] + $DefComission + ($DeductionPrice);
            }
            if ($Variety == 'HEAD ON') {
                $Variety = 'HO-' . $Species;
            } else {
                $Variety = 'HO-' . $Variety . '-' . $Species;
            }
            $BreakUpData['Variety'] = $Variety;
            $BreakUpData['BreakupTotal'] = sprintf('%0.2f', round($Weight * $Rate, 2));
            $BreakUpData['HarvestCommission'] = round($Weight * $HarvestCommission, 2);
            $BreakUpData['HarvestCommissionKg'] = round($HarvestCommission, 2);
            $BreakUpData['BreakupTotalIncHC'] = sprintf('%0.2f', $BreakUpData['BreakupTotal'] + round($Weight * $HarvestCommission, 2));
            $Data[] = $BreakUpData;
        }
        return $Data;
    }

    function getGrandTotal($ArrivalID)
    {
        $sqlQuery = "SELECT  HarvestCommission FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $HarvestCommission = $dataRow['HarvestCommission'];
        $Data = array();
        $sqlFields = "sum(RAB.Weight) as AWeightTotal ,sum(RAB.Weight*RAB.Rate) as AValueTotal,
                    sum(RBB.Weight) as BWeightTotal ,sum(RBB.Weight*RBB.Rate) as BValueTotal";
        $sqlQuery = "select $sqlFields from `RmArrivalBreakUpDetails` as RAB
            left join RmBillBreakup as RBB on RBB.BreakUpID=RAB.ArrivalBrkUpID 
            where RAB.ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $Extras = $this->getArrivalData($ArrivalID);
        $dataRow['ValueTotal'] = sprintf('%0.3f', isset($row['BValueTotal']) ? $row['BValueTotal'] : $row['AValueTotal']);
        $dataRow['WeightTotal'] = sprintf('%0.3f', isset($row['BWeightTotal']) ? $row['BWeightTotal'] : $row['AWeightTotal']);
        $dataRow['HarvestExpanses'] = sprintf('%0.2f', $HarvestCommission * $dataRow['WeightTotal']);
        $dataRow['ValueTotal'] = sprintf('%0.2f', $dataRow['ValueTotal']);
        $dataRow['ValueIncTdsTotal'] = sprintf('%0.2f', $dataRow['ValueTotal'] + $dataRow['HarvestExpanses']);
        $dataRow['HarvestExpansesCommision'] = sprintf('%0.2f', $this->getBillData($ArrivalID)['HarvestCommision']);
        $dataRow['HarvestExpansesTds'] = sprintf('%0.2f', $this->getBillData($ArrivalID)['HarvestTds']);//uma
        $dataRow['ValueTotalExTds'] = sprintf('%0.2f', $this->getBillData($ArrivalID)['BillTds']); //uma
        $dataRow['HarvestExpAfterTds'] = sprintf('%0.2f', $dataRow['HarvestExpanses'] - $dataRow['HarvestExpansesTds']);
        $dataRow['HarvestExpAfterCommision'] = sprintf('%0.2f', $dataRow['HarvestExpanses'] - $dataRow['HarvestExpansesCommision']);
        $dataRow['TdsValue'] = sprintf('%0.2f', $dataRow['ValueTotal'] - $dataRow['ValueTotalExTds']);
        $dataRow['AmountInWords'] = ConvertNumbersToINR($dataRow['TdsValue'], "RUPEES", "PAISE");
        $dataRow['HarvestExpAmountInWords'] = ConvertNumbersToINR($dataRow['HarvestExpAfterTds'], "RUPEES", "PAISE");
        return $dataRow;
    }
    function getTdsValues($ValueTotal, $TdsPerc)
    {
        $ValueTotalExTds = ($ValueTotal * $TdsPerc) / 100;
        $ValueTotalExTds = round($ValueTotalExTds, 2);
        $decimal = fmod($ValueTotalExTds, 1); //0.25
        $AryValueTds = explode('.', $ValueTotalExTds);
        if (count($AryValueTds) > 1) {
            $postFix = $AryValueTds[1];
            if ($decimal > 0.10) {
                $ValueTotalExTds = ceil($ValueTotalExTds);
            } else {
                $ValueTotalExTds = $AryValueTds[0];
            }
        }
        return $ValueTotalExTds;
    }
    function getBillData($ArrivalID)
    {
        $dataRow = null;
        $SqlCheckupBrkUp = "select Deductions,Remarks,BillStatus,BillTds,HarvestTds,HarvestCommision  from RmBillDetails where ArrivalID='$ArrivalID'";
        $stmtCheck = $this->conn->prepare($SqlCheckupBrkUp);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() > 0) {
            $dataRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        }

        return $dataRow;
    }

}
?>
