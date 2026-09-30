<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class DelayedShipmentsController
{
    private $conn;
    private $db_table = "ScrmPoData";
    private $utilController;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    // public function getDelayedShipmentsDetails($RequestBody)
    // {
    //     $RecordsPerPage = 100;
    //     $ExporterID = $RequestBody['ExporterID'];
    //     $ConsigneeID = $RequestBody['ConsigneeID'];
    //     $YEAR = $RequestBody['YEAR'];
    //     $Month = $RequestBody['Month'];
    //     $ShipByDate = $YEAR . '-' . $Month . '-31';
    //     $SearchKey = $RequestBody['SearchKey'];
    //     $PageNo = $RequestBody['PageNo'];
    //     $LoadAll = $RequestBody['LoadAll'];

    //     $DateFilter =" SCI.CInvDate > '$ShipByDate'";
    //     // $DateFilter = " month(SCI.CInvDate) ='$Month' and year(SCI.CInvDate)='$YEAR'";
    //     $Query = '';
    //     if ($ExporterID != "") {
    //         $Query .= ' and POD.ExporterID=' . $ExporterID;
    //     }
    //     if ($ConsigneeID != "") {
    //         $Query .= ' and POD.ConsigneeID=' . $ConsigneeID;
    //     }

    //     $RecordsPerPage = 100;
    //     $LimitMax = ($PageNo - 1) * $RecordsPerPage;
    //     $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

    //     if ($LoadAll == 1) {
    //         $Limit = '';
    //     }

    //     if ($SearchKey != '') {
    //         $Query .= " and CONCAT_WS('', POD.PoID,ProductionStartedDate, InvDate, PoDate, ConsigneeName, PoNo, PoStatus, SCI.StockOutDate, 
    //         SCL.name, POD.ShipByDate, SSC.Name,SPC.Name,POD.ExporterID, POD.ConsigneeID) LIKE '%$SearchKey%'";
    //     }

    //     $SqlDelayedShipmentsCount = "SELECT count(*) as Total  FROM ScrmPoData as POD 
    //     left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
    //     left join ScrmISFDetails ISF on ISF.PIID=POD.PoID  
    //     left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID 
    //     left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
    //     left join ScrmPortCities as SPC on POD.PortOfDischarge	=SPC.PortId 
    //     left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
    //     left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID
    //     WHERE POD.PoID in ( select SCI.PiID from ScrmCustomInvoices as SCI 
    //     left join ScrmISFDetails ISF on ISF.PIID=SCI.PiID 
    //     where $DateFilter and StockOutDate!=''
    //     and (ISF.EmptyReturn is null or ISF.EmptyReturn='')) 
    //     or POD.PoID in (select SCI.PiID from ScrmCustomInvoices as SCI 
    //     left join ScrmISFDetails ISF on ISF.PIID=SCI.PiID 
    //     where $DateFilter and StockOutDate!='' and ISF.EmptyReturn!='') 
    //     or PoID NOT in (SELECT PiID FROM ScrmCustomInvoices where StockOutDate!='') and POD.Status not in (2,3,4)   $Query
    //      ORDER by PoID ASC,  ShipByDate asc";

    //     $stmtDelayedShipmentsCount = $this->conn->prepare($SqlDelayedShipmentsCount);
    //     $stmtDelayedShipmentsCount->execute();
    //     $dataRow = $stmtDelayedShipmentsCount->fetch(PDO::FETCH_ASSOC);
    //     $TotalCount = $dataRow['Total'];
    //     $PageCount = ceil($TotalCount / $RecordsPerPage);
    //     // COALESCE(DATEDIFF(POD.ShipByDate,now())-1,0) as TDaysRemaining

    //     $SqlFieldsList = "POD.PoID,ProductionStartedDate, InvDate, PoDate, ConsigneeName, PoNo, PoStatus, SCI.StockOutDate, 
    //     SCL.name as CountryName, POD.ShipByDate, SSC.Name as PoStatus ,SPC.Name as Port ,POD.ExporterID, POD.ConsigneeID,
    //     COALESCE(DATEDIFF(POD.ShipByDate,SCI.StockOutDate)-1,0) as DaysRemaining";

    //     echo $SqlDelayedShipments = "SELECT $SqlFieldsList FROM ScrmPoData AS POD 
    //     LEFT JOIN ScrmConsigneeDetails AS SCD ON POD.ConsigneeID=SCD.ConID
    //     LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID=POD.PoID  
    //     LEFT JOIN ScrmCustomInvoices AS SCI ON POD.PoID=SCI.PiID 
    //     LEFT JOIN ScrmCountriesList AS SCL ON POD.DestinationCountry=SCL.id 
    //     LEFT JOIN ScrmPortCities AS SPC ON POD.PortOfDischarge	=SPC.PortId 
    //     LEFT JOIN ScrmShipmentStatusCodes AS SSC ON POD.Status=SSC.Code
    //     LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON POD.PoID=SDB.PiID
    //     WHERE ( POD.PoID IN 
    //     (SELECT SCI.PiID FROM ScrmCustomInvoices AS SCI 
    //     LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID=SCI.PiID 
    //     WHERE $DateFilter AND StockOutDate!=''
    //     AND (ISF.EmptyReturn IS NULL OR ISF.EmptyReturn='')) 
    //     OR POD.PoID IN (SELECT SCI.PiID FROM ScrmCustomInvoices AS SCI 
    //     LEFT JOIN ScrmISFDetails AS ISF ON ISF.PIID=SCI.PiID 
    //     WHERE $DateFilter AND StockOutDate!='' AND ISF.EmptyReturn!='') 
    //     OR PoID NOT IN (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate!='')) AND POD.Status NOT IN (2,3,4)   $Query
    //     ORDER BY PoID ASC,  ShipByDate ASC " . $Limit;
    //     $stmt = $this->conn->prepare($SqlDelayedShipments);
    //     $stmt->execute();
    //     // $errorInfo = $stmt->errorInfo();
    //     // var_dump($errorInfo);

    //     $AryShipmentsData = [];
    //     $i = $LimitMax;
    //     while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         // $ArrayData = $this->GetBreakupDetails($dataRow['PoID']);  uncomment this for popup functionality

    //         $PoDate =  $dataRow['PoDate'] ? date('d-m-Y', strtotime($dataRow['PoDate'])) : ' ';
    //         $ShipByDate =  $dataRow['ShipByDate'] ? date('d-m-Y', strtotime($dataRow['ShipByDate'])) : ' ';
    //         $StockOutDate =  $dataRow['StockOutDate'] ? date('d-m-Y', strtotime($dataRow['StockOutDate'])) : ' ';

    //         $ArrayShipmentsDetails['SNO'] = ++$i;
    //         $ArrayShipmentsDetails['ID'] = $dataRow['PoID'];
    //         $ArrayShipmentsDetails['PoNo'] = $dataRow['PoNo'];
    //         $ArrayShipmentsDetails['ExporterID'] = $dataRow['ExporterID'];
    //         $ArrayShipmentsDetails['ConsigneeID'] = $dataRow['ConsigneeID'];
    //         $ArrayShipmentsDetails['PoDate'] = $PoDate;
    //         $ArrayShipmentsDetails['ProductionStartDate'] = $dataRow['ProductionStartedDate'];;
    //         $ArrayShipmentsDetails['Country'] = $dataRow['CountryName'];
    //         $ArrayShipmentsDetails['Buyer'] = $dataRow['ConsigneeName'];
    //         $ArrayShipmentsDetails['ActualShipByDate'] =  $ShipByDate;
    //         $ArrayShipmentsDetails['StockOutDate'] = $StockOutDate;
    //         $ArrayShipmentsDetails['Status'] = $dataRow['PoStatus'];
    //         $ArrayShipmentsDetails['DaysRemDelayed'] = $dataRow['DaysRemaining'];
    //         // $ArrayShipmentsDetails['BreakupData'] = $ArrayData; uncomment this for popup functionality
    //         $AryShipmentsData[] = $ArrayShipmentsDetails;
    //     }
    //     if (count($AryShipmentsData) < $RecordsPerPage) {
    //         $RecordsAval = 0;
    //     } else {
    //         $RecordsAval = 1;
    //     }

    //     $data['TotalCount'] = $TotalCount;
    //     $data['RecordsAval'] = $RecordsAval;
    //     $data['PageCount'] = $PageCount;
    //     $data['Data'] = $AryShipmentsData;

    //     return $data;
    // }

    public function getDelayedShipmentsDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ExporterID = $RequestBody['ExporterID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $FinClsYear = $RequestBody['YEAR'];
        $Month = $RequestBody['Month'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        if ($Month != '') {
            $Month = sprintf('%02d', $Month);
            if ($Month > 3) {
                $DateStart = $FinClsYear . '-' . $Month . '-01';
                $DateEnd = ($FinClsYear) . '-' . $Month . '-31';
            } else {
                $DateStart = ($FinClsYear + 1) . '-' . $Month . '-01';
                $DateEnd = ($FinClsYear + 1) . '-' . $Month . '-31';
            }
            $DateFilter = " SCI.CInvDate BETWEEN '$DateStart' and '$DateEnd'";
        } else {
            $DateStart = $FinClsYear . '-04-01';
            $DateEnd = ($FinClsYear + 1) . '-03-31';
            $DateFilter = " SCI.CInvDate BETWEEN '$DateStart' and '$DateEnd'";
        }

        $Query = '';
        if ($ExporterID != "") {
            $Query .= ' and SPD.ExporterID=' . $ExporterID;
        }
        if ($ConsigneeID != "") {
            $Query .= ' and SPD.ConsigneeID=' . $ConsigneeID;
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', SPD.PoNo,SPD.PoID,SPD.PoDate,SCI.StockOutDate,ISF.EmptyReturn,SCD.ConsigneeName,SDB.ProductionStartedDate,POD.ExporterID, POD.ConsigneeID,
            SPD.ShipByDate,SCL.name,SPD.ExporterID, SPD.ConsigneeID) LIKE '%$SearchKey%'";
        }

        $SqlDelayedShipmentsCount = "SELECT count(*) as Total FROM ScrmPoData as SPD 
        LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
        left join ScrmISFDetails ISF on ISF.PIID=SPD.PoID 
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
        LEFT JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
        LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SPD.PoID=SDB.PiID
        WHERE (SPD.PoID in 
        (SELECT SCI.PiID from ScrmCustomInvoices as SCI 
        left join ScrmISFDetails ISF on ISF.PIID=SCI.PiID 
        where $DateFilter and StockOutDate!='' and (ISF.EmptyReturn is null or ISF.EmptyReturn='')) or 
        SPD.PoID in (SELECT SCI.PiID from ScrmCustomInvoices as SCI 
        left join ScrmISFDetails ISF on ISF.PIID=SCI.PiID 
        where $DateFilter and StockOutDate!='' and ISF.EmptyReturn!='') or PoID NOT in 
        (SELECT PiID FROM ScrmCustomInvoices where StockOutDate!='')) and SPD.Status not in (2,3,4)  $Query  ORDER BY PoID ASC,ShipByDate ASC";

        $stmtDelayedShipmentsCount = $this->conn->prepare($SqlDelayedShipmentsCount);
        $stmtDelayedShipmentsCount->execute();
        $dataRow = $stmtDelayedShipmentsCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFieldsList = "SPD.PoNo,SPD.PoID,SPD.PoDate,SCI.StockOutDate,ISF.EmptyReturn,SCD.ConsigneeName,SPD.ShipByDate,SDB.ProductionStartedDate,
        SPD.ExporterID, SPD.ConsigneeID,COALESCE(DATEDIFF(SPD.ShipByDate,SCI.StockOutDate)-1,0) as DaysRemaining,SCL.name as CountryName";

        $SqlDelayedShipments = "SELECT $SqlFieldsList FROM ScrmPoData as SPD 
        LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
        left join ScrmISFDetails ISF on ISF.PIID=SPD.PoID 
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
        LEFT JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
        LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SPD.PoID=SDB.PiID
        WHERE ( SPD.PoID in 
        ( SELECT SCI.PiID from ScrmCustomInvoices as SCI 
        left join ScrmISFDetails ISF on ISF.PIID=SCI.PiID 
        where $DateFilter and StockOutDate!='' and (ISF.EmptyReturn is null or ISF.EmptyReturn='')) or 
        SPD.PoID in (SELECT SCI.PiID from ScrmCustomInvoices as SCI 
        left join ScrmISFDetails ISF on ISF.PIID=SCI.PiID 
        where $DateFilter and StockOutDate!='' and ISF.EmptyReturn!='') or PoID NOT in 
        (SELECT PiID FROM ScrmCustomInvoices where StockOutDate!='') )and SPD.Status not in (2,3,4)  $Query  
        ORDER BY PoID ASC,ShipByDate ASC " . $Limit;
        
        $stmt = $this->conn->prepare($SqlDelayedShipments);
        $stmt->execute();

        $AryShipmentsData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $PoDate =  $dataRow['PoDate'] ? date('d-m-Y', strtotime($dataRow['PoDate'])) : '';
            $ShipByDate =  $dataRow['ShipByDate'] ? date('d-m-Y', strtotime($dataRow['ShipByDate'])) : '';
            $StockOutDate =  $dataRow['StockOutDate'] ? date('d-m-Y', strtotime($dataRow['StockOutDate'])) : '';
            $ProductionStartedDate =  $dataRow['ProductionStartedDate'] ? date('d-m-Y', strtotime($dataRow['ProductionStartedDate'])) : '';

            $EmptyReturn = $dataRow['EmptyReturn'];
            $ArrayShipmentsDetails['SNO'] = ++$i;
            $ArrayShipmentsDetails['ID'] = $dataRow['PoID'];
            $ArrayShipmentsDetails['PoNo'] = $dataRow['PoNo'];
            $ArrayShipmentsDetails['EmptyReturn'] = $EmptyReturn;
            $ArrayShipmentsDetails['StockOutDate'] = $StockOutDate;
            $ArrayShipmentsDetails['PoDate'] = $PoDate;
            $ArrayShipmentsDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayShipmentsDetails['ActualShipByDate'] =  $ShipByDate;
            $ArrayShipmentsDetails['ProductionStartDate'] = $ProductionStartedDate;
            $ArrayShipmentsDetails['Country'] = $dataRow['CountryName'];
            $ArrayShipmentsDetails['DaysRemDelayed'] = $dataRow['DaysRemaining'];

            if ($StockOutDate != '' && $EmptyReturn != '') {
                $ArrayShipmentsDetails['Status'] = "Delivered";
            } else  if ($StockOutDate != '') {
                $ArrayShipmentsDetails['Status'] = "Shipped";
            } else {
                $ArrayShipmentsDetails['Status'] = "Pending";
            }

            $AryShipmentsData[] = $ArrayShipmentsDetails;
        }
        if (count($AryShipmentsData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $AryShipmentsData;

        return $data;
    }

    public function GetBreakupDetails($PoID)
    {
        $SqlList = "VL.FreezingType,PGD.ID AS PGDID,CurrentMcs,PoNo,PoID,ShortName as Variety,VL.Grade,Mcs,APS.PackingStyle,Price as LbPrice, 
        sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs, sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price";
        $SqlBreakupDetails = "SELECT $SqlList FROM ScrmPoData as SPD 
        LEFT JOIN ScrmPoGradeData as PGD ON PGD.InvId=SPD.PoID 
        LEFT JOIN ScrmPIVarietyList as VL ON PGD.VarietyID=VL.VarietyID 
        LEFT JOIN AdminPackingStyles as APS ON APS.PCKID=PGD.PackingStyleID 
        WHERE PoID='$PoID' GROUP BY PGD.ID order by Abs(VL.Grade) ASC";
        $stmt = $this->conn->prepare($SqlBreakupDetails);
        $stmt->execute();

        $AryShipmentsData = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakupDetails['SNO'] = ++$i;
            $ArrayBreakupDetails['ID'] = $dataRow['PGDID'];
            $ArrayBreakupDetails['Variety'] = $dataRow['Variety'];
            $ArrayBreakupDetails['FreezingType'] = $dataRow['FreezingType'];
            $ArrayBreakupDetails['Grade'] = $dataRow['Grade'];;
            $ArrayBreakupDetails['Mcs'] = $dataRow['Mcs'];
            $ArrayBreakupDetails['CurrentMcs'] = $dataRow['CurrentMcs'];
            $ArrayBreakupDetails['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayBreakupDetails['Price'] = $dataRow['LbPrice'];
            $ArrayBreakupDetails['Lbs'] = $dataRow['Lbs'];
            $ArrayBreakupDetails['Value'] = $dataRow['Price'];
            $AryShipmentsData[] = $ArrayBreakupDetails;
        }
        return $AryShipmentsData;
    }
}
