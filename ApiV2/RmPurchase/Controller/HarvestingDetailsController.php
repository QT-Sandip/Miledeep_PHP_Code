<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class HarvestingDetailsController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $auditLogger;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }

    public function postHarvestingDetails($RequestBody)
    {
        $HarvestFormNewDesignForSME = !empty($RequestBody["HarvestFormNewDesignForSME"])?$RequestBody["HarvestFormNewDesignForSME"]:'';//SMEV3
        $HarvestingID = $RequestBody['HarvestingID'];
        $BookingID = $RequestBody['BookingID'];
        $BookingDate = $RequestBody['BookingDate'];
        $SupervisorORGrader = $RequestBody['SupervisorORGrader'];
        $FarmerName = $RequestBody['FarmerName'];
        $PhoneNumber = $RequestBody['PhoneNumber'];
        $SealNumber = $RequestBody['SealNumber'];
        $BoysGroupID = $RequestBody['BoysGroupID'];
        $WorkingBoys = $RequestBody['WorkingBoys'];
        $MTCrates = $RequestBody['MTCrates'];
        $Nets = $RequestBody['Nets'];
        $Savels = $RequestBody['Savels'];
        $WeightStones = $RequestBody['WeightStones'];
        $WeightMc = $RequestBody['WeightMc'];
        $WaterTin = $RequestBody['WaterTin'];
        $Remarks = $RequestBody['Remarks'];
        $DefectRemarks = $RequestBody['DefectRemarks'];

        $MTCrateWeight = $RequestBody['MTCrateWeight'];
        $CheckingCount = $RequestBody['CheckingCount'];
        $CheckingQty = $RequestBody['CheckingQty'];
        $MaterialTotalQuantity = $RequestBody['MaterialTotalQuantity'];
        $MaterialTotalCrates = $RequestBody['MaterialTotalCrates'];
        $NumberOfPieces = $RequestBody['NumberOfPieces'];

        $HarvestDate = $RequestBody['HarvestDate'];
        $CheckingCrate = $RequestBody['CheckingCrate'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $HarvestingBreakupID = $RequestBody['HarvestingBreakupID'];
        $insertColmn = $insertVal = $updateQry = '';
		if($HarvestFormNewDesignForSME == 1){
            $HarvestStartingTime = $RequestBody['HarvestStartingTime'];
            $HarvestEndingTime = $RequestBody['HarvestEndingTime'];
            $IceQtyInCrates = $RequestBody['IceQtyInCrates'];
            $ReturnIceQtyInCrates = $RequestBody['ReturnIceQtyInCrates'];
			$insertColmn = ',HarvestStartingTime,HarvestEndingTime,IceQtyInCrates,ReturnIceQtyInCrates';
			$insertVal = ",'$HarvestStartingTime','$HarvestEndingTime','$IceQtyInCrates','$ReturnIceQtyInCrates'";
			$updateQry = ",`HarvestStartingTime`='$HarvestStartingTime',`HarvestEndingTime`='$HarvestEndingTime'
                            ,`IceQtyInCrates`='$IceQtyInCrates',`ReturnIceQtyInCrates`='$ReturnIceQtyInCrates'";
		}

        $SupervisorORGraders = trim($SupervisorORGrader);

        if ($HarvestingID == '') {
            $sqlHarvestInsert = "INSERT INTO `RmHarvestingDetails`( `BookingID`, `BookingDate`, `SupervisorORGrader`, `FarmerName`, 
            `PhoneNumber`, `SealNumber`, `BoysGroupID`, `WorkingBoys`, `MTCrates`, `Nets`, `Savels`, `WeightStones`, `WeightMc`, 
            `WaterTin`, `Remarks`, `MTCrateWeight`, `CheckingQty`, `CheckingCount`, `MaterialTotalQuantity`, `MaterialTotalCrates`, 
            `PostedDatetime`, `PostedUserID`, `HarvestDate`, `CheckingCrate`, `NumberOfPieces`,`DefectRemarks` $insertColmn) 
            VALUES ('$BookingID','$BookingDate','$SupervisorORGraders','$FarmerName','$PhoneNumber','$SealNumber','$BoysGroupID',
            '$WorkingBoys','$MTCrates','$Nets','$Savels','$WeightStones','$WeightMc','$WaterTin','$Remarks', '$MTCrateWeight',
            '$CheckingQty','$CheckingCount','$MaterialTotalQuantity','$MaterialTotalCrates','$PostedDate',1, '$HarvestDate', '$CheckingCrate','$NumberOfPieces','$DefectRemarks' $insertVal)";

            $stmtHarvestInsert = $this->conn->prepare($sqlHarvestInsert);
            $stmtHarvestInsert->execute();
            $HarvestID = $this->conn->lastInsertId();

            if ($stmtHarvestInsert) {
                $newHarvest = $this->auditLogger->fetchRowById('RmHarvestingDetails', 'HarvestingID', $HarvestID);
                $this->auditLogger->logInsert('RmHarvestingDetails', $HarvestID, $newHarvest, $RequestBody, [
                    'ReferenceNo' => $BookingID,
                    'Remarks' => 'Harvesting created',
                ]);
                if($HarvestFormNewDesignForSME == 1 && !empty($RequestBody['BreakUpData'])){
                    $this->postQtyBreakUpDetails($HarvestID, $RequestBody['BreakUpData'], $RequestBody['UserId'], $RequestBody);
                }
                $this->postBreakUpDetails($HarvestID, $RequestBody);
            }
        } else {
            $existingHarvest = $this->auditLogger->fetchRowById('RmHarvestingDetails', 'HarvestingID', $HarvestingID);
            $sqlHarvestUpdate = "UPDATE `RmHarvestingDetails` SET `BookingID`='$BookingID',`BookingDate`='$BookingDate',
            `SupervisorORGrader`='$SupervisorORGraders',`FarmerName`='$FarmerName',`PhoneNumber`='$PhoneNumber',`SealNumber`='$SealNumber',
            `BoysGroupID`='$BoysGroupID',`WorkingBoys`='$WorkingBoys',`MTCrates`='$MTCrates',`Nets`='$Nets',`Savels`='$Savels',
            `WeightStones`='$WeightStones',`WeightMc`='$WeightMc',`WaterTin`='$WaterTin',`Remarks`='$Remarks',`MTCrateWeight`='$MTCrateWeight',
            `CheckingQty`='$CheckingQty',`CheckingCount`='$CheckingCount',
            `MaterialTotalQuantity`='$MaterialTotalQuantity',`MaterialTotalCrates`='$MaterialTotalCrates',`PostedDatetime`='$PostedDate',
            `PostedUserID`= 1, `HarvestDate`='$HarvestDate', `CheckingCrate`='$CheckingCrate', `NumberOfPieces`='$NumberOfPieces' , `DefectRemarks`='$DefectRemarks'
             $updateQry WHERE `HarvestingID`='$HarvestingID'";

            $stmtHarvestUpdate = $this->conn->prepare($sqlHarvestUpdate);
            $stmtHarvestUpdate->execute();
            $HarvestID = $HarvestingID;

            if ($stmtHarvestUpdate) {
                $updatedHarvest = $this->auditLogger->fetchRowById('RmHarvestingDetails', 'HarvestingID', $HarvestingID);
                $this->auditLogger->logUpdate('RmHarvestingDetails', $HarvestingID, $existingHarvest, $updatedHarvest, $RequestBody, [
                    'ReferenceNo' => $BookingID,
                    'Remarks' => 'Harvesting updated',
                ]);
                if($HarvestFormNewDesignForSME == 1 && !empty($RequestBody['BreakUpData'])){
                    $this->postQtyBreakUpDetails($HarvestingID, $RequestBody['BreakUpData'], $RequestBody['UserId'], $RequestBody);
                }
                $this->postBreakUpDetails($HarvestingID, $RequestBody);
            }
        }

        $getData = $this->getHarvestingDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = 'Success';
        $Data['HarvestID'] = $HarvestID;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }

    function postBreakUpDetails($HarvestingID, $RequestBody)
    {
        $HarvestingBreakupID = $RequestBody['HarvestingBreakupID'];
        $Variety = $RequestBody['Variety'];
        $Weight = $RequestBody['Weight'];
        $NoofCrates = $RequestBody['NoofCrates'];
        $Count = $RequestBody['Count'];
        // $CrateNumber = $RequestBody['CrateNumber'];

        $SqlCrateCount="SELECT Count(*) as CrateNumberCount FROM `RmHarvestingBreakupDetails` WHERE HarvestingID='$HarvestingID'";  
       $stmtCrateCount= $this->conn->prepare($SqlCrateCount);
       $stmtCrateCount->execute();
       $dataRowCrateCount = $stmtCrateCount->fetch(PDO::FETCH_ASSOC);
       $CrateNumberCount=$dataRowCrateCount['CrateNumberCount'];
         $CrateCount=$CrateNumberCount+1; 

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        if ($Variety || $Weight || $NoofCrates || $Count) {
            if ($HarvestingBreakupID == '') {
                $sqlBreakUpInsert = "INSERT INTO `RmHarvestingBreakupDetails`(`HarvestingID`,`CrateNumber`, `Variety`, 
            `Weight`, `NoofCrates`, `Count`, `PostedDatetime`, `PostedUserID`) 
            VALUES ('$HarvestingID','$CrateCount','$Variety','$Weight','$NoofCrates','$Count','$PostedDate',1)";
                $stmtBreakUpInsert = $this->conn->prepare($sqlBreakUpInsert);
                $stmtBreakUpInsert->execute();
                if ($stmtBreakUpInsert) {
                    $newBreakupId = $this->conn->lastInsertId();
                    $newBreakup = $this->auditLogger->fetchRowById('RmHarvestingBreakupDetails', 'HarvestingBreakupID', $newBreakupId);
                    $this->auditLogger->logInsert('RmHarvestingBreakupDetails', $newBreakupId, $newBreakup, $RequestBody, [
                        'ReferenceNo' => $HarvestingID,
                        'Remarks' => 'Harvesting breakup created',
                    ]);
                }
            } else {
                $existingBreakup = $this->auditLogger->fetchRowById('RmHarvestingBreakupDetails', 'HarvestingBreakupID', $HarvestingBreakupID);
                $sqlBreakUpUpdate = "UPDATE `RmHarvestingBreakupDetails` SET `Variety`='$Variety',`Weight`='$Weight',
                `NoofCrates`='$NoofCrates',`Count`='$Count',`PostedDatetime`='$PostedDate',
                `PostedUserID`=1 WHERE `HarvestingBreakupID`='$HarvestingBreakupID' AND `HarvestingID`='$HarvestingID'";
                $stmtBreakUpUpdate = $this->conn->prepare($sqlBreakUpUpdate);
                $stmtBreakUpUpdate->execute();
                if ($stmtBreakUpUpdate) {
                    $updatedBreakup = $this->auditLogger->fetchRowById('RmHarvestingBreakupDetails', 'HarvestingBreakupID', $HarvestingBreakupID);
                    $this->auditLogger->logUpdate('RmHarvestingBreakupDetails', $HarvestingBreakupID, $existingBreakup, $updatedBreakup, $RequestBody, [
                        'ReferenceNo' => $HarvestingID,
                        'Remarks' => 'Harvesting breakup updated',
                    ]);
                }
            }
        }
    }

    function getHarvestingDetails($RequestBody)
    {
        $HarvestFormNewDesignForSME = !empty($RequestBody["HarvestFormNewDesignForSME"])?$RequestBody["HarvestFormNewDesignForSME"]:'';//SMEV3
        $HarvestingID = $RequestBody['HarvestingID'];
        $BookingID = $RequestBody['BookingID'];
        $BookingDate = $RequestBody['BookingDate'];
        $PurchaseStation = $RequestBody['PurchaseStation'];

        if ($PurchaseStation != '') {
            $Query = "and RmBD.BookingStationID='$PurchaseStation'";
        }

        $Query = '';
        if ($BookingDate != '') {
            $prevDate = date('Y-m-d', strtotime($BookingDate . ' -1 day'));
            $Query .= " AND RmBD.BookingDate = '$prevDate'";
            // $Query .= " AND RmHD.HarvestDate = '$BookingDate'";
        }

        if ($BookingID != '') {
            $Query .= " AND RmBD.RmBookingID = '$BookingID'";
        }
        $smeQueryFields = "";
        if($HarvestFormNewDesignForSME == 1){
            $smeQueryFields = ",HarvestStartingTime,HarvestEndingTime,ReturnIceQtyInCrates,IceQtyInCrates";
            $Query .= " AND RmBD.SampleStatus IN(1,2 )";//1-Accepted,2-Not Applicable, 0-Rejected
        }

        $sqlFields = "`HarvestingID`, `RmBookingID`, RmBD.BookingDate, SupervisorORGrader, `FarmerName`, RmHD.PhoneNumber, `SealNumber`, 
        `BoysGroupID`, `WorkingBoys`, `MTCrates`, `Nets`, `Savels`, `WeightStones`, `WeightMc`,MTCrateWeight, `WaterTin`, RmHD.Remarks, RmHD.DefectRemarks,
        `MaterialTotalQuantity`, `MaterialTotalCrates`,CheckingCount, CheckingQty, RmBD.BookingNumber, FarmAddress, VehicalNumber,
        RmUL.UserName AS DriverName, GroupName, HarvestDate, CheckingCrate, NumberOfPieces, RmS.FinancierName as Supplier
        $smeQueryFields";

        if ($HarvestingID != '') {
            $sqlQuery = "SELECT $sqlFields, GROUP_CONCAT(RmUL2.UserName) AS GraderName FROM `RmBookingDetatils` AS RmBD
            LEFT JOIN RmHarvestingDetails AS RmHD ON RmHD.BookingID = RmBD.RmBookingID 
            LEFT JOIN RmVehicalDetails AS RmVD ON RmBD.VehicalID = RmVD.ID
            LEFT JOIN RmUsersList AS RmUL ON RmBD.DriverID = RmUL.ID
            LEFT JOIN RmUsersList AS RmUL2 ON FIND_IN_SET(RmUL2.ID, RmHD.SupervisorORGrader)
            LEFT JOIN RmBoysGroupMasters AS RmBGM ON RmHD.BoysGroupID = RmBGM.GroupID
            LEFT JOIN RmSuppliers AS RmS ON RmS.ID = RmBD.RmSupplierID
            WHERE RmHD.HarvestingID='$HarvestingID' GROUP BY RmHD.HarvestingID";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `RmBookingDetatils` AS RmBD
            LEFT JOIN RmHarvestingDetails AS RmHD ON RmHD.BookingID = RmBD.RmBookingID
            LEFT JOIN RmVehicalDetails AS RmVD ON RmBD.VehicalID = RmVD.ID
            LEFT JOIN RmUsersList AS RmUL ON RmBD.DriverID = RmUL.ID
            LEFT JOIN RmUsersList AS RmUL2 ON FIND_IN_SET(RmHD.SupervisorORGrader, RmUL2.ID)
            LEFT JOIN RmBoysGroupMasters AS RmBGM ON RmHD.BoysGroupID = RmBGM.GroupID
            LEFT JOIN RmSuppliers AS RmS ON RmS.ID = RmBD.RmSupplierID
            WHERE 1=1 $Query order by RmBD.BookingNumber desc";
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayHarvestData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            //echo '<pre>';print_r($dataRow);die;
            $HarvestingID = $dataRow['HarvestingID'];

            $BreakUpData = $this->getBreakUpDetails($HarvestingID);
            $Totals = $this->getTotals($HarvestingID);
            if($HarvestFormNewDesignForSME == 1){ 
                $QtyBreakUpData = $this->getQtyBreakUpDetails($HarvestingID,$Totals);
                $dataRow['CheckingQty'] = !empty($QtyBreakUpData[0]['CheckingQty'])?$QtyBreakUpData[0]['CheckingQty']:0;
                $dataRow['MTCrateWeight'] = !empty($QtyBreakUpData[0]['MTCrateWeight'])?$QtyBreakUpData[0]['MTCrateWeight']:0;
                //echo '<pre>';print_r($QtyBreakUpData);die;
            }
            $OverallBreakUpData = $this->getOverallBreakUpDetails($HarvestingID);
            $BillerDetails = $this->getBillerDetails();

            $ArrayHarvestList['SNO'] = ++$i;
            $ArrayHarvestList['HarvestingID'] = $HarvestingID;
            $ArrayHarvestList['BookingID'] = $dataRow['RmBookingID'];
            $ArrayHarvestList['BookingNumber'] = $dataRow['BookingNumber'];
            $ArrayHarvestList['BookingDate'] = $dataRow['BookingDate'];
            $ArrayHarvestList['SupervisorORGrader'] = $dataRow['SupervisorORGrader'];
            $ArrayHarvestList['GraderName'] = $dataRow['GraderName'] ? $dataRow['GraderName'] : 'Others';
            $ArrayHarvestList['FarmerName'] = $dataRow['FarmerName'];
            $ArrayHarvestList['FarmAddress'] = $dataRow['FarmAddress'];
            $ArrayHarvestList['PhoneNumber'] = $dataRow['PhoneNumber'];
            $ArrayHarvestList['SealNumber'] = $dataRow['SealNumber'];
            $ArrayHarvestList['VehicalNumber'] = $dataRow['VehicalNumber'];
            $ArrayHarvestList['DriverName'] = $dataRow['DriverName'];
            $ArrayHarvestList['BoysGroupID'] = $dataRow['BoysGroupID'];
            $ArrayHarvestList['GroupName'] = $dataRow['GroupName'];
            $ArrayHarvestList['WorkingBoys'] = $dataRow['WorkingBoys'];
            $ArrayHarvestList['MTCrates'] = $dataRow['MTCrates'];
            $ArrayHarvestList['Nets'] = $dataRow['Nets'];
            $ArrayHarvestList['Savels'] = $dataRow['Savels'];
            $ArrayHarvestList['WeightStones'] = $dataRow['WeightStones'];
            $ArrayHarvestList['MTCrateWeight'] = $dataRow['MTCrateWeight'];
            $ArrayHarvestList['WeightMc'] = $dataRow['WeightMc'];
            $ArrayHarvestList['WaterTin'] = $dataRow['WaterTin'];
            $ArrayHarvestList['Remarks'] = $dataRow['Remarks'];
            $ArrayHarvestList['DefectRemarks'] = $dataRow['DefectRemarks'];

            $ArrayHarvestList['CheckingCount'] = $dataRow['CheckingCount'];
            $ArrayHarvestList['CheckingQty'] = $dataRow['CheckingQty'];
            $ArrayHarvestList['HarvestDate'] = $dataRow['HarvestDate'];
            $ArrayHarvestList['CheckingCrate'] = $dataRow['CheckingCrate'];
            $ArrayHarvestList['NumberOfPieces'] = $dataRow['NumberOfPieces'];
            $ArrayHarvestList['Supplier'] = $dataRow['Supplier'];
            if($HarvestFormNewDesignForSME == 1){
                $ArrayHarvestList['HarvestStartingTime'] = $dataRow['HarvestStartingTime'];
                $ArrayHarvestList['HarvestEndingTime'] = $dataRow['HarvestEndingTime'];
                $ArrayHarvestList['IceQtyInCrates'] = $dataRow['IceQtyInCrates'];
                $ArrayHarvestList['ReturnIceQtyInCrates'] = $dataRow['ReturnIceQtyInCrates'];
            }


            $TotalNetWeight = $Totals['TotalWeight'] + $dataRow['CheckingQty'];

            $ArrayHarvestList['MaterialTotalQuantity'] = $TotalNetWeight;

            $TotalCrateQty = $Totals['TotalCrates'] * $dataRow['MTCrateWeight'];

            $ArrayHarvestList['MaterialTotalCrates'] = $TotalCrateQty;

            $ArrayHarvestList['WithOutNetQty'] = $TotalNetWeight - $TotalCrateQty;
            $ArrayHarvestList['BreakUpData'] = $BreakUpData;
            $ArrayHarvestList['QtyBreakUpData'] = $QtyBreakUpData;
            $ArrayHarvestList['OverallBreakUpData'] = $OverallBreakUpData;
            $ArrayHarvestList['BillerDetails'] = $BillerDetails;

            $ArrayHarvestData[] = $ArrayHarvestList;
        }

        $BookingData = $this->getBookingNumber($BookingID);

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['BookingData'] = $BookingData;
        $data['Data'] = $ArrayHarvestData;

        return $data;
    }

    function getBreakUpDetails($HarvestingID)
    {
        $sqlBreakUpFields = "`HarvestingBreakupID`, `HarvestingID`, `Variety`, `Weight`, `NoofCrates`, `Count`, DefectName,CrateNumber";
         $sqlBreakUpSelect = "SELECT $sqlBreakUpFields FROM RmHarvestingBreakupDetails AS RmHBD
        LEFT JOIN RmDefectsList AS RmDF ON RmHBD.Variety = RmDF.DefectID
        WHERE HarvestingID='$HarvestingID'"; 
        $stmtBreakUpSelect = $this->conn->prepare($sqlBreakUpSelect);
        $stmtBreakUpSelect->execute();

        $j = 0;
        $ArrayBreakUpData = [];
        while ($breakUpData = $stmtBreakUpSelect->fetch(PDO::FETCH_ASSOC)) {
            
            $ArrayBreakUpList['SNO'] = ++$j;
            $ArrayBreakUpList['HarvestingBreakupID'] = $breakUpData['HarvestingBreakupID'];
            $ArrayBreakUpList['CrateNumber'] = $breakUpData['CrateNumber'];
            $ArrayBreakUpList['HarvestingID'] = $breakUpData['HarvestingID'];
            $ArrayBreakUpList['Variety'] = $breakUpData['Variety'];
            $ArrayBreakUpList['DefectName'] = $breakUpData['DefectName'];
            $ArrayBreakUpList['Weight'] = $breakUpData['Weight'];
            $ArrayBreakUpList['NoofCrates'] = $breakUpData['NoofCrates'];
            $ArrayBreakUpList['Count'] = $breakUpData['Count'];

            $ArrayBreakUpData[] = $ArrayBreakUpList;
        }
        return $ArrayBreakUpData;
    }

    function getTotals($HarvestingID)
    {
        $sqlTotals = "SELECT SUM(Weight) AS TotalWeight, SUM(NoofCrates) AS TotalCrates FROM RmHarvestingBreakupDetails 
        WHERE HarvestingID='$HarvestingID' AND HarvestingID !=0";
        $stmtTotals = $this->conn->prepare($sqlTotals);
        $stmtTotals->execute();
        $dataTotals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
        $AryTotals['TotalWeight'] = $dataTotals['TotalWeight'];
        $AryTotals['TotalCrates'] = $dataTotals['TotalCrates'];

        return $AryTotals;
    }

    function getBookingNumber($BookingID)
    {
        $sqlBookingSelect = "SELECT RmBookingID,BookingNumber FROM RmBookingDetatils WHERE RmBookingID='$BookingID'";
        $stmtBookingSelect = $this->conn->prepare($sqlBookingSelect);
        $stmtBookingSelect->execute();
        $ArrBookingData = [];
        while ($dataBookingRow = $stmtBookingSelect->fetch(PDO::FETCH_ASSOC)) {
            $BookingList['RmBookingID'] = $dataBookingRow['RmBookingID'];
            $BookingList['BookingNumber'] = $dataBookingRow['BookingNumber'];

            $ArrBookingData[] = $BookingList;
        }
        return $ArrBookingData;
    }

    function getOverallBreakUpDetails($HarvestingID)
    {
        $sqlBreakUpFields = "RmDF.DefectName as Variety, SUM(Weight) AS TotalWeight, `Count`";
        $sqlBreakUpSelect = "SELECT $sqlBreakUpFields FROM RmHarvestingBreakupDetails AS RmHBD
        LEFT JOIN RmDefectsList AS RmDF ON RmHBD.Variety = RmDF.DefectID
        WHERE HarvestingID='$HarvestingID'
        GROUP BY RmDF.DefectName";
        $stmtBreakUpSelect = $this->conn->prepare($sqlBreakUpSelect);
        $stmtBreakUpSelect->execute();

        $j = 0;
        $ArrayBreakUpData = [];
        while ($breakUpData = $stmtBreakUpSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakUpList['SNO'] = ++$j;
            $ArrayBreakUpList['Variety'] = $breakUpData['Variety'];
            $ArrayBreakUpList['TotalWeight'] = $breakUpData['TotalWeight'];
            $ArrayBreakUpList['Count'] = $breakUpData['Count'];

            $ArrayBreakUpData[] = $ArrayBreakUpList;
        }
        return $ArrayBreakUpData;
    }

    public function getBillerDetails()
    {
        $SqlCheckupBrkUp = "select Name as CompanyName,LogoUrl,GSTNumber,Address  from AdminClientDetails ";
        $stmtCheck = $this->conn->prepare($SqlCheckupBrkUp);
        $stmtCheck->execute();
        $BillerDetails = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        return $BillerDetails;
    }
    public function postQtyBreakUpDetails($HarvestID, $BreakUpData = [], $UserId = '', $RequestBody = []){
        if(!empty($HarvestID) && !empty($BreakUpData['txtMTCrateWeight'])){
            $existingQtyBreakups = $this->auditLogger->fetchRowsByConditions('RmHarvestingQtyBreakupDetails', ['HarvestingID' => $HarvestID]);
            $deleteBreakUpQuery = "DELETE FROM RmHarvestingQtyBreakupDetails WHERE HarvestingID=$HarvestID";
            $stmtBreakUpDelete = $this->conn->prepare($deleteBreakUpQuery);
            $stmtBreakUpDelete->execute();
            if ($stmtBreakUpDelete) {
                foreach ($existingQtyBreakups as $qtyBreakup) {
                    $this->auditLogger->logDelete('RmHarvestingQtyBreakupDetails', isset($qtyBreakup['ID']) ? $qtyBreakup['ID'] : $qtyBreakup['HarvestingID'], $qtyBreakup, $RequestBody, [
                        'ReferenceNo' => $HarvestID,
                        'Remarks' => 'Harvesting qty breakup replaced',
                    ]);
                }
            }
            for($i = 0;$i < count($BreakUpData['txtMTCrateWeight']);$i++){
                $txtMTCrateWeight = $BreakUpData['txtMTCrateWeight'][$i];
                $txtCheckingQty = $BreakUpData['txtCheckingQty'][$i];
                $txtNumberOfPieces = $BreakUpData['txtNumberOfPieces'][$i];
                $txtCheckingCount = $BreakUpData['txtCheckingCount'][$i];
                $txtCheckingCrate = $BreakUpData['txtCheckingCrate'][$i];
                $NoOfPattu = $BreakUpData['NoOfPattu'][$i];
                $txtMaterialtotalQty = $BreakUpData['txtMaterialtotalQty'][$i];
                $txtMaterialtotalCrates = $BreakUpData['txtMaterialtotalCrates'][$i];
                $txtQtyWON = $BreakUpData['txtQtyWON'][$i];
                $VehicleID = $BreakUpData['VehicleID'][$i];
                $PostedDate = date("Y-m-d H:i:s");
                $insertBreakUpQuery = "INSERT INTO RmHarvestingQtyBreakupDetails 
                                        (`HarvestingID`,`MTCrateWeight`,`CheckingQty`,`NumberOfPieces`,`CheckingCount`,`CheckingCrate`,`NoOfPattu`,`MaterialtotalQty`,`MaterialtotalCrates`,`WithOutNetQty`,`CreatedBy`,`CreatedOn`,VehicleID)
                                        VALUES($HarvestID,'$txtMTCrateWeight','$txtCheckingQty','$txtNumberOfPieces','$txtCheckingCount','$txtCheckingCrate','$NoOfPattu','$txtMaterialtotalQty','$txtMaterialtotalCrates','$txtQtyWON','$UserId','$PostedDate','$VehicleID')";
                $stmtBreakUpInsert = $this->conn->prepare($insertBreakUpQuery);
                $stmtBreakUpInsert->execute();
                $BreakUpID = $this->conn->lastInsertId();
                if ($stmtBreakUpInsert) {
                    $newQtyBreakup = $this->auditLogger->fetchRowById('RmHarvestingQtyBreakupDetails', 'ID', $BreakUpID);
                    $this->auditLogger->logInsert('RmHarvestingQtyBreakupDetails', $BreakUpID, $newQtyBreakup, [], [
                        'ReferenceNo' => $HarvestID,
                        'ActionBy' => $UserId,
                        'Remarks' => 'Harvesting qty breakup created',
                    ]);
                }
            }
            if(!empty($BreakUpID)){
                return true;
            }
            return false;
        }
    }

    public function getQtyBreakUpDetails($HarvestID, $Totals = []){
        if(!empty($HarvestID)){
            $HarvestQtyBreakUpQuery = "SELECT * FROM `RmHarvestingQtyBreakupDetails` WHERE `HarvestingID`=$HarvestID";
            $stmtHarvestQtyBreakUp= $this->conn->prepare($HarvestQtyBreakUpQuery);
            $stmtHarvestQtyBreakUp->execute();
            //$ResHarvestQtyBreakUp = $stmtHarvestQtyBreakUp->fetch(PDO::FETCH_ASSOC);
           // $ArrayBreakUpData = [];$j = 0;
            while ($dataRow = $stmtHarvestQtyBreakUp->fetch(PDO::FETCH_ASSOC)) {
               
                $TotalNetWeight = $Totals['TotalWeight'] + $dataRow['CheckingQty'];

                $dataRow['MaterialtotalQty'] = $TotalNetWeight;

                $TotalCrateQty = $Totals['TotalCrates'] * $dataRow['MTCrateWeight'];

                $dataRow['MaterialtotalCrates'] = $TotalCrateQty;

                $dataRow['WithOutNetQty'] = $TotalNetWeight - $TotalCrateQty;
                $res[] = $dataRow;
            
            }
            return $res;
        }
        return false;
    }
}
