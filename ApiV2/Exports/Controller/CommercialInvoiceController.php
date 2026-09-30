<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class CommercialInvoiceController
{
    private $conn;
    private $db_table = "ScrmCommercialData";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function GetCommercialListDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Year = $RequestBody['Year'];
        $ExporterID = $RequestBody['ExporterID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $CountryID = $RequestBody['CountryID'];
        $InvType = $RequestBody['InvType'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $SearchKey = $RequestBody['SearchKey'];
        $ClientID = $RequestBody['ClientID'];


        $Query = '';
        $IQuery = '';


        if ($ExporterID != "") {
            $Query .= ' and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ConsigneeID != "") {
            $Query .= ' and POD.ConsigneeID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and POD.DestinationCountry=' . $CountryID;
        }

        $DateStart = $Year . '-04-01';
        $DateEnd = ($Year + 1) . '-03-31';
        $DateFilter = "between '$DateStart' and '$DateEnd'";



        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', ConsigneeName,PoNo,InvNumFull,CustomsInvNo) LIKE '%$SearchKey%'";
        }

        if ($InvType == "0") {
            $IQuery .= " SCI.CInvDate $DateFilter and SCI.PiID not in (SELECT PoID FROM `ScrmCommercialData`) and POD.Status not in (2,3,4)";
        } else if ($InvType == "1") {
            $IQuery .= " SCI.CInvDate $DateFilter and SCI.PiID  in 
                (SELECT PoID FROM `ScrmCommercialData`) and  POD.Status not in (2,3,4)";
        } else if ($InvType == "4") {
            $IQuery = " SCI.CInvDate $DateFilter and POD.PoID  in 
                (SELECT PiID FROM `ScrmCustomInvoices` ) AND POD.Status=4";
        }

        $SqlCommericalCount = "SELECT count(*) as Total FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        WHERE $IQuery  $Query";

        $stmt = $this->conn->prepare($SqlCommericalCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFieldsList = "POD.PoID,
                            POD.PoNo,
                            POD.InvNumFull,
                            SCI.CustomsInvNo as CInvNo,
                            SCI.CInvDate,
                            SCI.StockOutDate,
                            SCOM.CommercialID,
                            SCD.ConsigneeName, ScID.FDAContainerStatus";

        // $SqlCommercialInvoiceQuery = "SELECT $SqlFieldsList FROM ScrmPoData as POD 
        // left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        // left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        // left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
        // WHERE $IQuery  $Query order by SCI.CInvDate desc,SCI.CInvNo desc   $Limit";  

        $SqlCommercialInvoiceQuery = "SELECT $SqlFieldsList FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        left join ScrmCommercialData as SCOM on POD.PoID=SCOM.PoID 
        LEFT JOIN ScrmISFDetails AS ScID ON POD.PoID=ScID.PIID
        WHERE $IQuery  $Query order by SCI.CInvDate desc,SCI.CInvNo desc   $Limit";

        $stmt = $this->conn->prepare($SqlCommercialInvoiceQuery);
        $stmt->execute();

        $AryCommercialData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rejectedStatusArray = ["Rejected", "Cancelled", "Destroyed", "Container ROB", "Return Container"];
            $FDAContainerStatus = $dataRow['FDAContainerStatus'];
            if ($FDAContainerStatus != '' && in_array($FDAContainerStatus, $rejectedStatusArray)) {
                $PoStatus = $dataRow['FDAContainerStatus'];
                $CLR = 'red';
            } else if ($dataRow['CommercialID'] == '') {
                $PoStatus = 'Pending';
                $CLR = 'red';
            } else if ($dataRow['CommercialID'] != '') {
                $PoStatus = 'Completed';
                $CLR = 'Green';
            }

            $ArrayCommercialDetails['SNO'] = ++$i;
            $ArrayCommercialDetails['ID'] = $dataRow['PoID'];
            $ArrayCommercialDetails['InvoiceNO'] = $dataRow['CInvNo'];
            $date = date_create($dataRow['CInvDate']);
            $ArrayCommercialDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayCommercialDetails['PoNO'] = $dataRow['PoNo'];
            $ArrayCommercialDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayCommercialDetails['PInvoiceNO'] = $dataRow['InvNumFull'];
            $ArrayCommercialDetails['Status'] = $PoStatus;
            $ArrayCommercialDetails['Color'] = $CLR;
            $AryCommercialData[] = $ArrayCommercialDetails;
        }
        if (count($AryCommercialData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        //$aryActionLinks=$this->getActionLinks();

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = 'AddCommercialInvoice?PIID=';
        if ($ClientID == 'DFLV3') {
            $data['CommercialInvoicePrintLink'] = 'Reports/DocComInvoice.php?id=';
            $data['PackingInvoicePrintLink'] = 'Reports/DocPackingList.php?id=';
        } else {
            $data['CommercialInvoicePrintLink'] = '';
            $data['PackingInvoicePrintLink'] = '';
        }

        $data['ViewReportsLink'] = 'DocAddReports.php?PoID=';
        $data['Data'] = $AryCommercialData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;


        return $data;
    }
    /*function getActionLinks(){
        $PrintLink='Reports/DocCommercialInvoicePrint.php?id=';
       
        try {  
         $SqlCommercialQuery = "SELECT * FROM `ScrmReports` where ReportCode='PI'"; 
          $stmt = $this->conn->prepare($SqlCommercialQuery);
          $stmt->execute();
            if($stmt != FALSE){
                $RowReport = $stmt->fetch(PDO::FETCH_ASSOC);
                $PrintLink=$RowReport['Path'].$RowReport['PrintLink'].$RowReport['QueryID'].'=';
            }
            }catch(Exception $e){
            }    
            if($PrintLink==''){
                $PrintLink='Reports/DocCommercialInvoicePrint.php?id=';
            }

            $aryActionLinks['EditLink']='AddCommercialInvoice?PIID=';
            $aryActionLinks['PrintLink']=$PrintLink;
            $aryActionLinks['PrintLink']=$PrintLink;
            return $aryActionLinks;
    }*/
}
