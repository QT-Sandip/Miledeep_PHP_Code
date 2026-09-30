<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class ShippedPoListController
{
    private $conn;

    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getShippedPoListDetails($RequestBody)
    {

        $Year = $RequestBody['Year'];
        $Exporter = $RequestBody['Exporter'];
        $ColdStore = $RequestBody['ColdStore'];
        $Country = $RequestBody['Country'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $Query = '';

        if ($Exporter != "") {
            $Query .= ' and POD.ExporterID=' . $Exporter;
        }
        if ($Country != "") {
            $Query .= ' and POD.DestinationCountry=' . $Country;
        }
        if ($ColdStore != "") {
            $Query .= ' and RPS.ProcessingStationID IN ('.$ColdStore.')';
        }

        $DateFilter = $this->utilController->getFinancialYear($Year);
        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CustomsInvNo,PoNo) LIKE '%$SearchKey%'";
        }

        $SqlCustomCount = "SELECT count(*) as Total  FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
        left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
        left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID        
        left JOIN AdminExporterDetails as AED on AED.ExpID=POD.ProcessorID 
        left join RmProcessingStations as RPS on RPS.ProdCode=AED.InvShortCode
        left join ScrmISFDetails as SID on POD.PoID=SID.PIID
        WHERE   (SCI.StockOutDate!='') and  SCI.StockOutDate $DateFilter $Query order by StockOutDate Desc";
        // WHERE (SCI.StockOutDate!=''  or SCI.StockOutDate is null) $Query";

        $stmt = $this->conn->prepare($SqlCustomCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        // $dataRow = null;
        $sqlFields = "PoID,CustomsInvNo,SCI.CInvDate, PoNo,PoDate,SCL.name as DestinationCountry,SID.EstimatedETA,SCI.StockOutDate ";

     $sqlQuery = "SELECT $sqlFields FROM ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
        left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
        left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID        
        left JOIN AdminExporterDetails as AED on AED.ExpID=POD.ProcessorID 
        left join RmProcessingStations as RPS on RPS.ProdCode=AED.InvShortCode
        left join ScrmISFDetails as SID on POD.PoID=SID.PIID
        WHERE   (SCI.StockOutDate!='') and  SCI.StockOutDate $DateFilter $Query order by StockOutDate Desc $Limit";  //TransportedDate!=''  and

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // $Kgs = $dataRow['AvalMcsKgs'] + $dataRow['LooseKgs'];
            $CInvDate = $this->utilController->getFormatedDatelines($dataRow['CInvDate']);
            $PoDate = $this->utilController->getFormatedDatelines($dataRow['PoDate']);
            if ($dataRow['EstimatedETA'] == '') {
                $EstimatedETA = $this->getEtaFDDate($dataRow['PoID']);
            }else{
                $EstimatedETA = $dataRow['EstimatedETA'];

            }

            $EstimatedETA = $this->utilController->getFormatedDatelines($EstimatedETA);
            $StockOutDate = $this->utilController->getFormatedDatelines($dataRow['StockOutDate']);

            $ArrayShippedPoList['SNo'] = ++$i;
            $ArrayShippedPoList['CInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayShippedPoList['CInvDate'] = $CInvDate;
            $ArrayShippedPoList['PoNo'] = $dataRow['PoNo'];
            $ArrayShippedPoList['PoDate'] = $PoDate;
            $ArrayShippedPoList['ShipmentDate'] = $StockOutDate;
            $ArrayShippedPoList['ETA'] = $EstimatedETA;

            $AryData[] = $ArrayShippedPoList;
        }
        if (count($AryData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ShippedPoListData'] = $AryData;
        $Data['PageCount'] = $PageCount;
        $Data['TotalCount'] = $TotalCount;
        $Data['RecordsAval'] = $RecordsAval;
        return $Data;
    }

    function getEtaFDDate($PoID)
    {
        global $iconn;
        $sql = "SELECT SPD.PortOfDischarge,TranshipDt FROM `ScrmPoData` as SPD 
        left join ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
        left join ScrmTranshipmentPortsList  as STL  on STL.ToTOPID=SPC.Name
        WHERE  SPD.PoID='$PoID' and STL.PIID='$PoID'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TranshipDt = $dataRow['TranshipDt'];

        return $TranshipDt;
    }
}
