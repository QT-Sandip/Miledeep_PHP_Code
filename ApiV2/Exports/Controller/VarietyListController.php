<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class VarietyListController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;
    private $auditLogger;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
    }
    // GET ALL
    public function GetPIListDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $BrandList = $RequestBody['BrandList'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ShortName = $RequestBody['ShortName'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        // $Status = $RequestBody['Status'];


        // Dropdown Conditions
        $Query = '';
        if ($BrandList != "") {
            $Query .= 'and SPVL.BrandName in (' . $BrandList . ')';
        }

        if ($Species != "") {
            $Query .= " and SPVL.Species = '$Species'";
        }

        if ($TypeProduct != "") {
            $Query .= "and SPVL.TypeProduct = '$TypeProduct'";
        }

        if ($ShortName != "") {
            $Query .= " and SPVL.ShortName = '$ShortName'";
        }

        if ($Grade != "") {
            $Query .= " and SPVL.Grade = '$Grade'";
        }

        if ($FreezingType != "") {
            $Query .= " and SPVL.FreezingType = '$FreezingType'";
        }
        // if ($Status != "") {
        //     $Query .= ' and SPVL.Status='.$Status;
        // }



        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', SPVL.ShortName,SPVL.BrandName,SPVL.FullName,SPVL.Grade,SPVL.FreezingType,STM.TreatmentName,SPVL.PreparationType,APS.PackingStyle,SPVL.TypeProduct,SPVL.ProductDescription,SPVL.BuyerSUPC,SPVL.DeviSUPC) LIKE '%$SearchKey%'";
        }


        // $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `ScrmPIVarietyList` WHERE Status='1'";
        $sqlVarietyCount = "SELECT COUNT(*) AS TotalCount
        FROM `ScrmPIVarietyList` SPVL
        left join ScrmBrandsMaster SBM ON SBM.BrandID=SPVL.BrandName
        left join AdminPackingStyles APS ON APS.PCKID=SPVL.PackingStyle
        left join ScrmTreatmentsMaster STM ON STM.ShortCode=SPVL.Treatment
        where SPVL.Status='1' $Query";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);
// echo 
        $SqlVarietyQuery = "SELECT *,SBM.BrandName AS BRND,STM.ShortCode as Treatment,
        APS.PackingStyle AS PCKSTYL
        FROM `ScrmPIVarietyList` SPVL
        left join ScrmBrandsMaster SBM ON SBM.BrandID=SPVL.BrandName
        left join AdminPackingStyles APS ON APS.PCKID=SPVL.PackingStyle
        left join ScrmTreatmentsMaster STM ON STM.ShortCode=SPVL.Treatment
        where SPVL.Status='1' $Query  ORDER by VarietyID desc $Limit ";

        $stmt = $this->conn->prepare($SqlVarietyQuery);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $AryVarietyData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['SNO'] = ++$i;
            $ArrayVarietyList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayVarietyList['ShortName'] = $dataRow['ShortName'];
            $ArrayVarietyList['FullName'] = $dataRow['FullName'];
            $ArrayVarietyList['Grade'] = $dataRow['Grade'];
            $ArrayVarietyList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayVarietyList['TreatmentName'] = $dataRow['TreatmentName'];
            $ArrayVarietyList['PreparationType'] = $dataRow['PreparationType'];
            $ArrayVarietyList['BRND'] = $dataRow['BRND'];
            $ArrayVarietyList['PCKSTYL'] = $dataRow['PCKSTYL'];
            $ArrayVarietyList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayVarietyList['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayVarietyList['BuyerSUPC'] = $dataRow['BuyerSUPC'];
            $ArrayVarietyList['DeviSUPC'] = $dataRow['DeviSUPC'];

            $AryVarietyData[] = $ArrayVarietyList;
        }
        if (count($AryVarietyData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "AddVariety?id=";
        $data['Data'] = $AryVarietyData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }

    public function duplicateVariety($RequestBody)
    {
        $Error = null;
        $Msg = '';
        $VarietyID = $RequestBody['VarietyID'];


        if ($VarietyID != '') {

            // $SqlPICount = "SELECT * FROM ScrmPIData WHERE PoID = '$OrderID'";
            // $stmt = $this->conn->prepare($SqlPICount);
            // $stmt->execute();
            // $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

            // $ExporterID = $dataRow['ExporterID'];
            // $InvDate = $dataRow['InvDate'];
            //$PoNo = $dataRow['PoNo'];

            // $SqlPiDetails = "SELECT max(InvNo) as InvNO from ScrmPIData where ExporterID='$ExporterID'";
            // $stmt = $this->conn->prepare($SqlPiDetails);
            // $stmt->execute();
            // $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            // $InvNO = $dataRow['InvNO'] + 1;
            // $InvNumFull = $this->utilController->getFullPiInvoiceNumbertoSave($ExporterID, $InvDate, $InvNO);
            // $InvNumFull = $dataRow['InvNO'] + 1; //update codew

            if ($Error == null) {
                // $SqlDuplicatePI = "INSERT INTO `ScrmPIData`(
                //      `PoRefId`, `InvNo`, `InvNumFull`, 
                //     `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`,
                //      `SciName`, `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`,
                //       `TypeCountry`, `PoStatus`, `Year`, `BankID`, `PortOfDischarge`, 
                //       `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, 
                //       `PreparedBy`, `PRInstrctns`, `ShipByDate`, `ShippedOutDate`, `Status`,
                //        `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`, `ImporterID`, 
                //        `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`, 
                // `PortofLoading`, `ShowBank`, `UltimateConsigneeID`, `Freight`, `PostedDate`, `IsEdit`)
                // SELECT  `PoRefId`, '$InvNO', '$InvNumFull', '',
                // `ExporterID`, `ProcessorID`, `LotNo`, `SciName`, `ConsigneeID`,
                //  `BuyerID`, '', '',`DestinationCountry`, `TypeCountry`, 
                //  `PoStatus`, `Year`, `BankID`, `PortOfDischarge`, `FinalDestination`,
                //   `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`, 
                //   `PRInstrctns`, `ShipByDate`, `ShippedOutDate`, '0', 
                //   `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`, 
                //   `ImporterID`, `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`, 
                //   `PortofLoading`, `ShowBank`, `UltimateConsigneeID`, `Freight`,
                //    `PostedDate`, '0' FROM `ScrmPIData` where PoID = '$OrderID'";
                   
                   $SqlDuplicatePI = "INSERT INTO `ScrmPIVarietyList`( `TypeProduct`, `ShortName`, `FullName`, `Category`, 
                   `Status`, `BuyerID`, `ItemCode`, `Glaze`, `GlazePerc`, `Treatment`, `Grade`, `FreezingType`, `ProductID`, `ProductDescription`,
                    `TestDesc`, `Species`, `PreparationType`, 
                   `BrandName`, `GlazeStatus`, `PackingStyle`, `UltimateConsignee`, `BuyerProdDescription`, `BuyerSUPC`, `DeviSUPC`)
                   
                   SELECT  `TypeProduct`, `ShortName`, `FullName`, `Category`, `Status`, `BuyerID`, `ItemCode`, `Glaze`, `GlazePerc`,
                    `Treatment`, '', `FreezingType`, `ProductID`, `ProductDescription`, `TestDesc`, `Species`, `PreparationType`, `BrandName`,
                     `GlazeStatus`, '',
                    `UltimateConsignee`, `BuyerProdDescription`, '', `DeviSUPC` FROM `ScrmPIVarietyList` WHERE VarietyID = '$VarietyID'";
                $stmt = $this->conn->prepare($SqlDuplicatePI);
                $stmt->execute();
                if ($stmt) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $sourceVariety = $this->auditLogger->fetchRowById('ScrmPIVarietyList', 'VarietyID', $VarietyID);
                    $createdVariety = $this->auditLogger->fetchRowById('ScrmPIVarietyList', 'VarietyID', $lastInsertedId);
                    $this->auditLogger->logInsert('ScrmPIVarietyList', $lastInsertedId, $createdVariety, $RequestBody, [
                        'ReferenceNo' => isset($sourceVariety['ShortName']) ? $sourceVariety['ShortName'] : $VarietyID,
                        'Remarks' => 'Export variety duplicated',
                    ]);
                }
                // $DupInvID = $this->conn->lastInsertId();
                if (!$stmt) {
                    $Error = "Unable Create Temp Order";
                }
            }




            // if ($Error == null && $DupInvID != '') {

            //     $SqlGradeBreakUp = "INSERT INTO `ScrmPoGradeData`( 
            //     `InvId`, `PINumber`, `CInvFull`, `LineNo`, `Grade`, `Mcs`, 
            //     `CurrentMcs`, `FreezingType`, `PackingStyleID`, `GrossWt`, `GlazeType`, `Variety`, 
            //     `GlazePerc`, `Treatment`, `Brand`, `Price`, `BAP`, `VarietyID`, `SKUNo`, `BuyerItemCode`, 
            //     `Description`, `CodeType`, `DeviSupc`) 
                
            //      SELECT  $DupInvID, `PINumber`, `CInvFull`, `LineNo`, `Grade`, `Mcs`, 
            //     `CurrentMcs`, `FreezingType`, `PackingStyleID`, `GrossWt`, `GlazeType`, `Variety`, 
            //     `GlazePerc`, `Treatment`, `Brand`, `Price`, `BAP`, `VarietyID`, `SKUNo`, `BuyerItemCode`, 
            //     `Description`, `CodeType`, `DeviSupc` FROM ScrmPoGradeData WHERE InvId = '$OrderID'";
            //     $stmt = $this->conn->prepare($SqlGradeBreakUp);
            //     $stmt->execute();
            //     if (!$stmt) {
            //         $Error = "Unable Update Temp Order";
            //     }
            // }



        }

        // $AryData['Error'] = $Error;
        // $AryData['Msg'] = $Msg;

        // return $AryData;

        return $this->GetPIListDetails($RequestBody);


    }


}
