<?php

include_once '../Global/Util.php';
class ISFDetailsController
{
    private $conn;

    private $db_table = "ScrmISFDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;


    }
    // GET ALL
    public function GetISFDetails($RequestBody)
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
        $ClientID = isset($RequestBody['ClientID']) ? $RequestBody['ClientID'] : '';

        // $utilController = new UtilController($this->conn);

        $Query = '';
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

        $hasIsfDetails = "EXISTS (
                            SELECT 1
                            FROM ScrmISFDetails AS SID
                            WHERE SID.PIID = SCI.PiID
                        )";

        $isCompleted = $hasIsfDetails;

        if ($ClientID === 'SMEV3' && $DateStart === '2026-04-01') {
            $matchedDestinationTranshipment = "EXISTS (
        SELECT 1
        FROM ScrmTranshipmentPortsList AS STL
        INNER JOIN ScrmPortCities AS TPC
            ON TPC.Name = STL.ToTOPID
        WHERE STL.PIID = POD.PoID
          AND TPC.PortId = CASE
              WHEN POD.DeliveryTerms = 'CY/CY'
                  THEN POD.PortOfDischarge
              WHEN POD.DeliveryTerms = 'CY/SD'
                  THEN POD.FinalDestination
          END
    )";

            $isCompleted = "(
        (
            POD.DeliveryTerms IN ('CY/CY', 'CY/SD')
            AND $matchedDestinationTranshipment
        )
        OR
        (
            (
                POD.DeliveryTerms NOT IN ('CY/CY', 'CY/SD')
                OR POD.DeliveryTerms IS NULL
            )
            AND $hasIsfDetails
        )
    )";
        }

        if ($InvType === '0') {
            $IQuery = "SCI.CInvDate $DateFilter AND NOT ($isCompleted)";
        } elseif ($InvType === '1') {
            $IQuery = "SCI.CInvDate $DateFilter AND ($isCompleted)";
        }

        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ConsigneeName,PoNo,InvNumFull,CustomsInvNo) LIKE '%$SearchKey%'";
        }

        $SqlISFCount = "SELECT count(*) as Total FROM ScrmPoData as POD 
        left join ScrmISFDetails as ISF on ISF.PIID=POD.PoID
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        WHERE  $IQuery  $Query and POD.Status not in(4)";

        $stmt = $this->conn->prepare($SqlISFCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);



        $SqlFieldsList = "POD.PoID,
        POD.PoNo,
        POD.InvNumFull,
        SCI.CustomsInvNo as CInvNo,
        SCI.CInvDate,
        ISF.IsfID,
        SCD.ConsigneeName";

        $SqlISFQuery = "SELECT $SqlFieldsList FROM ScrmPoData as POD 
                        left join ScrmISFDetails as ISF on ISF.PIID=POD.PoID
                        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
                        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
                        WHERE  $IQuery  $Query and POD.Status not in (2,3,4,7) order by SCI.CInvNo desc $Limit ";

        $stmt = $this->conn->prepare($SqlISFQuery);
        $stmt->execute();

        $AryISFData = [];
        //$i = 0;
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {


            if ($InvType == "0") {
                $PoStatus = 'Pending';
                $CLR = 'red';
            } else {
                $PoStatus = 'Completed';
                $CLR = 'Green';
            }

            $ArrayISFListDetails['SNO'] = ++$i;
            $ArrayISFListDetails['ID'] = $dataRow['PoID'];
            $ArrayISFListDetails['PInvoiceNo'] = $dataRow['InvNumFull'];
            $ArrayISFListDetails['InvoiceNo'] = $dataRow['CInvNo'];
            $date = date_create($dataRow['CInvDate']);
            $ArrayISFListDetails['InvoiceDate'] = date_format($date, "d-m-Y");
            $ArrayISFListDetails['PoNO'] = $dataRow['PoNo'];
            $ArrayISFListDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayISFListDetails['Status'] = $PoStatus;
            $ArrayISFListDetails['Color'] = $CLR;
            $AryISFData[] = $ArrayISFListDetails;

        }
        if (count($AryISFData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        //$aryActionLinks = $this->getActionLinks();

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = 'AddISFForm?PIID=';
        $data['ShipmentDetailsPrintLink'] = 'Reports/DocShipmentDetailsPrint.php?id=';
        $data['TenplusTwoPrintLink'] = 'Reports/DocTenPlusTwoPrint.php?id=';
        $data['Data'] = $AryISFData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }

    // function getActionLinks()
    // {
    // $PrintLink = 'Reports/DocTenPlusTwoPrint.php?id=';

    // try {
    // $SqlProformaQuery = "SELECT * FROM `ScrmReports` where ReportCode='1'";
    // $stmt = $this->conn->prepare($SqlProformaQuery);
    // $stmt->execute();
    // if ($stmt != FALSE) {
    // $RowReport = $stmt->fetch(PDO::FETCH_ASSOC);
    // $PrintLink = $RowReport['Path'] . $RowReport['PrintLink'] . $RowReport['QueryID'] . '=';
    // }
    // } catch (Exception $e) {
    // }
    // if ($PrintLink == '') {
    // $PrintLink = 'Reports/DocProformaInvoicePrint.php?id=';
    // }

    // $aryActionLinks['PrintLink1'] = 'AddTenPlusTwoReport.php?id=';
    // $aryActionLinks['PrintLink2'] = $PrintLink;
    // return $aryActionLinks;
    // }
}
?>