<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class CsIBTBreakUpPreviewController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);
    }

    function deleteBreakUpDetails($RequestBody)
    {
        $IBTBreakupID = $RequestBody['IBTBreakupID'];
        $IBTRequestID = $RequestBody['IBTRequestID'];
        $existingBreakup = $this->auditLogger->fetchRowById('CsIBTRequestBreakUpDetails', 'IBTBreakupID', $IBTBreakupID);

        $sqlDelete = "DELETE FROM `CsIBTRequestBreakUpDetails` WHERE IBTBreakupID='$IBTBreakupID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('CsIBTRequestBreakUpDetails', $IBTBreakupID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => $IBTRequestID,
                'Remarks' => 'Cold store IBT breakup preview row deleted',
            ]);
            $sqlSelect = "SELECT COUNT(*) AS TotalCount FROM CsIBTRequestBreakUpDetails WHERE IBTRequestID = '$IBTRequestID'";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();
            $dataSelectRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            $TotalCount = $dataSelectRow['TotalCount'];
            // echo $TotalCount ; 

            if ($TotalCount == 0) {
                $existingRequest = $this->auditLogger->fetchRowById('CsIBTRequestDetails', 'IBTID', $IBTRequestID);
                $sqlDeleteRequest = "DELETE FROM CsIBTRequestDetails WHERE IBTID = '$IBTRequestID'";
                $stmtDeleteRequest = $this->conn->prepare($sqlDeleteRequest);
                $stmtDeleteRequest->execute();
                if ($stmtDeleteRequest) {
                    $this->auditLogger->logDelete('CsIBTRequestDetails', $IBTRequestID, $existingRequest, $RequestBody, [
                        'ReferenceNo' => isset($existingRequest['IBTOrderNumber']) ? $existingRequest['IBTOrderNumber'] : null,
                        'Remarks' => 'Cold store empty IBT request deleted after breakup removal',
                    ]);
                }
            }
            $BreakUpData = $this->getBreakUpDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Request Deleted Successfully ';
            $Data['Data'] = $BreakUpData['Data'];
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

        $sqlFields = "CRBD.StockID,CSD.ProductionID,CSD.BarCodeID,CSD.StoreRackID,CSRD.StoreID,CSRD.BlockID,CSS.StoreName,CRBD.IBTBreakupID, CRBD.NoOfMcs, CRBD.NoOfLooseBags, CSD.AvalMcs, CSD.LooseBags, CSD.PackingTypeID, CSD.Grade, CSD.ProductCategoryID,
        SPD.PoNo,SPVL.ShortName, APS.PackingStyle, CSRD.RackName, (CRBD.NoOfMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) as AvalMcsKgs, 
        (CRBD.NoOfLooseBags * APS.SlabWeight * APS.MFactor) as LooseKgs,OrderDate,RequestStatus, CIR.ToStore,TransferDate,IBTOutReason,Uom,TotalSlabs,SlabWeight,LbsFactor,MFactor";
        // echo
        $sqlQuery = "SELECT $sqlFields FROM CsIBTRequestBreakUpDetails AS CRBD
        LEFT JOIN CsStockData AS CSD ON CRBD.StockID=CSD.StockID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON CSD.VarietyID=SPVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID=APS.PCKID
        LEFT JOIN CsStoresRackData AS CSRD ON CSD.StoreRackID=CSRD.RowID
        LEFT JOIN CsStores AS CSS ON CSS.StoreID=CSRD.StoreID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN CsIBTRequestDetails AS CIR ON CIR.IBTID=CRBD.IBTRequestID
        WHERE CRBD.IBTRequestID='$IBTRequestID'";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $dataRowRequestStatus = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $RequestStatus = $dataRowRequestStatus['RequestStatus'];

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $AryData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

              if ($dataRow['Uom'] == 'KGS' || $dataRow['Uom'] == 'GRAMS') {
                    $Kgs = ($dataRow['NoOfMcs'] * $dataRow['TotalSlabs'] + $dataRow['NoOfLooseBags']) * $dataRow['SlabWeight'] * $dataRow['MFactor'];
                } else {
                    $Kgs = ($dataRow['NoOfMcs'] * $dataRow['TotalSlabs'] + $dataRow['NoOfLooseBags']) * $dataRow['SlabWeight'] * $dataRow['LbsFactor'] * 0.454;
                }
            // $Kgs = $dataRow['AvalMcsKgs'] + $dataRow['LooseKgs'];

            $AryBrkData['SNO'] = ++$i;
            $AryBrkData['IBTBreakupID'] = $dataRow['IBTBreakupID'];
            $AryBrkData['NoOfMcs'] = $dataRow['NoOfMcs'];
            $AryBrkData['NoOfLooseBags'] = $dataRow['NoOfLooseBags'];
            $AryBrkData['AvalMcs'] = $dataRow['AvalMcs'];
            $AryBrkData['LooseBags'] = $dataRow['LooseBags'];
            $AryBrkData['PackingTypeID'] = $dataRow['PackingTypeID'];
            $AryBrkData['Grade'] = $dataRow['Grade'];
            $AryBrkData['ProductCategoryID'] = $dataRow['ProductCategoryID'];
            $AryBrkData['ShortName'] = $dataRow['ShortName'];
            $AryBrkData['PackingStyle'] = $dataRow['PackingStyle'];
            $AryBrkData['RackName'] = $dataRow['RackName'];
            $AryBrkData['AvalMcsKgs'] = $dataRow['AvalMcsKgs'];
            $AryBrkData['LooseKgs'] = $dataRow['LooseKgs'];
            $AryBrkData['StoreName'] = $dataRow['StoreName'];
            $AryBrkData['PoNumber'] = $dataRow['PoNo'];
            $AryBrkData['Kgs'] = sprintf('%0.2f', round($Kgs, 2));
            $AryBrkData['StockID'] = $dataRow['StockID'];
            $AryBrkData['BarCodeID'] = $dataRow['BarCodeID'];
            $AryBrkData['StoreID'] = $dataRow['StoreID'];
            $AryBrkData['ToStore'] = $dataRow['ToStore'];
            $AryBrkData['RackID'] = $dataRow['StoreRackID'];
            $AryBrkData['OutMcs'] = $dataRow['NoOfMcs'];
            $AryBrkData['OutLooseBags'] = $dataRow['NoOfLooseBags'];
            $AryBrkData['BlockID'] = $dataRow['BlockID'];
            $AryBrkData['ProductionID'] = $dataRow['ProductionID'];
            // $AryBrkData['StockOutDate'] = $dataRow['OrderDate'];
            $AryBrkData['StockOutDate'] = $dataRow['TransferDate'];
            $AryBrkData['StockOutReason'] = "IBT Out";
            $AryBrkData['TransferDate'] = date('d-m-Y', strtotime($dataRow['TransferDate']));
            $AryBrkData['Reason'] = $dataRow['IBTOutReason'];


            $AryData[] = $AryBrkData;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['RequestStatus'] = $RequestStatus;
        $Data['Data'] = $AryData;

        return $Data;
    }
}
