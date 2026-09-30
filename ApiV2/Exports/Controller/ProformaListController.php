<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../App/Controller/AppUtilController.php';

class ProformaListController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;
    private $appController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->appController = new AppUtilController($this->conn);        
    }
    // GET ALL
    public function GetPIListDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ExporterID = $RequestBody['ExporterID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $CountryID = $RequestBody['CountryID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $Status = $RequestBody['Status'];
        $UserID = $RequestBody['UserID'];
        $ClientID = $RequestBody['ClientID'];


        $Query = '';
        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ConsigneeID != "") {
            $Query .= ' and SCD.ConID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and SCL.id=' . $CountryID;
        }
        if ($Status != "") {
            $Query .= ' and POD.Status=' . $Status;
        }



        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ConsigneeName,PoNo,InvNumFull,ExpApprovalNo) LIKE '%$SearchKey%'";
        }

        $SqlProformaCount = "SELECT count(*) as Total  FROM ScrmPIData as POD 
       left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
       left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
       left join ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
       left join AdminExporterDetails as AED on AED.ExpID=POD.ProcessorID
       WHERE POD.PoID not in (SELECT PiID FROM `ScrmCustomInvoices`)  
       $Query";

        $stmt = $this->conn->prepare($SqlProformaCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $SqlFildsListList = "
        POD.PoID,
        SCD.ConsigneeName,
        POD.PoNo,
        POD.InvNumFull,
        POD.InvDate,
        POD.ShipByDate,
        SSC.Name as PoStatus,
        POD.Status,
        AED.ExpApprovalNo,  GROUP_CONCAT(POD.ProductionPlantID SEPARATOR ',') as ProductionPlantIDs,
        GROUP_CONCAT(RmPS.StationName SEPARATOR ',') as StationNames,
        GROUP_CONCAT(RmPS.FullName SEPARATOR ',') as FullNames";
        // AED.ExpApprovalNo, POD.ProductionPlantID,RmPS.StationName,RmPS.FullName";

        $SqlProformaQuery = "SELECT $SqlFildsListList  FROM ScrmPIData as POD 
        LEFT JOIN ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
        LEFT JOIN ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
        LEFT JOIN ScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
        LEFT JOIN AdminExporterDetails as AED on AED.ExpID=POD.ProcessorID
        LEFT JOIN RmProcessingStations AS RmPS ON FIND_IN_SET(RmPS.ProcessingStationID, POD.ProductionPlantID)
        WHERE POD.PoID not in (SELECT PiID FROM `ScrmCustomInvoices`)  
        $Query GROUP BY POD.PoID,SCD.ConsigneeName,POD.PoNo,POD.InvNumFull,POD.InvDate,POD.ShipByDate,SSC.Name,
        AED.ExpApprovalNo order by InvDate desc,InvNo desc $Limit";
        // LEFT JOIN RmProcessingStations AS RmPS ON POD.ProductionPlantID=RmPS.ProcessingStationID


        $stmt = $this->conn->prepare($SqlProformaQuery);
        $stmt->execute();


        $UserAcessType =$this->appController->getUserRoleID($UserID);
        $AccessType = $UserAcessType['AccessType'];

        $IsHidePoEditCondition =$this->appController->getClientConfig($ClientID)['IsHidePoEditCondition'];
        
        $AryPiData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPIListDetails['SNO'] = ++$i;
            $ArrayPIListDetails['ID'] = $dataRow['PoID'];
            $ArrayPIListDetails['Processor'] = $dataRow['ExpApprovalNo'];
            $ArrayPIListDetails['PInvNumber'] = $dataRow['InvNumFull'];
            $date = date_create($dataRow['InvDate']);
            $ArrayPIListDetails['PInvDate'] = date_format($date, "d-m-Y");
            $ArrayPIListDetails['PoNo'] = $dataRow['PoNo'];
            $ArrayPIListDetails['Buyer'] = $dataRow['ConsigneeName'];
            $ArrayPIListDetails['ProductionPlantID'] = $dataRow['ProductionPlantIDs'];
            $ArrayPIListDetails['FullName'] = $dataRow['FullNames'];
            $ArrayPIListDetails['StationName'] = $dataRow['StationNames'];
            //$date = date_create($dataRow['ShipByDate']);
            //$ArrayPIListDetails['ShipByDate'] = date_format($date, "d-m-Y");
            $ArrayPIListDetails['ShipByDate'] = $this->utilController->getFormatedDatelines($dataRow['ShipByDate']);
            $ArrayPIListDetails['Status'] = $dataRow['PoStatus'];
            $PoStatus = $dataRow['PoStatus'];
            $ArrayPIListDetails['Color'] = $this->utilController->getShipmentStatusCodes($PoStatus);
            $ArrayPIListDetails['IsPoEdit'] = (($IsHidePoEditCondition && $dataRow['Status']!=0) && $AccessType == 3 ) ? 0 : 1;

            $AryPiData[] = $ArrayPIListDetails;
        }
        if (count($AryPiData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $aryActionLinks = $this->getActionLinks();

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = $aryActionLinks['EditLink'];
        $data['PrintLink'] = $aryActionLinks['PrintLink'];
        $data['Data'] = $AryPiData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }

    function getActionLinks()
    {
        $PrintLink = 'Reports/DocProformaInvoicePrint.php?id=';

        try {
            $SqlProformaQuery = "SELECT * FROM `ScrmReports` where ReportCode='PI'";
            $stmt = $this->conn->prepare($SqlProformaQuery);
            $stmt->execute();
            if ($stmt != FALSE) {
                $RowReport = $stmt->fetch(PDO::FETCH_ASSOC);
                $PrintLink = $RowReport['Path'] . $RowReport['PrintLink'] . $RowReport['QueryID'] . '=';
            }
        } catch (Exception $e) {
        }
        if ($PrintLink == '') {
            $PrintLink = 'Reports/DocProformaInvoicePrint.php?id=';
        }

        $aryActionLinks['EditLink'] = 'PoEdit?id=';
        $aryActionLinks['PrintLink'] = $PrintLink;
        return $aryActionLinks;
    }
}
