<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

session_start();

class ContainerReturnDetailsController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postReturnContainerDetails($RequestBody)
    {
        // $ReturnID = $RequestBody['ReturnID'];
        $PoID = $RequestBody['PoID'];
        $ReceivedDate = $RequestBody['ReceivedDate'];
        $ReturnBLNo = $RequestBody['ReturnBLNo'];
        $ReturnContainerNo = $RequestBody['ReturnContainerNo'];
        $ContainerWeight = $RequestBody['ContainerWeight'];
        $ContainerReturnPort = $RequestBody['ContainerReturnPort'];
        $BillofEntryNo = $RequestBody['BillofEntryNo'];
        $BillofEntryDate = $RequestBody['BillofEntryDate'];        
        $Remarks = $RequestBody['Remarks'];        

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        $sqlCount = "SELECT count(*) AS Count FROM ScrmCotainerReturnDetails WHERE PoID='$PoID'";

        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();

        $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);

        $Count = $rowCount['Count'];

        // echo $Count;
        // exit;

        if ($Count == 0) {

            $sqlInsert = "INSERT INTO ScrmCotainerReturnDetails(PoID, ReturnContainerStockReceivedDate, ReturnBLNo, ReturnContainerNo,
            ReturnContainerNetWeightKgs, ContainerReturnPort, BillofEntryNo, BillofEntryDate, Remarks, PostedDate) 
            VALUES ('$PoID','$ReceivedDate','$ReturnBLNo','$ReturnContainerNo','$ContainerWeight','$ContainerReturnPort','$BillofEntryNo',
            '$BillofEntryDate','$Remarks','$PostedDate')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $lastInsertedId = $this->conn->lastInsertId();
                $createdReturn = $this->auditLogger->fetchRowById('ScrmCotainerReturnDetails', 'ReturnID', $lastInsertedId);
                $this->auditLogger->logInsert('ScrmCotainerReturnDetails', $lastInsertedId, $createdReturn, $RequestBody, [
                    'ReferenceNo' => $PoID,
                    'Remarks' => 'Export container return created',
                ]);
                $this->Error = null;
                $this->Msg = 'Return Container Posted Successfully';
            }
        } else {
            $existingReturn = $this->auditLogger->fetchRowsByConditions('ScrmCotainerReturnDetails', ['PoID' => $PoID]);
            $existingReturnRow = count($existingReturn) > 0 ? $existingReturn[0] : null;
            $sqlUpdate = "UPDATE ScrmCotainerReturnDetails SET ReturnContainerStockReceivedDate='$ReceivedDate',
            ReturnBLNo='$ReturnBLNo',ReturnContainerNo='$ReturnContainerNo',ReturnContainerNetWeightKgs='$ContainerWeight',
            ContainerReturnPort='$ContainerReturnPort',BillofEntryNo='$BillofEntryNo',BillofEntryDate='$BillofEntryDate',
            Remarks='$Remarks',PostedDate='$PostedDate'
            WHERE PoID='$PoID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedReturn = $this->auditLogger->fetchRowsByConditions('ScrmCotainerReturnDetails', ['PoID' => $PoID]);
                $updatedReturnRow = count($updatedReturn) > 0 ? $updatedReturn[0] : null;
                $recordId = $updatedReturnRow && isset($updatedReturnRow['ReturnID']) ? $updatedReturnRow['ReturnID'] : $PoID;
                $this->auditLogger->logUpdate('ScrmCotainerReturnDetails', $recordId, $existingReturnRow, $updatedReturnRow, $RequestBody, [
                    'ReferenceNo' => $PoID,
                    'Remarks' => 'Export container return updated',
                ]);
                $this->Error = null;
                $this->Msg = 'Return Container Updated Successfully';
            }
        }
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $getData = $this->getReturnContainerDetails($RequestBody);
        $Data['Data'] = $getData['Data'];

        return $Data;
    }


    // function deleteReturnContainer($RequestBody)
    // {
    //     $ReturnID = $RequestBody['ReturnID'];

    //     $sqlDelete = "DELETE FROM `ScrmCotainerReturnDetails` WHERE ReturnID='$ReturnID'";

    //     $stmtUpdate = $this->conn->prepare($sqlDelete);
    //     $stmtUpdate->execute();

    //     if ($stmtUpdate) {
    //         $CountryData = $this->getCountryDetails($RequestBody);
    //         $Data['Error'] = null;
    //         $Data['Msg'] = 'Return Container Deleted Successfully';
    //         $Data['Data'] = $CountryData['Data'];
    //         return $Data;
    //     } else {
    //         $Data['Error'] = 'Unable to Delete Data';
    //         $Data['Msg'] = $this->Error;
    //         return $Data;
    //     }
    // }

    function getReturnContainerDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $PoID = $RequestBody['PoID'];
        $Year = $RequestBody['Year'];
        $ExporterID = $RequestBody['ExporterID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $PortID = $RequestBody['PortID'];
        $ReturnStatus = $RequestBody['ReturnStatus'];
        $ReturnID = $RequestBody['ReturnID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $DateStart = $Year . '-04-01';
        $DateEnd = ($Year + 1) . '-03-31';
        $DateFilter = "between '$DateStart' and '$DateEnd'";

        $Query = '';

        if ($Year != "") {
            $Query .= " and CInvDate $DateFilter";
        }

        if ($ExporterID != "") {
            $Query .= " and SPD.ExporterID = '$ExporterID'";
        }
        if ($ConsigneeID != "") {
            $Query .= " and SPD.ConsigneeID = '$ConsigneeID'";
        }
        if ($PortID != "") {
            $Query .= " and SCRD.ContainerReturnPort = '$PortID'";
        }
        if ($ReturnStatus != "") {
            $Query .= " and  ISF.FDAContainerStatus= '$ReturnStatus'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', SPD.PoID ,CustomsInvNo,PoNo,FDAContainerStatus,ReturnContainerStockReceivedDate,ReturnBLNo,ReturnContainerNo,
            ReturnContainerNetWeightKgs,ContainerReturnPort,BillofEntryNo,BillofEntryDate,SCRD.Remarks,SPD.ExporterID,SPD.ConsigneeID) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) as Total FROM ScrmPoData AS SPD
        LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID=SPD.PoID
        LEFT JOIN ScrmCotainerReturnDetails AS SCRD ON SCRD.PoID=SPD.PoID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
        WHERE SPD.Status=4 $Query ";

        $stmt = $this->conn->prepare($sqlCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "SPD.PoID ,CustomsInvNo,PoNo,FDAContainerStatus,ReturnContainerStockReceivedDate,ReturnBLNo,ReturnContainerNo,
        ReturnContainerNetWeightKgs,ContainerReturnPort,BillofEntryNo,BillofEntryDate,SCRD.Remarks,SPD.ExporterID,SPD.ConsigneeID,
        ScPC.Name as PortName,SCRD.ReturnID";

        if ($PoID != '') {

            $sqlQuery = "SELECT $sqlFields FROM ScrmPoData AS SPD
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID=SPD.PoID
            LEFT JOIN ScrmCotainerReturnDetails AS SCRD ON SCRD.PoID=SPD.PoID
            LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
            LEFT JOIN ScrmPortCities AS ScPC ON ScPC.PortId=SCRD.ContainerReturnPort
            WHERE SCRD.PoID='$PoID'";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM ScrmPoData AS SPD
            LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID=SPD.PoID
            LEFT JOIN ScrmCotainerReturnDetails AS SCRD ON SCRD.PoID=SPD.PoID
            LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
            LEFT JOIN ScrmPortCities AS ScPC ON ScPC.PortId=SCRD.ContainerReturnPort
            WHERE SPD.Status=4 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayContainerData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {


        //     SPD.PoID ,CustomsInvNo,PoNo,FDAContainerStatus,ReturnContainerStockReceivedDate,ReturnBLNo,ReturnContainerNo,
        // ReturnContainerNetWeightKgs,ContainerReturnPort,BillofEntryNo,BillofEntryDate,SCRD.Remarks,SPD.ExporterID,SPD.ConsigneeID

            $ArrayContainerList['SNO'] = ++$i;
            $ArrayContainerList['ReturnID'] = $dataRow['ReturnID'];
            $ArrayContainerList['PoID'] = $dataRow['PoID'];
            $ArrayContainerList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayContainerList['PoNo'] = $dataRow['PoNo'];
            $ArrayContainerList['FDAContainerStatus'] = $dataRow['FDAContainerStatus'];
            $ArrayContainerList['ExporterID'] = $dataRow['ExporterID'];
            $ArrayContainerList['ConsigneeID'] = $dataRow['ConsigneeID'];
            $ArrayContainerList['ReceivedDate'] = $dataRow['ReturnContainerStockReceivedDate'];
            $ArrayContainerList['ReturnBLNo'] = $dataRow['ReturnBLNo'];
            $ArrayContainerList['ReturnContainerNo'] = $dataRow['ReturnContainerNo'];
            $ArrayContainerList['NetWeightKgs'] = $dataRow['ReturnContainerNetWeightKgs'];
            $ArrayContainerList['ContainerReturnPort'] = $dataRow['ContainerReturnPort'];
            $ArrayContainerList['PortName'] = $dataRow['PortName'];
            $ArrayContainerList['BillofEntryNo'] = $dataRow['BillofEntryNo'];
            $ArrayContainerList['BillofEntryDate'] = $dataRow['BillofEntryDate'];
            $ArrayContainerList['Remarks'] = $dataRow['Remarks'];
            // $ArrayContainerList['PostedDate'] = $dataRow['PostedDate'];
            $ArrayContainerList['EditLink'] = "add-container-return-details?PoID=" . $dataRow['PoID'];
            

            $ArrayContainerData[] =  $ArrayContainerList;
        }

        if (count($ArrayContainerData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayContainerData;

        return $data;
    }
}
