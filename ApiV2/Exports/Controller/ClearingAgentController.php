<?php

include_once '../Global/Util.php';
class ClearingAgentController
{
    private $conn;
    private $db_table = "";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function GetClearingAgentDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $StatusType = $RequestBody['Status'];
        $CountryID = $RequestBody['CountryID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $SQuery = '';
        $CountryIDList = implode(',', array_map('intval', $CountryID));
        if ($CountryID != "") {
            // $SQuery = 'and POD.DestinationCountry=' . $CountryID;
            $SQuery = "AND POD.DestinationCountry IN ($CountryIDList)";
        }

        $DateFilter = "SCI.CInvDate between '$FromDate' and '$ToDate'";
        $ApproveFields = "";
        $columnExists = 0;
         $Query="";
        if ($StatusType == "0") {
            $Query .= ' and POD.PoID not in (SELECT PoID FROM `ScrmClearingAgentDetails`)';
        } else {
            $Query .= ' and POD.PoID in (SELECT PoID FROM `ScrmClearingAgentDetails`)';
                $stmt = $this->conn->query("SELECT COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'ScrmClearingAgentDetails' 
                    AND COLUMN_NAME = 'ApprovalStatus'
                    LIMIT 1");
                    $colum = $stmt->fetch(PDO::FETCH_ASSOC);
                if (isset($colum['COLUMN_NAME'])) {
                    $columnExists = 1;
                }
            if($columnExists == 1){
                $ApproveFields = ",SCOD.ApprovalStatus";
            }else{
                $ApproveFields = "";
            }
        }
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


        $SqlFieldsList = "SCI.CustomsInvNo as CInvNo,SCD.ConsigneeName,SPC.Name,SIF.BLNo,SNP.NotifypartyName,POD.PoID";

        $SqlClearingAgentQuery = "SELECT $SqlFieldsList $ApproveFields FROM ScrmPIData as POD 
                            left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
                            left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
                            left join ScrmPortCities as SPC on POD.PortOfDischarge=SPC.PortId
                            left join ScrmISFDetails as SIF on POD.PoID=SIF.PIID 
                            left join ScrmNotifyparty as SNP on POD.NotifyPartyID=SNP.NotifyPartyID 
                            left join ScrmClearingAgentDetails as SCOD on POD.PoID=SCOD.PoID 
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
            $ArrayClearingDetails['NotifypartyName'] = $dataRow['NotifypartyName'];
            if($columnExists == 1){
                $ArrayClearingDetails['ApprovalStatus'] = (int)$dataRow['ApprovalStatus'];
            }else{
                $ArrayClearingDetails['ApprovalStatus'] = 0;
            }
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
        $data['EditLink'] = 'ClearingAgentForm?id=';
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
