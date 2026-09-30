<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class MediaKitsSummaryReportController
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
        //$Filter = "  mKitsOrderedDate between '$FromDate' and '$ToDate'";
        $Filter = "";//all
        $status  = $RequestBody['Status'];
        if(!empty($status) && $status == 1){
            $Filter = " AND mBalanceWells!=0";//pending
        }elseif(!empty($status) && $status == 2){
             $Filter = " AND mBalanceWells=0";//completed
        }

        $sqlParameter = "SELECT mParameter, mMedia, mKitsOrderedDate,mBottleWeight FROM mediakitsdetails mk
                         LEFT JOIN mediakitsbreakupdetails mbk ON mbk.mKitID=mk.`mKitID`
                         WHERE 1 $Filter and mMedia != '' GROUP BY mMedia,mKitsOrderedDate order by mKitsOrderedDate asc";
        //echo $sqlParameter; exit;
        $stmtParameter = $this->conn->prepare($sqlParameter);
        $stmtParameter->execute();

        //  echo print_r($sqlParameter);
        //     exit;

        while ($dataRowParameter = $stmtParameter->fetch(PDO::FETCH_ASSOC)) {
            $Media = $dataRowParameter['mMedia'];
            $KitsOrderedDate = $dataRowParameter['mKitsOrderedDate'];
            $ArrayKitSummaryData[] = $this->getData($RequestBody, $Media, $SNO, $KitsOrderedDate);


        }
        return $ArrayKitSummaryData;
    }

    public function getData($RequestBody, $Media, &$SNO, $KitsOrderedDate)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DateFilter = "   mKitsOrderedDate between '$FromDate' and '$ToDate'";

        $sqlFields = "QKD.mKitID,QKD.mKitsOrderedDate,QKD.mKitsReceivedDate,QKD.mInvoiceNo,  QKD.mMedia, 
                      QKD.mManufactureDate, QKD.mExpiryDate, mNoOfKitsReceived,mNoOfKitsOrdered,QKD.mBottleWeight";
        
        $sqlQuery = "SELECT $sqlFields FROM mediakitsdetails AS QKD  where mKitsOrderedDate='$KitsOrderedDate' and QKD.mMedia='$Media'";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();


        // $ArrayData = [];
        // $i = 0;
        $ArrayData['SNO'] = ++$SNO;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $mediaSql = "SELECT MediaName FROM `usedmediamaster` where MediaID='".$dataRow['mMedia']."'";    
            $medai_row = $this->conn->query($mediaSql)->fetch(PDO::FETCH_ASSOC);
            // $ArrayData['SNO'] = ++$SNO;
            $ArrayData['Media'] = $medai_row['MediaName'];
            $ArrayData['KitID'] = $dataRow['mKitID'];
            $ArrayData['KitsOrderedDate'] = $this->utilController->getFormatedDateLines($dataRow['mKitsOrderedDate']);
            $ArrayData['KitsReceivedDate'] = $this->utilController->getFormatedDateLines($dataRow['mKitsReceivedDate']);
            $ArrayData['InvoiceNo'] = $dataRow['mInvoiceNo'];
            $ArrayData['ManufactureDate'] = $this->utilController->getFormatedDateLines($dataRow['mManufactureDate']);
            $ArrayData['ExpiryDate'] = $this->utilController->getFormatedDateLines($dataRow['mExpiryDate']);
            $ArrayData['NoOfKitsReceived'] = $dataRow['mNoOfKitsReceived'];
            $ArrayData['NoOfKitsOrdered'] = $dataRow['mNoOfKitsOrdered'];
             $ArrayData['BottleWeight'] = $dataRow['mBottleWeight'];
            $ArrayData['BalanceKits'] = $this->getBalanceKits($dataRow['mKitID']);

            $ArrayData['BreakUpData'] = $this->getAbByParameter($dataRow['mKitID'],$RequestBody);
            // $ArrayKitData[] = $ArrayData;

        }
        return $ArrayData;
    }

    public function getBalanceKits($KitID)
    {

        $sqlBalanceKits = "SELECT COUNT(*) as BalanceKits FROM `mediakitsbreakupdetails` WHERE mKitID='$KitID'and mBalanceWells!=0"; 
//echo $sqlBalanceKits; exit;
        $stmtBalanceKits = $this->conn->prepare($sqlBalanceKits);
        $stmtBalanceKits->execute();

       $dataRowBalanceKits = $stmtBalanceKits->fetch(PDO::FETCH_ASSOC);
       $BalanceKits=$dataRowBalanceKits['BalanceKits'];

 return $BalanceKits;
    }

   public function getAbByParameter($KitID,$RequestBody = [])
{
    $Filter = '';
    $status  = $RequestBody['Status'];
    if(!empty($status) && $status == 1){
        $Filter = " AND mBalanceWells!=0";//pending
    } elseif(!empty($status) && $status == 2){
        $Filter = " AND mBalanceWells=0";//completed
    }


    $sqlFields = "QKBD.mKitBreakupID, QKBD.mKitBreakupLotNo, QKBD.mBalanceWells, QKBD.mNoOfWells, 
                  QKBD.mUtilizedWells, QKBD.mKitOpeningDate, mk.mBottleWeight AS BottleWeight";

    $sqlBreakUpDetails = "SELECT $sqlFields 
        FROM mediakitsbreakupdetails AS QKBD 
        LEFT JOIN mediakitsdetails mk ON QKBD.mKitID = mk.mKitID
        WHERE QKBD.mKitID = '$KitID' $Filter";

    $stmtBreakUpDetails = $this->conn->prepare($sqlBreakUpDetails);
    $stmtBreakUpDetails->execute();

    $AryBreakUpData = [];
    while ($dataRowBreakUpDetails = $stmtBreakUpDetails->fetch(PDO::FETCH_ASSOC)) {
        $ArrayBreakUpData['KitBreakupLotNo'] = $dataRowBreakUpDetails['mKitBreakupLotNo'];
        $ArrayBreakUpData['KitOpeningDate'] = $this->utilController->getFormatedDateLines($dataRowBreakUpDetails['mKitOpeningDate']);
        $ArrayBreakUpData['UtilizedWells'] = $dataRowBreakUpDetails['mUtilizedWells'];
        $ArrayBreakUpData['BalanceWells'] = $dataRowBreakUpDetails['mBalanceWells'];
        $ArrayBreakUpData['BottleWeight'] = $dataRowBreakUpDetails['BottleWeight'] ;

        $AryBreakUpData[] = $ArrayBreakUpData;
    }

    return $AryBreakUpData;

    }
}