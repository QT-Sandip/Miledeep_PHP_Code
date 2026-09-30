<?php

include_once '../Global/Util.php';
class ShippingLinesChargesController
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
        $LinerId = $RequestBody['LinerId'];


        $SQuery = '';
        $CountryIDList = implode(',', array_map('intval', $CountryID));
        if ($CountryID != "") {
            // $SQuery = 'and POD.DestinationCountry=' . $CountryID;
            $SQuery = "AND POD.DestinationCountry IN ($CountryIDList)";
        }

        $DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";

        // if ($StatusType == "0") {
        //     $Query .= ' and POD.PoID not in (SELECT DISTINCT(po_id) FROM `ShippingLinesChargeTransactions`)';
        // } else {
        //     $Query .= ' and POD.PoID in (SELECT DISTINCT(po_id) FROM `ShippingLinesChargeTransactions``)';
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
        WHERE   $DateFilter $SQuery  $Query and POD.Status not in (2,3,4,7)";

        $stmt = $this->conn->prepare($SqlClearingAgentCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFieldsList = "SCI.CustomsInvNo as CInvNo,SCD.ConsigneeName,SPC.Name,SIF.BLNo,SNP.NotifypartyName,POD.PoID, SLM.LinerName";

        $SqlClearingAgentQuery = "SELECT $SqlFieldsList FROM ScrmPIData as POD 
                            left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
                            left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
                            left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
                            left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID 
                            left join ScrmNotifyparty as SNP on POD.NotifyPartyID=SNP.NotifyPartyID 
                            left join ScrmLinerMaster as SLM on SCI.ShippingLineID=SLM.LinerID
                            WHERE   $DateFilter $SQuery  $Query and POD.Status not in (2,3,4,7) order by SCI.CInvNo desc $Limit ";


        $stmt = $this->conn->prepare($SqlClearingAgentQuery);
        $stmt->execute();
        // echo $SqlClearingAgentQuery;exit;

        $AryClearingData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayClearingDetails['SNO'] = ++$i;
            $ArrayClearingDetails['InvNo'] = $dataRow['CInvNo'];
            $ArrayClearingDetails['ID'] = $dataRow['PoID'];
            $ArrayClearingDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayClearingDetails['BLNo'] = $dataRow['BLNo'];
            $ArrayClearingDetails['PortOfDischarge'] = $dataRow['Name'];
            $ArrayClearingDetails['LinerName'] = $dataRow['LinerName'];
            $AryClearingData[] = $ArrayClearingDetails;
        }
        if (count($AryClearingData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['Data'] = $AryClearingData;
        $data['EditLink'] = 'CreateShippingLinesCharges?id=';
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

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
            $ShippingLinerFormData['grand_total'] = $chargeRow['grand_total'];
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
