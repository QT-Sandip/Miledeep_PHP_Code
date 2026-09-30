<?php

include_once '../Global/Util.php';
class ShippingLinesChargesReportController
{
    private $conn;
    private $db_table = "";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function GetShippingLinerChargesList($RequestBody)
    {
        $RecordsPerPage = 100;
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $StatusType = $RequestBody['Status'];
        $CountryID = $RequestBody['CountryID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $LinerId = $RequestBody['Liner'];


        $SQuery = '';
        $CountryIDList = implode(',', array_map('intval', $CountryID));
        if ($CountryID != "") {
            // $SQuery = 'and POD.DestinationCountry=' . $CountryID;
            $SQuery = "AND POD.DestinationCountry IN ($CountryIDList)";
        }

        $DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";

        // if ($StatusType == "0") {
        // $Query .= ' and POD.PoID not in (SELECT DISTINCT(po_id) FROM `ShippingLinesChargeTransactions`)';
        // } else {
        // $Query .= ' and POD.PoID in (SELECT DISTINCT(po_id) FROM `ShippingLinesChargeTransactions``)';
        // }
        $Query = '';
        $condition = ($StatusType == "0") ? 'not in' : 'in';
        $Query .= " and POD.PoID $condition (SELECT DISTINCT(po_id) FROM `ShippingLinesCharges`)";

        $RecordsPerPage = 100;
        if ($PageNo > 0) {
            $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        } else {
            $LimitMax = 0;
        }
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ConsigneeName,BLNo,CustomsInvNo) LIKE '%$SearchKey%'";
        }


        if ($LinerId) {
            $SQuery = "AND SCI.ShippingLineID IN ($LinerId)";
        }

        $SqlClearingAgentCount = "SELECT count(*) as Total  FROM ScrmPIData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
        left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID 
        left join ScrmNotifyparty as SNP on POD.PoID=SNP.NotifyPartyID 
        WHERE   $DateFilter $SQuery   and POD.Status not in (2,3,4,7)";

        $stmt = $this->conn->prepare($SqlClearingAgentCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFieldsList = "
    SCI.CustomsInvNo AS CInvNo,
    SCD.ConsigneeName,
    SPC.Name AS PortName,
    SIF.BLNo,
    SNP.NotifypartyName,
    POD.PoID,
    SLM.LinerName,
    SLC.id AS charges_id
";

        $SqlClearingAgentQuery = "
    SELECT $SqlFieldsList
    FROM ScrmPIData AS POD 
    LEFT JOIN ScrmConsigneeDetails AS SCD ON POD.ConsigneeID = SCD.ConID
    LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID = POD.PoID
    LEFT JOIN ScrmPortCities AS SPC ON POD.PortOfDischarge = SPC.PortId
    LEFT JOIN ScrmISFDetails AS SIF ON POD.PoID = SIF.PIID 
    LEFT JOIN ScrmNotifyparty AS SNP ON POD.NotifyPartyID = SNP.NotifyPartyID 
    LEFT JOIN ScrmLinerMaster AS SLM ON SCI.ShippingLineID = SLM.LinerID
    LEFT JOIN ShippingLinesCharges AS SLC ON SLC.po_id = POD.PoID
    WHERE $DateFilter $SQuery AND POD.Status NOT IN (2,3,4,7)
    ORDER BY SCI.CustomsInvNo DESC $Limit
";

        $stmt = $this->conn->prepare($SqlClearingAgentQuery);
        $stmt->execute();

        $AryClearingData = [];
        $i = $LimitMax;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayClearingDetails = [
                'SNO' => ++$i,
                'InvNo' => $dataRow['CInvNo'],
                'ID' => $dataRow['PoID'],
                'Buyer' => $dataRow['ConsigneeName'],
                'BLNo' => $dataRow['BLNo'],
                'PortOfDischarge' => $dataRow['PortName'],
                'LinerName' => $dataRow['LinerName'],
                'ShippingLinesChargesTransactions' => []
            ];

            // Load charges if available
            if (!empty($dataRow['charges_id'])) {
                $charges_id = (int) $dataRow['charges_id'];

                $SqlClearingAgentTransaction = "SELECT  *, 
                    SUM(SLCT.amount_in_usd) AS category_usd_total,
                    SUM(SLCT.amount_in_inr) AS category_inr_total,
                    SUM(SLCT.amount_in_usd * SLCT.exchange_rate) AS category_total
                FROM ShippingLinesChargesCategory AS SLCC
                LEFT JOIN ShippingLinesChargeTransactions AS SLCT 
                    ON SLCT.category_code = SLCC.id AND SLCT.charges_id = :charges_id
                LEFT JOIN ShippingLinesCharges AS SLC 
                    ON SLC.id = SLCT.charges_id
                GROUP BY SLCC.id
                ORDER BY SLCC.sort ASC;";

                $stmtCharges = $this->conn->prepare($SqlClearingAgentTransaction);
                $stmtCharges->bindParam(':charges_id', $charges_id, PDO::PARAM_INT);
                $stmtCharges->execute();

                while ($chargeRow = $stmtCharges->fetch(PDO::FETCH_ASSOC)) {
                    $ArrayClearingDetails['ShippingLinesChargesTransactions'][] = [
                        'name' => $chargeRow['name'],
                        'category_code' => $chargeRow['category_code'],
                        'exchange_rate' => $chargeRow['exchange_rate'],
                        'amount_in_inr' => $chargeRow['category_inr_total'],
                        'amount_in_usd' => $chargeRow['category_usd_total'],
                        'bill_date' => $chargeRow['bill_date'],
                        'bill_number' => $chargeRow['bill_number'],
                        'sgst' => $chargeRow['sgst'],
                        'igst' => $chargeRow['igst'],
                        'cgst' => $chargeRow['cgst'],
                        'category_total' => $chargeRow['category_total'],
                        'totalUsdValue' => round((float) ($chargeRow['exchange_rate'] * $chargeRow['amount_in_usd'])),
                        'totalValue' => round((float) ($chargeRow['exchange_rate'] * $chargeRow['amount_in_usd'])),
                    ];
                }
            }

            $AryClearingData[] = $ArrayClearingDetails;
        }



        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['Data'] = $AryClearingData;
        $data['EditLink'] = 'CreateShippingLinesCharges?id=';
        $data['PageCount'] = $PageCount;

        return $data;
    }


    public function GetShippingLinerChargesDetails($RequestBody)
    {
        $Error = false;

        $PoID = $RequestBody['PoID'];

        $SqlFields = "SCI.CustomsInvNo,SPD.PoNo,SCD.ConsigneeName,SCI.CInvDate,ISF.BLNo,ISF.ContainerNo,FDAEntryNumber,
FDAEntryDate,NotifyPartyName,ClearingAgentInvoiceNo,ClearingAgentInvoiceDate,SCAD.CandFCharges,SCAD.CVDCharges,SCAD.RTCharges,
SCAD.ADCharges as CAAntiDumpingCharges,SCAD.HmDutyCharges as CAHmDutyCharges ,SCAD.MPCharges as CaMPFCharges";

        $SqlClearingAgentFormData = "SELECT  $SqlFields
 FROM `ScrmCustomInvoices` as SCI 
 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
 left join ScrmShippingBillDetails as SCOM on SPD.PoID=SCOM.InvID 
 LEFT JOIN  ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
 left JOIN ScrmPortCities as SPC on SPC.PortId=SPD.FinalDestination 
 left JOIN ScrmClearingAgentDetails as SCAD on SCAD.PoID=SPD.PoID 
 left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID 
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SPD.PoID 
 where  SPD.PoID='$PoID' ";

        $stmt = $this->conn->prepare($SqlClearingAgentFormData);
        $stmt->execute();

        $Response = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ShippingLinerFormData['InvoiceNo'] = $dataRow['CustomsInvNo'];
            $ShippingLinerFormData['PoNo'] = $dataRow['PoNo'];
            $ShippingLinerFormData['ConsigneeName'] = $dataRow['ConsigneeName'];
            $ShippingLinerFormData['CInvDate'] = $dataRow['CInvDate'];
            $ShippingLinerFormData['BLNo'] = $dataRow['BLNo'];
            $ShippingLinerFormData['ContainerNo'] = $dataRow['ContainerNo'];
            $ShippingLinerFormData['EntryNumber'] = $dataRow['FDAEntryNumber'];
            $ShippingLinerFormData['EntryDate'] = $dataRow['FDAEntryDate'];

        }

        $SqlShipppingLiner = "SELECT * FROM ShippingLinesCharges WHERE  po_id='$PoID' ";

        $stmtShipppingLiner = $this->conn->prepare($SqlShipppingLiner);
        $stmtShipppingLiner->execute();

        $LinerChargeTransactions = [];
        $chargesId = '';
        while ($chargeRow = $stmtShipppingLiner->fetch(PDO::FETCH_ASSOC)) {
            $ShippingLinerFormData['BillNumber'] = $chargeRow['bill_number'];
            $ShippingLinerFormData['BillDate'] = $chargeRow['bill_date'];
            $ShippingLinerFormData['cgst'] = $chargeRow['cgst'];
            $ShippingLinerFormData['igst'] = $chargeRow['igst'];
            $ShippingLinerFormData['sgst'] = $chargeRow['sgst'];
            $chargesId = $chargeRow['id'];

            $SqlTransactions = "SELECT * FROM ShippingLinesChargeTransactions WHERE charges_id = :charges_id";
            $stmtTransactions = $this->conn->prepare($SqlTransactions);
            $stmtTransactions->bindParam(':charges_id', $chargesId);
            $stmtTransactions->execute();

            while ($txRow = $stmtTransactions->fetch(PDO::FETCH_ASSOC)) {
                $LinerChargeTransactions[] = $txRow;
            }
        }

        $data['ShippingLinerFormData'] = $ShippingLinerFormData;
        $data['LinerChargeTransactions'] = $LinerChargeTransactions;
        if ($dataRow) {
            $data['Error'] = null;
            $data['Msg'] = "Success";
            $records = array('Data' => $data, );

            return $records;
        } else {
            $data['Error'] = "Unable To Fetch Data";
            $records = array('Data' => $data);
            return $records;
        }
    }
}
