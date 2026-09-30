<?php

include_once '../Global/Util.php';
include_once '../App/Controller/AppUtilController.php';
//include_once 'Controller/UtilController.php';

class CustomInvoicesController
{
    private $conn;
    private $db_table = "ScrmCustomInvoices";
    private $appUtilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->appUtilController = new AppUtilController($this->conn);
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
        $UserID = $RequestBody['UserID'];
        $procID="";

        $aryUserData = $this->appUtilController->getUserRoleID($UserID);
       
       
        if($aryUserData['UnitID']!=0){
           $procID= $aryUserData['UnitID'];
           
            $SqlProcessors = "SELECT GROUP_CONCAT(ExpID) as procID FROM `AdminExporterDetails` WHERE ExpApprovalNo in (SELECT ExpApprovalNo FROM `AdminExporterDetails` WHERE ExpID= $procID)";   
            $stmt = $this->conn->prepare($SqlProcessors);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 
            $procID = $dataRow['procID'];
        }
       
        $Query = '';
        $SubQuery = '';
        $SQuery = '';
        $IQuery = '';

        $DateStart = $Year . '-04-01';
        $DateEnd = ($Year + 1) . '-03-31';
        $DateFilter = "between '$DateStart' and '$DateEnd'";

        if ($procID != "") {
            $Query .= 'and POD.ProcessorID in (' . $procID . ')';
        }
        
        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ConsigneeID != "") {
            $Query .= ' and POD.ConsigneeID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and POD.DestinationCountry=' . $CountryID;
        }

        if($InvType=="0"){

            $IQuery=' POD.PoID  not in (SELECT DISTINCT(InvID) FROM `QcCodeList` )';
            }
            else if($InvType=="1"){
            $IQuery=" POD.PoDate $DateFilter and POD.PoID  in 
            (SELECT DISTINCT(InvID) FROM `QcCodeList` )";	
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

        $SqlCustomCount = "SELECT count(*) as Total  FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        WHERE $IQuery  $SubQuery $SQuery $Query ";

        $stmt = $this->conn->prepare($SqlCustomCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $SqlFieldsList = "POD.PoID,
                            POD.PoNo,
                            POD.LotNo as InternalLotNo,
                            POD.InvNumFull,
                            SCI.CustomsInvNo as CInvNo,
                            SCI.CInvDate,
                            SCI.StockOutDate,
                            SCD.ConsigneeName";

        $SqlCustomInvoicesQuery = "SELECT $SqlFieldsList FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        WHERE $IQuery  $SubQuery $SQuery $Query order by SCI.CInvNo desc  $Limit";

        $stmt = $this->conn->prepare($SqlCustomInvoicesQuery);
        $stmt->execute();

        $AryCustomData = [];
        // $i = 0;
        $i =$LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($dataRow['StockOutDate'] != '') {
                $PoStatus = "Stock Out";
                $StockOutDate = date_create($dataRow['StockOutDate']);

                $PoStatus = date_format($StockOutDate, "d-m-Y");
                $CLR = "Green";
            } else {
                $PoStatus = 'Pending';
                $CLR = "red";
            }

            $ArrayCustomDetails['SNO'] = ++$i;
            $ArrayCustomDetails['ID'] = $dataRow['PoID'];
            $ArrayCustomDetails['InvoiceNO'] = $dataRow['CInvNo'];
            $date = date_create($dataRow['CInvDate']);
            $ArrayCustomDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayCustomDetails['PoNO'] = $dataRow['PoNo'];
            $ArrayCustomDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayCustomDetails['PInvoiceNO'] = $dataRow['InvNumFull'];
            $ArrayCustomDetails['InternalLotNo'] = $dataRow['InternalLotNo'];
            $ArrayCustomDetails['StockStatus'] = $PoStatus;
            $ArrayCustomDetails['Color'] = $CLR;
            $AryCustomData[] = $ArrayCustomDetails;

        }
        if (count($AryCustomData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        if(isset($RequestBody['qc']) && $RequestBody['qc']=='T'){
            $data['EditLink'] = "add-documents?PIID=";
        }else{
            $data['EditLink'] = "AddCustomInvoice.php?PIID=";
        }
        //echo  $RequestBody['qc'];
        $data['EditLink'] = "DocAddReports?PoID=";
        $data['DownloadExcel'] = "Reports/DocProformaExcel.php?id=";
        $data['Data'] = $AryCustomData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        return $data;
    }

}
?>