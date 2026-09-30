<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
class ShippingBillController
{
    private $conn;
    private $db_table = "ScrmShippingBillDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function GetShippingBillDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Year = $RequestBody['Year'];
        $ExporterID = $RequestBody['ExporterID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $CountryID = $RequestBody['CountryID'];
        $ProcessorID = $RequestBody['ProcessorID'];
        $BillsType = $RequestBody['BillsType'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];



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
        if ($ProcessorID != "") {
            $Query .= ' and POD.ProcessorID=' . $ProcessorID;
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

        if ($BillsType == 0) {
            // $Query .= " and  SCI.PiID not in (SELECT InvID FROM `ScrmShippingBillDetails`) ";
            $Query .= " and SCI.CInvDate $DateFilter and  SCI.PiID not in (SELECT InvID FROM `ScrmShippingBillDetails`) ";
        } else if ($BillsType == 1) {
            $Query .= " and SCI.CInvDate $DateFilter and SCI.PiID  in (SELECT InvID FROM `ScrmShippingBillDetails`)";
        }



        $SqlShippingBillCount = "SELECT count(*) as Total FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID 
        WHERE  POD.Status not in (2,3,4,7)  $Query ";

        $stmt = $this->conn->prepare($SqlShippingBillCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFieldsList = "
		POD.PoID,
		SCD.ConsigneeName,
		POD.PoNo,	
        SCI.CustomsInvNo as CInvNo,
        SCOM.InvID,
        SCOM.ShippingBillNo,
        SCOM.ScrmID";

        $SqlShippingQuery = "SELECT $SqlFieldsList FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID 
        WHERE  POD.Status not in (2,3,4,7) $Query  order by SCI.CInvNo desc $Limit";


        $stmt = $this->conn->prepare($SqlShippingQuery);
        $stmt->execute();

        $AryShippingData = [];
        // $i = 0;
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($dataRow['ScrmID'] == '') {
                $PoStatus = 'Pending';
                $CLR = 'red';
            } else if ($dataRow['ScrmID'] != '') {
                $PoStatus = 'Completed';
                $CLR = 'Green';
            }

            $ArrayShippingDetails['SNO'] = ++$i;
            $ArrayShippingDetails['ID'] = $dataRow['PoID'];
            $ArrayShippingDetails['InvNo'] = $dataRow['CInvNo'];
            $ArrayShippingDetails['PoNo'] = $dataRow['PoNo'];
            $ArrayShippingDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayShippingDetails['ShippingBillNo'] = $dataRow['ShippingBillNo'];
            $ArrayShippingDetails['InvoiceStatus'] = $PoStatus;
            $ArrayShippingDetails['Color'] = $CLR;
            $AryShippingData[] = $ArrayShippingDetails;

        }
        if (count($AryShippingData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "ShippingBillForm?PoID=";
        $data['Data'] = $AryShippingData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }

}
?>