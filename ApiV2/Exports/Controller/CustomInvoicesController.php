<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class CustomInvoicesController
{
    private $conn;
    private $db_table = "ScrmCustomInvoices";
    private $utilController;
    private $Error = null;
    private $auditLogger;

    private $Msg = '';




    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
    }
    // GET ALL
    public function GetCustomsListDetails($RequestBody)
    {
        $RecordsPerPage = 100;

        $Year = $RequestBody['Year'];
        $ExporterID = $RequestBody['ExporterID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $CountryID = $RequestBody['CountryID'];
        $InvType = $RequestBody['InvType'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $PlantID = $RequestBody['PlantID'];

        $Query = '';
        $SubQuery = '';
        $SQuery = '';
        $IQuery = '';


        $DateStart = $Year . '-04-01';
        $DateEnd = ($Year + 1) . '-03-31';
        $DateFilter = "between '$DateStart' and '$DateEnd'";

        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ConsigneeID != "") {
            $Query .= ' and POD.ConsigneeID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and POD.DestinationCountry=' . $CountryID;
        }

        if ($InvType == "0") {

            $IQuery = ' POD.PoID  not in (SELECT PiID FROM `ScrmCustomInvoices` ) and  POD.Status not in (7,3,4)';
        } else if ($InvType == "1") {
            $IQuery = " SCI.CInvDate $DateFilter and POD.PoID  in 
            (SELECT PiID FROM `ScrmCustomInvoices` ) and  POD.Status not in (7,3,4)";
        } else if ($InvType == "4") {
            $IQuery = " SCI.CInvDate $DateFilter and POD.PoID  in 
                (SELECT PiID FROM `ScrmCustomInvoices` ) AND POD.Status=4";
        } else if ($InvType == "7" || $InvType == "8") {
            $IQuery = " SCI.CInvDate $DateFilter";
        }

        /*$IQuery = " SCI.CInvDate $DateFilter and POD.PoID  in 
        (SELECT PiID FROM `ScrmCustomInvoices` )";*/

        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ConsigneeName,PoNo,InvNumFull,CustomsInvNo) LIKE '%$SearchKey%'";
        }
        if(!empty($PlantID) && $PlantID != '' && $PlantID != 0){
            $ProcessorSql = "SELECT GROUP_CONCAT(ExpID) ExpID FROM AdminExporterDetails 
                                WHERE ExpType IN(2,3) AND ProcessingStationID = '$PlantID'";
            $stmtProcessor = $this->conn->prepare($ProcessorSql);
            $stmtProcessor->execute();
            $ExpID = $stmtProcessor->fetch(PDO::FETCH_ASSOC)['ExpID'];
            if(!empty($ExpID)){
                $Query .= " AND POD.ProcessorID IN ($ExpID) ";
            }                
        }

        $SqlCustomCount = "SELECT count(*) as Total  FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        WHERE $IQuery  $SubQuery $SQuery $Query";

        $stmt = $this->conn->prepare($SqlCustomCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        if ($InvType == 7) {
            $TableName = 'ScrmCancelledCustomInvoices';
            $CancelStatus = ' , CustomsInvoiceStatus';
        } else if ($InvType == 8) {
            $TableName = 'ScrmDeletedCustomInvoices';
            $CancelStatus = ' , CustomsInvoiceStatus';
        } else {
            $TableName = 'ScrmCustomInvoices';
            $CancelStatus = '';
        }

        $SauceQry = "(
                SELECT
                    CASE
                        WHEN (
                            SELECT COUNT(*)
                            FROM ScrmPIVarietyList AS SPVL
                            WHERE SPVL.VarietyID IN (
                                SELECT VarietyID
                                FROM ScrmPoGradeData
                                WHERE InvId = POD.PoID
                            )
                            AND SPVL.HasSauce = 'Yes'
                        ) > 0
                        THEN 'Yes'
                        ELSE 'No'
                    END
                ) AS IsSauce";

        $SqlFieldsList = "POD.PoID,
                            POD.PoNo,
                            POD.InvNumFull,
                            SCI.CustomsInvNo as CInvNo,
                            SCI.CInvDate,
                            SCI.StockOutDate,
                            SCSL.name AS CountryName,
                            SCD.ConsigneeName,SCD.CustomPoUrl, ScID.FDAContainerStatus $CancelStatus, $SauceQry";

        $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCountriesList as SCSL on SCSL.id=POD.DestinationCountry 
        left join $TableName as SCI on SCI.PiID=POD.PoID
        LEFT JOIN ScrmISFDetails AS ScID ON POD.PoID=ScID.PIID
        WHERE $IQuery  $SubQuery $SQuery $Query order by SCI.CInvDate desc, SCI.CInvNo desc  $Limit";

        // exit;
        $stmt = $this->conn->prepare($SqlCustomInvoicesQuery);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);
        // SELECT PIID ,FDAContainerStatus FROM ScrmISFDetails ORDER BY IsfID DESC;
        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $AryCustomData = [];
        // $i = 0;
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {


            $PoID = $dataRow['PoID'];

            // 13-05-2024 for commercial check
            $sqlCommercialCheck = "SELECT COUNT(*) as Count  FROM ScrmCommercialData WHERE PoID='$PoID'";
            $stmtCommercialCheck = $this->conn->prepare($sqlCommercialCheck);
            $stmtCommercialCheck->execute();
            $commCount = $stmtCommercialCheck->fetch(PDO::FETCH_ASSOC);
            $rowCount = $commCount['Count'];
            if ($rowCount == 0) {
                $PopupStatus = 'Show';
            } else {
                $PopupStatus = 'dontShow';
            }
            // 13-05-2024 for commercial check

            $rejectedStatusArray = ["Rejected", "Cancelled", "Destroyed", "Container ROB", "Return Container"];
            $FDAContainerStatus = $dataRow['FDAContainerStatus'];
            if ($FDAContainerStatus != '' && in_array($FDAContainerStatus, $rejectedStatusArray)) {
                $PoStatus = $dataRow['FDAContainerStatus'];
                $CLR = 'red';
            } else if ($InvType == 7 || $InvType == 8) {
                $PoStatus = $dataRow['CustomsInvoiceStatus'];
                $CLR = 'blue';
            } else if ($dataRow['StockOutDate'] != '') {
                $PoStatus = "Stock Out";
                $StockOutDate = date_create($dataRow['StockOutDate']);

                $PoStatus = date_format($StockOutDate, "d-m-Y");
                $CLR = "Green";
            } else {
                $PoStatus = 'Pending';
                $CLR = "red";
            }


            if ($dataRow['CustomPoUrl'] != '') {
                $CustomPoUrl = 'Reports/' . $dataRow['CustomPoUrl'];
            } else {
                $CustomPoUrl = '';
            }
            $ArrayCustomDetails['SNO'] = ++$i;
            $ArrayCustomDetails['ID'] = $dataRow['PoID'];
            $ArrayCustomDetails['InvoiceNO'] = $dataRow['CInvNo'];
            $date = date_create($dataRow['CInvDate']);
            $ArrayCustomDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayCustomDetails['PoNO'] = $dataRow['PoNo'];
            $ArrayCustomDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayCustomDetails['PInvoiceNO'] = $dataRow['InvNumFull'];
            $ArrayCustomDetails['CountryName'] = $dataRow['CountryName'];
            $ArrayCustomDetails['StockStatus'] = $PoStatus;
            $ArrayCustomDetails['Color'] = $CLR;
            $ArrayCustomDetails['IsSauce'] = $dataRow['IsSauce'];
            $ArrayCustomDetails['CustomPoUrl'] = $CustomPoUrl;
            $ArrayCustomDetails['PopUpStatus'] = $PopupStatus;
            $AryCustomData[] = $ArrayCustomDetails;
        }
        if (count($AryCustomData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $data['Error'] = $this->Error;
        $data['Msg'] = $this->Msg;
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "AddCustomInvoice.php?backurl=AddCustomInvoice&PIID=";
        $data['DownloadExcel'] = "Reports/DocProformaExcel.php?id=";
        $data['Data'] = $AryCustomData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        return $data;
    }

    function postCancelCustoms($RequestBody)
    {
        $PiID = $RequestBody['PiID'];
        $CustomsInvoiceStatus = $RequestBody['CustomsInvoiceStatus'];
        $ReasonforCancellation = $RequestBody['ReasonforCancellation'];
        $existingCustomInvoice = $this->auditLogger->fetchRowById('ScrmCustomInvoices', 'PiID', $PiID);

        $sqlSelectRecord = "SELECT CInvID, PiID, CInvNo, CustomsInvNo, PInvFull,
        CInvDate, CInvCode, IGST, Discount, BankID, NoOfDays, 
        ExporterID, AdvanceAmount, OverSeasCommission, Description, 
        CIDescription, NameofRepresentative, PackDescription, QCertificateNo, 
        ContainerNo, ProductTypes, USClearance, PaymentType, ByRoad, IRNNumber,
        ClearenceCharges, TotalNetWt, TotalGrossWt, Duty, Freight, 
        ExRate, StockOutDate, Vessel, VoyageNo, Remarks, CIDescriptionspl,
        ShippingLineID, Note, CodeType, ByRoadDate, LinerSealNo, CustomSealNo,
        DriverName, VehicelNumber FROM ScrmCustomInvoices 
        WHERE  PiID='$PiID'";

        $stmtSelectRecord = $this->conn->prepare($sqlSelectRecord);
        $stmtSelectRecord->execute();
        $dataRecord = $stmtSelectRecord->fetch(PDO::FETCH_ASSOC);

        // var_dump($dataRecord);
        // $errorInfo = $stmtSelectRecord->errorInfo();
        // var_dump($errorInfo);
        // var_dump($dataRecord);
        // exit;
        // $CInvID = $dataRecord['CInvID'];
        $PiID = $dataRecord['PiID'];
        $CInvNo = $dataRecord['CInvNo'];
        $CustomsInvNo = $dataRecord['CustomsInvNo'];
        $PInvFull = $dataRecord['PInvFull'];
        $CInvDate = $dataRecord['CInvDate'];
        $CInvCode = $dataRecord['CInvCode'];
        $IGST = $dataRecord['IGST'];
        $Discount = $dataRecord['Discount'];
        $BankID = $dataRecord['BankID'];
        $NoOfDays = $dataRecord['NoOfDays'];
        $ExporterID = $dataRecord['ExporterID'];
        $AdvanceAmount = $dataRecord['AdvanceAmount'];
        $OverSeasCommission = $dataRecord['OverSeasCommission'];
        $Description = $dataRecord['Description'];
        $CIDescription = $dataRecord['CIDescription'];
        $NameofRepresentative = $dataRecord['NameofRepresentative'];
        $PackDescription = $dataRecord['PackDescription'];
        $QCertificateNo = $dataRecord['QCertificateNo'];
        $ContainerNo = $dataRecord['ContainerNo'];
        $ProductTypes = $dataRecord['ProductTypes'];
        $USClearance = $dataRecord['USClearance'];
        $PaymentType = $dataRecord['PaymentType'];
        $ByRoad = $dataRecord['ByRoad'];
        $IRNNumber = $dataRecord['IRNNumber'];
        $ClearenceCharges = $dataRecord['ClearenceCharges'];
        $TotalNetWt = $dataRecord['TotalNetWt'];
        $TotalGrossWt = $dataRecord['TotalGrossWt'];
        $Duty = $dataRecord['Duty'];
        $Freight = $dataRecord['Freight'];
        $ExRate = $dataRecord['ExRate'];
        $StockOutDate = $dataRecord['StockOutDate'];
        $Vessel = $dataRecord['Vessel'];
        $VoyageNo = $dataRecord['VoyageNo'];
        $Remarks = $dataRecord['Remarks'];
        $CIDescriptionspl = $dataRecord['CIDescriptionspl'];
        $ShippingLineID = $dataRecord['ShippingLineID'];
        $Note = $dataRecord['Note'];
        $CodeType = $dataRecord['CodeType'];
        $ByRoadDate = $dataRecord['ByRoadDate'];
        $LinerSealNo = $dataRecord['LinerSealNo'];
        $CustomSealNo = $dataRecord['CustomSealNo'];
        $DriverName = $dataRecord['DriverName'];
        $VehicelNumber = $dataRecord['VehicelNumber'];

        $statusMap = [
            "Customs Cancelled" => ["ScrmCancelledCustomInvoices", "Cancel", "ReasonforCancellation"],
            "Customs Deleted" => ["ScrmDeletedCustomInvoices", "Delete", "ReasonforDelete"]
        ];
        if (isset($CustomsInvoiceStatus) && isset($statusMap[$CustomsInvoiceStatus])) {
            $ScrmTableName = $statusMap[$CustomsInvoiceStatus][0];
            $alertMsg = $statusMap[$CustomsInvoiceStatus][1];
            $reasonfieldName = $statusMap[$CustomsInvoiceStatus][2];
        }

        $sqlInsert = "INSERT INTO $ScrmTableName ( PiID, CInvNo, CustomsInvNo, PInvFull, CInvDate, CInvCode, IGST, Discount, 
        BankID, NoOfDays, ExporterID, AdvanceAmount, OverSeasCommission, Description, CIDescription, NameofRepresentative, PackDescription, 
        QCertificateNo, ContainerNo, ProductTypes, USClearance, PaymentType, ByRoad, IRNNumber, ClearenceCharges, TotalNetWt, TotalGrossWt, 
        Duty, Freight, ExRate, StockOutDate, Vessel, VoyageNo, Remarks, CIDescriptionspl, ShippingLineID, Note, CodeType, ByRoadDate, 
        LinerSealNo, CustomSealNo, DriverName, VehicelNumber, CustomsInvoiceStatus, ReasonforCancellation) 
        VALUES ('$PiID','$CInvNo','$CustomsInvNo','$PInvFull','$CInvDate','$CInvCode','$IGST','$Discount','$BankID','$NoOfDays','$ExporterID',
        '$AdvanceAmount','$OverSeasCommission','$Description','$CIDescription','$NameofRepresentative','$PackDescription','$QCertificateNo',
        '$ContainerNo','$ProductTypes','$USClearance','$PaymentType','$ByRoad','$IRNNumber','$ClearenceCharges','$TotalNetWt','$TotalGrossWt',
        '$Duty','$Freight','$ExRate','$StockOutDate','$Vessel','$VoyageNo','$Remarks','$CIDescriptionspl','$ShippingLineID','$Note','$CodeType',
        '$ByRoadDate','$LinerSealNo','$CustomSealNo','$DriverName','$VehicelNumber','$CustomsInvoiceStatus','$ReasonforCancellation')";


        error_log("✅ sqlInsert " . print_r($sqlInsert, true));


        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        // $errorInfo = $stmtInsert->errorInfo();
        // var_dump($errorInfo);

        if ($stmtInsert) {
            $sqlDelete = "DELETE FROM ScrmCustomInvoices WHERE PiID='$PiID'";
            $stmtDelete = $this->conn->prepare($sqlDelete);
            $stmtDelete->execute();

            if ($sqlDelete) {
                $archivedInvoice = $this->auditLogger->fetchRowById($ScrmTableName, 'PiID', $PiID);
                if (!empty($archivedInvoice)) {
                    $this->auditLogger->logInsert($ScrmTableName, $PiID, $archivedInvoice, $RequestBody, [
                        'ReferenceNo' => $PiID,
                        'Remarks' => 'Custom invoice moved to ' . $CustomsInvoiceStatus,
                    ]);
                }
                if (!empty($existingCustomInvoice)) {
                    $this->auditLogger->logDelete('ScrmCustomInvoices', $PiID, $existingCustomInvoice, $RequestBody, [
                        'ReferenceNo' => $PiID,
                        'Remarks' => 'Custom invoice removed from active list due to ' . $CustomsInvoiceStatus,
                    ]);
                }
                $this->Error = null;
                $this->Msg = "Customs $alertMsg Successful";
            } else {
                $this->Error = "Unable to $alertMsg the Customs";
                $this->Msg = "Unable to $alertMsg the Customs";
            }
        } else {
            $this->Error = "Unable to insert the Data";
            $this->Error = "Unable to insert the Data";
        }

        $data['Error'] = $this->Error;
        $data['Msg'] = $this->Msg;
        $data = $this->GetCustomsListDetails($RequestBody);

        return $data;
    }
}
