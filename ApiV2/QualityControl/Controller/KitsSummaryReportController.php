<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class KitsSummaryReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);

    }

    public function getKitsReportDetails($RequestBody)
    {

        $SNO = 0;
        $KitSummaryData = $this->getParameterData($RequestBody, $SNO);

        $response['Error'] = null;
        $response['Msg'] = 'Success';
        $response['KitSummaryData'] = $KitSummaryData;


        return $response;
    }

    public function getParameterData($RequestBody, &$SNO)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DateFilter = "  KitsOrderedDate between '$FromDate' and '$ToDate'";

        $sqlParameter = "SELECT Parameter,KitsOrderedDate FROM QcKitsDetails WHERE $DateFilter and Parameter != '' GROUP BY Parameter,KitsOrderedDate order by KitsOrderedDate asc";
        $stmtParameter = $this->conn->prepare($sqlParameter);
        $stmtParameter->execute();

        while ($dataRowParameter = $stmtParameter->fetch(PDO::FETCH_ASSOC)) {
            $Parameter = $dataRowParameter['Parameter'];
            $KitsOrderedDate = $dataRowParameter['KitsOrderedDate'];
            $ArrayKitSummaryData[] = $this->getData($RequestBody, $Parameter, $SNO, $KitsOrderedDate);


        }
        return $ArrayKitSummaryData;
    }

    public function getData($RequestBody, $Parameter, &$SNO, $KitsOrderedDate)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DateFilter = "   KitsOrderedDate between '$FromDate' and '$ToDate'";

        $sqlFields = "QKD.KitID,QKD.KitsOrderedDate,QKD.KitsReceivedDate,QKD.InvoiceNo,  QKD.Parameter, 
                      QKD.ManufactureDate, QKD.ExpiryDate, NoOfKitsReceived,NoOfKitsOrdered";



        // $sqlQuery = "SELECT $sqlFields FROM QcKitsDetails AS QKD   where $DateFilter and QKD.Parameter='$Parameter'";
        $sqlQuery = "SELECT $sqlFields FROM QcKitsDetails AS QKD   where KitsOrderedDate='$KitsOrderedDate' and QKD.Parameter='$Parameter'";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();


        // $ArrayData = [];
        // $i = 0;
        $ArrayData['SNO'] = ++$SNO;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // $ArrayData['SNO'] = ++$SNO;
            $ArrayData['Parameter'] = $dataRow['Parameter'];
            $ArrayData['KitID'] = $dataRow['KitID'];
            $ArrayData['KitsOrderedDate'] = $this->utilController->getFormatedDateLines($dataRow['KitsOrderedDate']);
            $ArrayData['KitsReceivedDate'] = $this->utilController->getFormatedDateLines($dataRow['KitsReceivedDate']);
            $ArrayData['InvoiceNo'] = $dataRow['InvoiceNo'];
            $ArrayData['ManufactureDate'] = $this->utilController->getFormatedDateLines($dataRow['ManufactureDate']);
            $ArrayData['ExpiryDate'] = $this->utilController->getFormatedDateLines($dataRow['ExpiryDate']);
            $ArrayData['NoOfKitsReceived'] = $dataRow['NoOfKitsReceived'];
            $ArrayData['NoOfKitsOrdered'] = $dataRow['NoOfKitsOrdered'];
            $ArrayData['BalanceKits'] = $this->getBalanceKits($dataRow['KitID']);

            $ArrayData['BreakUpData'] = $this->getAbByParameter($dataRow['KitID']);
            // $ArrayKitData[] = $ArrayData;

        }
        return $ArrayData;
    }

    public function getBalanceKits($KitID)
    {

        $sqlBalanceKits = "SELECT COUNT(*) as BalanceKits FROM `QcKitsBreakupDetails`WHERE KitID='$KitID'and BalanceWells!=0"; 

        $stmtBalanceKits = $this->conn->prepare($sqlBalanceKits);
        $stmtBalanceKits->execute();

       $dataRowBalanceKits = $stmtBalanceKits->fetch(PDO::FETCH_ASSOC);
       $BalanceKits=$dataRowBalanceKits['BalanceKits'];

 return $BalanceKits;
    }

    public function getAbByParameter($KitID)
    {
        // $sqlFields = "KitBreakupLotNo,QKD.KitID, QKD.KitsOrderedDate, QKD.KitsReceivedDate, QKD.KitsLotNo, 
        //               QKD.NoOfKitsOrdered, QKD.NoOfKitsReceived, QKD.InvoiceNo, 
        //               QKD.ManufactureDate, QKD.ExpiryDate, QKBD.BalanceWells, QKBD.NoOfWells, QKBD.UtilizedWells, QKD.Parameter";
        $sqlFields = "KitBreakupID,KitBreakupLotNo, QKBD.BalanceWells, QKBD.NoOfWells, QKBD.UtilizedWells,QKBD.DamagedWells,KitOpeningDate";

        $sqlBreakUpDetails = "SELECT $sqlFields FROM QcKitsBreakupDetails AS QKBD  WHERE QKBD.KitID = '$KitID' ";

        $stmtBreakUpDetails = $this->conn->prepare($sqlBreakUpDetails);
        $stmtBreakUpDetails->execute();

        while ($dataRowBreakUpDetails = $stmtBreakUpDetails->fetch(PDO::FETCH_ASSOC)) {
            $KitBreakupID = $dataRowBreakUpDetails['KitBreakupID'];
            // echo $KitID;

            $sqlAntibioticDetails = "SELECT distinct QLTA.LbuName as AnalyzedBy, QLTA.LbuName as ReceivedBy , 
QLTV.LbuName as VerifiedBy , QRAR.Remarks , QRAR.Shift FROM QcRmAbReport AS QRAR 
 LEFT JOIN QcLabTechnologist AS QLTA ON QLTA.LUID = QRAR.AnalyzedBy 
                     LEFT JOIN QcLabTechnologist AS QLTR ON QLTR.LUID = QRAR.ReceivedBy 
                     LEFT JOIN QcLabTechnologist AS QLTV ON QLTV.LUID = QRAR.VerifiedBy 
                     WHERE QRAR.KitBreakupID = '$KitBreakupID' and KitID='$KitID' ";

            $stmtAntibioticDetails = $this->conn->prepare($sqlAntibioticDetails);
            $stmtAntibioticDetails->execute();
            $dataRowAntibioticDetails = $stmtAntibioticDetails->fetch(PDO::FETCH_ASSOC);

            // $ArrayBreakUpData['NoOfKitsOrdered'] = $dataRowBreakUpDetails['NoOfKitsOrdered'];
            // $ArrayBreakUpData['NoOfKitsReceived'] = $dataRowBreakUpDetails['NoOfKitsReceived'];
            // $ArrayBreakUpData['KitsReceivedDate'] = $dataRowBreakUpDetails['KitsReceivedDate'];
            $ArrayBreakUpData['KitBreakupLotNo'] = $dataRowBreakUpDetails['KitBreakupLotNo'];
            $ArrayBreakUpData['KitOpeningDate'] = $this->utilController->getFormatedDateLines($dataRowBreakUpDetails['KitOpeningDate']);
            $ArrayBreakUpData['NoOfWells'] = $dataRowBreakUpDetails['NoOfWells'];
            $ArrayBreakUpData['UtilizedWells'] = $dataRowBreakUpDetails['UtilizedWells'];
            $ArrayBreakUpData['BalanceWells'] = $dataRowBreakUpDetails['BalanceWells'];
            $ArrayBreakUpData['AnalyzedBy'] = $dataRowAntibioticDetails['AnalyzedBy'];
            $ArrayBreakUpData['ReceivedBy'] = $dataRowAntibioticDetails['ReceivedBy'];
            $ArrayBreakUpData['VerifiedBy'] = $dataRowAntibioticDetails['VerifiedBy'];
            $ArrayBreakUpData['Remarks'] = $dataRowAntibioticDetails['Remarks'];
            $ArrayBreakUpData['Shift'] = $dataRowAntibioticDetails['Shift'];
            $ArrayBreakUpData['DamagedWells'] = $dataRowBreakUpDetails['DamagedWells'];

            $AryBreakUpData[] = $ArrayBreakUpData;
        }
        return $AryBreakUpData;
    }
}