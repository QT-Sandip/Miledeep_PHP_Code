<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';
include_once 'Controller/CsIBTRequestEntryController.php';

session_start();

class CsIBTBreakUpController
{
    private $conn;
    private $csIBTREController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->csIBTREController = new CsIBTRequestEntryController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function postBreakUPDetails($RequestBody)
    {


        // var_dump($RequestBody);
        // exit;
        $RequestBody['IBTID'] = $RequestBody['IBTRequestID'];

        $AryReq=$this->csIBTREController->postRequestDetails($RequestBody);

        $RequestBody['IBTRequestID']= $AryReq['IBTRequestID'];
        $RequestBody['RequestStatus']= $AryReq['RequestStatus'];
        $RequestBody['IBTID']= $AryReq['IBTRequestID'];


        $IBTBreakupID = $RequestBody['IBTBreakupID'];
        $StockItems = $RequestBody['StockItems'];
        if ($IBTBreakupID == null || $IBTBreakupID == '') {

            foreach ($StockItems as $Item) {
                $Data = $this->insertBreakUpDetails($RequestBody, $Item);
            }
            // $Data = $this->insertBreakUpDetails($RequestBody);
        } else {
            $Data = $this->updateBreakUpDetails($RequestBody);
        }

        return $Data;
    }

    public function insertBreakUpDetails($RequestBody, $Item)
    {

        $AryCurrentDate = getCurrentDateTime();
        $PostedDateNTime = $AryCurrentDate['TimeStamp'];

        $IBTRequestID = $RequestBody['IBTRequestID'];
        $IBTOutReason = $RequestBody['StockOutReason'];
        $TransferDate = $RequestBody['StockOutDate'];

        // sriram
        $NoOfMcs = $Item['OutMcs'];
        $NoOfLooseBags = $Item['OutLooseBags'];
        $StockID = $Item['StockID'];

        $AvailableStocks = $Item['AvailableStock'];
        $AvailableStocksArr = explode(',', $AvailableStocks);

        $DataArr = [];

        foreach ($AvailableStocksArr as $AvailableStock) {

            $AvailableStockData = explode('-',$AvailableStock);
            $StockID = $AvailableStockData[0];
            $PoID = $AvailableStockData[1];
            $Mcs = $AvailableStockData[2];
            $LooseBags = $AvailableStockData[3];

            if($NoOfMcs > 0 || $NoOfLooseBags > 0){
                $RequestStatus=$RequestBody['RequestStatus'];
                $PostedUserID = $_SESSION['PostedUserID'];
                $AryCurrentDate = getCurrentDateTime();
                $PostedDateNTime = $AryCurrentDate['TimeStamp'];
    
                $sqlVariety = "SELECT CPID.VarietyID,PackingTypeID, DeviSUPC FROM CsStockData AS CPID
                LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
                WHERE StockID = $StockID ";
    
                $stmtVariety = $this->conn->prepare($sqlVariety);
                $stmtVariety->execute();
                $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
    
                $VarietyID = $dataVariety['VarietyID'];
                $PackingType = $dataVariety['PackingTypeID'];
                $SUPC = $dataVariety['DeviSUPC'];
                
                if($Mcs > $NoOfMcs){
                    $Mcs = $NoOfMcs;
                }
                if($LooseBags > $NoOfLooseBags){
                    $LooseBags = $NoOfLooseBags;
                }

                $sqlInsert = "INSERT INTO CsIBTRequestBreakUpDetails(IBTRequestID, VarietyID, SUPC,StockID, NoOfMcs, 
                NoOfLooseBags,PoID, PackingType, Status, PostedUserID, PostedDateNTime,TransferDate, IBTOutReason) 
                VALUES ('$IBTRequestID','$VarietyID','$SUPC','$StockID','$Mcs','$LooseBags','$PoID','$PackingType',
                '$RequestStatus','$PostedUserID','$PostedDateNTime','$TransferDate','$IBTOutReason')"; 
    
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                $NoOfMcs -= $Mcs;
                $NoOfLooseBags -= $LooseBags;

                if ($stmtInsert) {

                    $lastInsertedId = $this->conn->lastInsertId();
                    $RequestBody['IBTBreakupID'] = $lastInsertedId;
                    $createdBreakup = $this->auditLogger->fetchRowById('CsIBTRequestBreakUpDetails', 'IBTBreakupID', $lastInsertedId);
                    $this->auditLogger->logInsert('CsIBTRequestBreakUpDetails', $lastInsertedId, $createdBreakup, $RequestBody, [
                        'ReferenceNo' => $IBTRequestID,
                        'Remarks' => 'Cold store IBT breakup created',
                        'Async' => 1,
                    ]);
        
                    $BreakUpData = $this->getBreakUpDetails($RequestBody);
                    $TotalData = $this->getBrkUpTotals($RequestBody);
                    $Data['Error'] = null;
                    $Data['Msg'] = 'IBT BreakUp Posted Successfully';
                    $Data['EditLink'] = $BreakUpData['EditLink'];
                    $Data['Data'] = $BreakUpData['Data'];
                    $Data['Total'] = $TotalData['Data'];

                    $DataArr[] = $Data;
                } 
            }
        }

        // $PoID = $Item['PoID'];
        // $Status = $Item['Status'];
        
        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($DataArr != []) {

            // $lastInsertedId = $this->conn->lastInsertId();
            // $RequestBody['IBTBreakupID'] = $lastInsertedId;

            // $BreakUpData = $this->getBreakUpDetails($RequestBody);
            // $TotalData = $this->getBrkUpTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'IBT BreakUp Posted Successfully';
            // $Data['EditLink'] = $BreakUpData['EditLink'];
            $Data['Data'] = $DataArr;
            // $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert IBT BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }


        // $StockID = $Item['StockID'];
        // $NoOfMcs = $Item['OutMcs'];
        // $NoOfLooseBags = $Item['OutLooseBags'];
        // $PoID = $Item['PoID'];
        // // $Status = $Item['Status'];
        // $RequestStatus=$RequestBody['RequestStatus'];
        // $PostedUserID = $_SESSION['PostedUserID'];
        // $AryCurrentDate = getCurrentDateTime();
        // $PostedDateNTime = $AryCurrentDate['TimeStamp'];

        // $sqlVariety = "SELECT CPID.VarietyID,PackingTypeID, DeviSUPC FROM CsStockData AS CPID
        // LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
        // WHERE StockID = $StockID ";

        // $stmtVariety = $this->conn->prepare($sqlVariety);
        // $stmtVariety->execute();
        // $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);

        // $VarietyID = $dataVariety['VarietyID'];
        // $PackingType = $dataVariety['PackingTypeID'];
        // $SUPC = $dataVariety['DeviSUPC'];

        // $sqlInsert = "INSERT INTO CsIBTRequestBreakUpDetails(IBTRequestID, VarietyID, SUPC,StockID, NoOfMcs, 
        // NoOfLooseBags,PoID, PackingType, Status, PostedUserID, PostedDateNTime,TransferDate, IBTOutReason) 
        // VALUES ('$IBTRequestID','$VarietyID','$SUPC','$StockID','$NoOfMcs','$NoOfLooseBags','$PoID','$PackingType',
        // '$RequestStatus','$PostedUserID','$PostedDateNTime','$TransferDate','$IBTOutReason')"; 

        // $stmtInsert = $this->conn->prepare($sqlInsert);
        // $stmtInsert->execute();

        // // $errorInfo = $stmtInsert->errorInfo();
        // // var_dump($errorInfo);

        // if ($stmtInsert) {

        //     $lastInsertedId = $this->conn->lastInsertId();
        //     $RequestBody['IBTBreakupID'] = $lastInsertedId;

        //     $BreakUpData = $this->getBreakUpDetails($RequestBody);
        //     $TotalData = $this->getBrkUpTotals($RequestBody);
        //     $Data['Error'] = null;
        //     $Data['Msg'] = 'IBT BreakUp Posted Successfully';
        //     $Data['EditLink'] = $BreakUpData['EditLink'];
        //     $Data['Data'] = $BreakUpData['Data'];
        //     $Data['Total'] = $TotalData['Data'];
        //     return $Data;
        // } else {
        //     $Data['Error'] = 'Unable to Insert IBT BreakUp';
        //     $Data['Msg'] = $this->Error;
        //     return $Data;
        // }
    }

    public function updateBreakUpDetails($RequestBody)
    {
        $IBTBreakupID = $RequestBody['IBTBreakupID'];
        $IBTRequestID = $RequestBody['IBTRequestID'];
        $VarietyID = $RequestBody['VarietyID'];
        $SUPC = $RequestBody['SUPC'];
        $StockID = $RequestBody['StockID'];
        $NoOfMcs = $RequestBody['NoOfMcs'];
        $NoOfLooseBags = $RequestBody['NoOfLooseBags'];
        $PoID = $RequestBody['PoID'];
        $PackingType = $RequestBody['PackingType'];
        // $Status = $RequestBody['Status'];
         $IBTOutReason = $RequestBody['stockOutReason'];
        $TransferDate = $RequestBody['stockOutDate'];
        $PostedUserID = $_SESSION['PostedUserID'];
        $RequestStatus=$RequestBody['RequestStatus'];
        $AryCurrentDate = getCurrentDateTime();
        $existingBreakup = $this->auditLogger->fetchRowById('CsIBTRequestBreakUpDetails', 'IBTBreakupID', $IBTBreakupID);

        $PostedDateNTime = $AryCurrentDate['TimeStamp'];

        $sqlUpdate = "UPDATE CsIBTRequestBreakUpDetails SET IBTRequestID='$IBTRequestID',VarietyID='$VarietyID',SUPC='$SUPC',
        StockID='$StockID',NoOfMcs='$NoOfMcs',NoOfLooseBags='$NoOfLooseBags',PoID='$PoID',PackingType='$PackingType',
        Status='$RequestStatus',PostedUserID='$PostedUserID',PostedDateNTime='$PostedDateNTime',`TransferDate`='$TransferDate',`IBTOutReason`='$IBTOutReason'
        WHERE IBTBreakupID=$IBTBreakupID";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedBreakup = $this->auditLogger->fetchRowById('CsIBTRequestBreakUpDetails', 'IBTBreakupID', $IBTBreakupID);
            $this->auditLogger->logUpdate('CsIBTRequestBreakUpDetails', $IBTBreakupID, $existingBreakup, $updatedBreakup, $RequestBody, [
                'ReferenceNo' => $IBTRequestID,
                'Remarks' => 'Cold store IBT breakup updated',
            ]);
            $BreakUpData = $this->getBreakUpDetails($RequestBody);
            $TotalData = $this->getBrkUpTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'IBT BreakUp Updated Successfully';
            $Data['Data'] = $BreakUpData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update IBT BreakUp';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteBreakUpDetails($RequestBody)
    {
        $IBTBreakupID = $RequestBody['IBTBreakupID'];
        $existingBreakup = $this->auditLogger->fetchRowById('CsIBTRequestBreakUpDetails', 'IBTBreakupID', $IBTBreakupID);

        $sqlDelete = "DELETE FROM `CsIBTRequestBreakUpDetails` WHERE IBTBreakupID='$IBTBreakupID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('CsIBTRequestBreakUpDetails', $IBTBreakupID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => isset($existingBreakup['IBTRequestID']) ? $existingBreakup['IBTRequestID'] : null,
                'Remarks' => 'Cold store IBT breakup deleted',
            ]);
            $BreakUpData = $this->getBreakUpDetails($RequestBody);
            $TotalData = $this->getBrkUpTotals($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Request Deleted Successfully';
            $Data['Data'] = $BreakUpData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Request';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getBreakUpDetails($RequestBody)
    {

        $IBTRequestID = $RequestBody['IBTRequestID'];

        $sqlFields = "CIBTRB.IBTBreakupID, CIBTRB.IBTRequestID, CIBTRB.VarietyID, SPVL.FullName,
        CIBTRB.SUPC, CIBTRB.StockID, CSD.AvalMcs, CIBTRB.NoOfMcs, 
        CIBTRB.NoOfLooseBags,CIBTRB.PoID, CASE CIBTRB.PoID WHEN 1 THEN 'DUMMY' WHEN 2 THEN 'MASTER' END AS PoIDName,
        CIBTRB.PackingType, AGO.Name ,CIBTRB.Status, CIBTRB.PostedUserID, CIBTRB.PostedDateNTime";

        if ($IBTRequestID != null && $IBTRequestID != '') {
            // echo 
            $sqlQuery = "SELECT $sqlFields FROM CsIBTRequestBreakUpDetails AS CIBTRB
            LEFT JOIN ScrmPIVarietyList AS SPVL ON CIBTRB.VarietyID=SPVL.VarietyID
            LEFT JOIN CsStockData AS CSD ON CIBTRB.StockID=CSD.StockID
            LEFT JOIN AdminGeneralOptions AS AGO ON CIBTRB.PackingType=AGO.OptID 
            WHERE IBTRequestID=$IBTRequestID";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM CsIBTRequestBreakUpDetails AS CIBTRB
            LEFT JOIN ScrmPIVarietyList AS SPVL ON CIBTRB.VarietyID=SPVL.VarietyID
            LEFT JOIN CsStockData AS CSD ON CIBTRB.StockID=CSD.StockID
            LEFT JOIN AdminGeneralOptions AS AGO ON CIBTRB.PackingType=AGO.OptID 
            WHERE 1=1 ORDER BY IBTRequestID ";
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayBreakUpData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBreakUpList['SNO'] = ++$i;
            $ArrayBreakUpList['IBTBreakupID'] = $dataRow['IBTBreakupID'];
            $ArrayBreakUpList['IBTRequestID'] = $dataRow['IBTRequestID'];
            $ArrayBreakUpList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayBreakUpList['FullName'] = $dataRow['FullName'];
            $ArrayBreakUpList['SUPC'] = $dataRow['SUPC'];
            $ArrayBreakUpList['StockID'] = $dataRow['StockID'];
            $ArrayBreakUpList['AvalMcs'] = $dataRow['AvalMcs'];
            $ArrayBreakUpList['NoOfMcs'] = $dataRow['NoOfMcs'];
            $ArrayBreakUpList['NoOfLooseBags'] = $dataRow['NoOfLooseBags'];
            $ArrayBreakUpList['PoID'] = $dataRow['PoID'];
            $ArrayBreakUpList['PoIDName'] = $dataRow['PoIDName'];
            $ArrayBreakUpList['PackingType'] = $dataRow['PackingType'];
            $ArrayBreakUpList['Name'] = $dataRow['Name'];
            $ArrayBreakUpList['Status'] = $dataRow['Status'];
            $ArrayBreakUpList['PostedUserID'] = $dataRow['PostedUserID'];
            $ArrayBreakUpList['PostedDateNTime'] = $dataRow['PostedDateNTime'];

            $ArrayBreakUpData[] =  $ArrayBreakUpList;
        }

        $TotalData = $this->getBrkUpTotals($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['RemainingCount'] = "";
        $data['EditLink'] = "#";
        $data['Data'] = $ArrayBreakUpData;
        $data['Total'] = $TotalData['Data'];

        return $data;
    }

    function getBrkUpTotals($RequestBody)
    {

        $IBTRequestID = $RequestBody['IBTRequestID'];

        $sqlTotal = "SELECT SUM(NoOfMcs) AS TotalMcs, SUM(NoOfLooseBags) AS TotalLooseBags FROM CsIBTRequestBreakUpDetails WHERE IBTRequestID=$IBTRequestID";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTotalData['TotalMcs'] = $dataRow['TotalMcs'];
            $ArrayTotalData['TotalLooseBags'] = $dataRow['TotalLooseBags'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
}
