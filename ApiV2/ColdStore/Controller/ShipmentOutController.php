<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ShipmentOutController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getShimpentOutDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $PoNo = $RequestBody['PoNo'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $pdCategory = $RequestBody['pdCategory'];

        $Query = '';

        if ($PoNo != "") {

            $Query = " and CPID.PoID in($PoNo)";
        } else {

            $SqlPoList = "SELECT Group_concat(PoID) as PoID FROM `ScrmPoData` as SPD
            LEFT Join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
            WHERE (ShippedOn='' or ShippedOn is Null)";

            $stmtPoList = $this->conn->prepare($SqlPoList);
            $stmtPoList->execute();

            $RowPo = $stmtPoList->fetch(PDO::FETCH_ASSOC);

            $PoID = $RowPo['PoID'];

            $Query = " and CPID.PoID in($PoID)";
        }

        if ($TenantID != 'BOTH') {
            $Query .= " and CPID.StockHolderID='$TenantID'";
        }

        if ($ColdstoreID != "") {
            $Query .= " and CPID.UnitID='$ColdstoreID'";
        }
        if ($pdCategory != "") {
            $Query .= " and CPID.ProductCategoryID='$pdCategory'";
        }

        $RecordsPerPage = 500;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CB.BarCodeID,ShortName,BarCodeValue,coalesce(PoNo,'NA'),RowID,CR.BlockID,RackName,StockID,AvalMcs,RPS.StationName
            BlockName,PackingTypeID,CSP.PackingStyle,CPID.Grade,ProductionDate, `ProductionTypeID`,ProductCategoryID,UnitID) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsBarCodes` WHERE BarCodeStatus='1' ";
        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        //    $errorInfo = $stmt->errorInfo();
        //     var_dump($errorInfo);

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalCount = $dataRow['Total'];
        // $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "(CPS.TotalSlabs*AvalMcs) AS McsSlabs, (LooseBags) AS LooseSlabs,AvalKgs,CPID.StockID,CB.BarCodeID,ShortName,BarCodeValue,coalesce(PoNo,'NA') as PoNo,CR.RowID,CR.BlockID,RackName,StockID,
        AvalMcs,BlockName,PackingTypeID,CSP.PackingStyle,CPID.Grade,ProductionDate, `ProductionTypeID`, ProductCategoryID,UnitID,StoreName
        ,CPID.ProductionID,CR.StoreID,CPID.VarietyID,RPS.StationName";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` AS CPID  
        LEFT JOIN `CsBarCodes` AS CB ON CB.BarCodeID = CPID.BarCodeID
        LEFT JOIN `CsStoresRackData` AS CR ON CR.RowID  = CPID.StoreRackID  
        LEFT JOIN CsStoresBlockData AS CBD ON CBD.BlockID=CR.BlockID
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList AS AGO ON CPID.VarietyID=AGO.VarietyID 
        LEFT JOIN ScrmPoData AS CPOS ON CPOS.PoID  = CPID.PoID 
        LEFT JOIN ScrmPoGradeData AS CPGD ON (CPGD.ID  = CPID.PoGradeId AND CPGD.VarietyID=CPID.VarietyID)
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = CPID.PackingStyleID	
        left join CsStores as CS on CS.StoreID = CPID.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CPID.PlantID
        WHERE (AvalMcs !=0  or LooseBags !=0) and PackingTypeID='MASTER' 
        AND  CPGD.ID IS NOT NULL
        $Query ORDER BY PoNo ASC " . $Limit;
        // $Query order by StockID ASC " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $TotalCount = $stmtSelect->rowCount();
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayShipmentData = [];
        $TotalSlabs = 0;

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayShipmentList['SNO'] = ++$i;
            $ArrayShipmentList['StockID'] = $dataRow['StockID'];
            $ArrayShipmentList['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayShipmentList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['BarCodeValue'] = $dataRow['BarCodeValue'];
            $ArrayShipmentList['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayShipmentList['Group'] = $dataRow['ProductionTypeID'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'];
            $ArrayShipmentList['RowID'] = $dataRow['RowID'];
            $ArrayShipmentList['BlockID'] = $dataRow['BlockID'];
            $ArrayShipmentList['RackName'] = $dataRow['RackName'];
            $ArrayShipmentList['StockID'] = $dataRow['StockID'];
            $ArrayShipmentList['AvalMcs'] = $dataRow['AvalMcs'];
            $ArrayShipmentList['BlockName'] = $dataRow['BlockName'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            $ArrayShipmentList['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayShipmentList['ProductionTypeID'] = $dataRow['ProductionTypeID'];
            $ArrayShipmentList['ProductCategoryID'] = $dataRow['ProductCategoryID'];
            $ArrayShipmentList['UnitID'] = $dataRow['UnitID'];
            $ArrayShipmentList['ProductionID'] = $dataRow['ProductionID'];
            $ArrayShipmentList['StoreName'] = $dataRow['StoreName'];
            $ArrayShipmentList['StoreID'] = $dataRow['StoreID'];
            $ArrayShipmentList['McsSlabs'] = $dataRow['McsSlabs'];
            $ArrayShipmentList['LooseSlabs'] = $dataRow['LooseSlabs'];
            $ArrayShipmentList['AvalKgs'] = $dataRow['AvalKgs'];
            $ArrayShipmentList['Location'] = $dataRow['BlockName'] . ' - ' . $dataRow['RackName'];

            $McsSlabs = $dataRow['McsSlabs'] ? $dataRow['McsSlabs'] : 0;
            $LooseSlabs = $dataRow['LooseSlabs'] ? $dataRow['LooseSlabs'] : 0;
            $TotalSlabs = $McsSlabs + $LooseSlabs;
            $ArrayShipmentList['TotalSlabs'] = $TotalSlabs;

            $ArrayShipmentData[] =  $ArrayShipmentList;
        }

        if (count($ArrayShipmentData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['Data'] = $ArrayShipmentData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
