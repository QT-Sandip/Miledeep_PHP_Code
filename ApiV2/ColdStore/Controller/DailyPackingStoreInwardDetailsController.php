<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';
include_once '../ColdStore/Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DailyPackingStoreInwardDetailsController
{
    private $conn;
    private $utilController;
    private $BalanceKgs;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function getStockDetails($RequestBody)
    {
        if (isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'GetPackingStoreDataByFilters') {
            $data = $this->GetPackingStoreDataByFilters($RequestBody);
            return $data;
        }
        if (isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'getBrandListByPoId') {
            $data['Brands'] = $this->getBrandListByPoId($RequestBody['Poid']);
            return $data;
        }
        if (isset($RequestBody['method']) && $RequestBody['method'] == 'getDrpDowns') {
            $data['Sizes'] = $this->getDrpDowns('Sizes');
            $data['Colours'] = $this->getDrpDowns('Colours');
            $data['SlabWeight'] = $this->getSlabWeight();
            $data['Variety'] = $this->getVariety();
            return $data;
        }
        if (isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'PoDummyTable') {
            $data = $this->GetPackingStoreDataByFilters($RequestBody);
            return $data;
        }
        if (isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'ConvertToDummy') {
           // echo '<pre>';print_r($RequestBody);die;
            $data = $this->ConvertToDummy($RequestBody);
            return $data;
        }
        /*from inward page*/
        if (isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'updateDamageQty') {
           // echo '<pre>';print_r($RequestBody);die;
            $data = $this->updateDamageQty($RequestBody);
            return $data;
        }
        if (isset($RequestBody['StockID']) && $RequestBody['StockID'] != '') {
            $StockData = $this->getStockData($RequestBody);
        } else {
            $StockData = null;
        }




        if (isset($RequestBody['StockID']) && $RequestBody['StockID'] != '') {
            $StockList = $this->getStockList($RequestBody);
        } else {
            $StockList = null;
        }


        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['StockData'] = $StockData;
        $Data['StockList'] = $StockList;
        return $Data;
    }
    function GetPackingStoreDataByFilters($RequestBody)
    {

        $coldStore = $RequestBody['coldStore'];
        $stockBlgsTo = $RequestBody['stockBlgsTo'];
        $pckgStyle = $RequestBody['pckgStyle'];
        $PoNumber = $RequestBody['PoNumber'];
        $blocks = $RequestBody['blocks'];
        $stockType = $RequestBody['stockType'];
        $SearchKey = $RequestBody['searchKey'];
        $RackID = $RequestBody['RackID'];
        if(!empty($RequestBody['group'])){
            $group = "'".implode("','",$RequestBody['group'])."'";
        }

        $PageNo = !empty($RequestBody['pageNo']) ? $RequestBody['pageNo'] : 1;
        $LoadAll = $RequestBody['loadAll'];

        $where = '';
        if($coldStore != ""){
            $where .= " AND UnitID=$coldStore ";
        }
        if($stockBlgsTo != ""){
            $where .= " AND PlantID=$stockBlgsTo ";
        }
        if($pckgStyle  != ""){
            $where .= " AND PackingStyleID=$pckgStyle ";
        }
        if($blocks  != ""){
            $where .= " AND CSBD.BlockID=$blocks ";
        }
        if($PoNumber  != ""){
            $where .= " AND (SPD.PoID=$PoNumber OR SPD2.PoID=$PoNumber)";
        }
        if($stockType != ""){
            $where .= " AND StockFloorStatus=$stockType ";
        }
        if($RackID != ""){
            $where .= " AND StoreRackID=$RackID ";
        }
        if(isset($RequestBody['StockIds'])){
            $StockIds = $RequestBody['StockIds'];
            $where .= " AND ID IN($StockIds) ";
        }
        if($group != ""){
            $where .= " AND CPSD.`Group` IN($group) ";
        }

        $RecordsPerPage = 100;
         $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;


        if ($SearchKey != '') {
            $where .= " and CONCAT_WS('', SPD.PoNo,CPSD.Group,CPSD.BarCodeID, CPSD.PoNo,APS.PackingStyle,CPSD.Grade,CPSD.UnitID,BrandName,SPD2.PoNo,PackingTypeID,CPSD.SlabWeight,CPSD.Variety) LIKE '%$SearchKey%'";
        }
        $dataRow = null;$PageCount = 0;
        if ($LoadAll == 0) {
            $sqlCount = "SELECT COUNT(CPSD.ID) Total
                    FROM `CsPackingStoreData` AS CPSD
                    LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPSD.PoNo
                    LEFT JOIN ScrmPoData AS SPD2 ON SPD2.PoID=CPSD.DummyPOID -- dummy poid
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPSD.PackingStyleID
                    LEFT JOIN CsStores AS CSS ON CSS.StoreID=CPSD.UnitID
                    LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CPSD.PlantID
                    LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=CPSD.Brand
                    LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPSD.StoreRackID
                    LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
                    WHERE  1 $where";
            $stmtCount = $this->conn->prepare($sqlCount);
            $stmtCount->execute();
            $TotalCount = $stmtCount->fetch(PDO::FETCH_ASSOC)['Total'];
            $PageCount = ceil($TotalCount / $RecordsPerPage);
        }else{
            $Limit = '';
        }




        $sqlQuery = "SELECT CPSD.*,SPD.PoID,APS.PackingStyle,RPS.StationName,CSS.StoreName,SBM.BrandName,SPD.PoNo,SPD2.PoNo DummyPoNo,
                    CONCAT(`BlockName`,'-',`RackName`) Location,PackingTypeID,CPSD.SlabWeight
                    FROM `CsPackingStoreData` AS CPSD
                    LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPSD.PoNo
                    LEFT JOIN ScrmPoData AS SPD2 ON SPD2.PoID=CPSD.DummyPOID -- dummy poid
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPSD.PackingStyleID
                    LEFT JOIN CsStores AS CSS ON CSS.StoreID=CPSD.UnitID
                    LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CPSD.PlantID
                    LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=CPSD.Brand
                    LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPSD.StoreRackID
                    LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
                    WHERE  1 $where
                    ORDER BY CPSD.ID DESC $Limit";

                   //echo '<pre>';print_r($sqlQuery);die;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = $LimitMax;
        $StockData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayStockData['SNO'] = ++$i;
             $ArrayStockData['ID'] = $dataRow['ID'];
            $ArrayStockData['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStockData['StockID'] = $dataRow['ID'];
            $ArrayStockData['TransactionDate'] = date('d-m-Y', strtotime($dataRow['TransactionDate']));
            $ArrayStockData['Group'] = $dataRow['Group'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];

            $ArrayStockData['PoNumber'] = $dataRow['PoNo'];


            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];

            $ArrayStockData['Mcs'] = $dataRow['Mcs'];
            $ArrayStockData['LooseBags'] = $dataRow['Pouches'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];


            $ArrayStockData['Sizes'] = $dataRow['Sizes'];
            $ArrayStockData['Colours'] = $dataRow['Colour'];
            $ArrayStockData['Spacers'] = $dataRow['Spacers'];
            $ArrayStockData['Count'] = $dataRow['Count'];
            $ArrayStockData['UtilizedCountVal'] = $dataRow['UtilizedCountVal'];
            $ArrayStockData['Brand'] = $dataRow['Brand'];
            $ArrayStockData['BrandName'] = $dataRow['BrandName'];
            $ArrayStockData['BillNo'] = $dataRow['BillNo'];

            $ArrayStockData['UtilizedMcs'] = ($dataRow['UtilizedMcs'] + $dataRow['DummyStock'] + $dataRow['DamagedStock']);
            $ArrayStockData['UtilizedPouches'] = ($dataRow['UtilizedPouches'] + $dataRow['DummyStock'] + $dataRow['DamagedStock']);
            $ArrayStockData['UtilizedSpacers'] = $dataRow['UtilizedSpacers'];
            $ArrayStockData['Location'] = $dataRow['Location'];
            $ArrayStockData['StoreRackID'] = $dataRow['StoreRackID'];
            $ArrayStockData['StockFloorStatus'] = $dataRow['StockFloorStatus'];
            $ArrayStockData['CreatedOn'] = $dataRow['CreatedOn'];
            $ArrayStockData['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayStockData['SlabWeight'] = $dataRow['SlabWeight'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['Dimention'] = $dataRow['Dimention'];

            //if(isset($RequestBody['StockIds'])){
                $ShowCheckBox = 0;
                $ArrayStockData['DummyPOID'] = $dataRow['DummyPOID'];
                $ArrayStockData['DummyPoNo'] = $dataRow['DummyPoNo'];
                $ArrayStockData['DummyStock'] = $dataRow['DummyStock'];
                $ArrayStockData['DamagedStock'] = $dataRow['DamagedStock'];
                $qty= 0;
                $availableQty = 0;
                if( $dataRow['StockFloorStatus'] == 1){
                    if($dataRow['Group'] == 'Mcs'){
                        $qty = $dataRow['Mcs'];
                        $ShowCheckBox = $dataRow['Mcs'] - ($dataRow['UtilizedMcs'] + $dataRow['DummyStock'] + $dataRow['DamagedStock']);//5 - (2+1+1)
                        $availableQty = $qty - ($dataRow['UtilizedMcs'] + $dataRow['DamagedStock'] + $dataRow['DummyStock']);
                    }elseif($dataRow['Group'] == 'Pouches'){
                        $qty = $dataRow['Pouches'];
                        $ShowCheckBox = $dataRow['Pouches'] - ($dataRow['UtilizedPouches'] + $dataRow['DummyStock'] + $dataRow['DamagedStock']);//5 - (2+1+1)
                        $availableQty = $qty - ((int)$dataRow['UtilizedPouches'] + (int)$dataRow['DamagedStock'] + $dataRow['DummyStock']);
                    }elseif($dataRow['Group'] == 'Spacers'){
                        $qty = $dataRow['Spacers'];
                        $ShowCheckBox = $dataRow['Spacers'] - $dataRow['UtilizedSpacers'];//5 - 3
                        $availableQty = $qty - (int)$dataRow['UtilizedSpacers'];
                    }elseif(in_array($dataRow['Group'],['Stickers','Tape Rolls','Count Slips'])){
                        $qty = $dataRow['Count'];
                        $ShowCheckBox = $dataRow['Count'] - $dataRow['UtilizedCountVal'];//5 - 3
                        $availableQty = $qty - (int)$dataRow['UtilizedCountVal'];
                    }
                }
                $ArrayStockData['qty'] = $qty;
                $ArrayStockData['AvailableQty'] = $availableQty;
                $ArrayStockData['AvlDummyQty'] = $ShowCheckBox;
                $ArrayStockData['ShowCheckBox'] = $ShowCheckBox > 0 ? 1 : 0;
            //}
            $StockData[] = $ArrayStockData;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['Data'] = $StockData;
        $data['PageCount'] = $PageCount;

        return $data;
    }
    function getStockData($RequestBody)
    {

        $StockID = $RequestBody['StockID'];
        $BarCodeID = $RequestBody['BarCodeID'];



        $dataRow = null;


        $sqlQuery = "SELECT CPSD.*,SPD.PoID,APS.PackingStyle,RPS.StationName,CSS.StoreName,SBM.BrandName,SPD.PoNo
                    FROM `CsPackingStoreData` AS CPSD
                    LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPSD.PoNo
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPSD.PackingStyleID
                    LEFT JOIN CsStores AS CSS ON CSS.StoreID=CPSD.UnitID
                    LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CPSD.PlantID
                    LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=CPSD.Brand
                    WHERE  ID='$StockID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayStockData['BarCodeID'] = $BarCodeID;
        $ArrayStockData['StockID'] = $dataRow['ID'];
        $ArrayStockData['TransactionDate'] = date('d-m-Y', strtotime($dataRow['TransactionDate']));
        $ArrayStockData['Group'] = $dataRow['Group'];
        $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
        $ArrayStockData['PoNo'] = $dataRow['PoNo'];
        $ArrayStockData['PoID'] = $dataRow['PoID'];
        $ArrayStockData['StoreID'] = $dataRow['UnitID'];
        $ArrayStockData['StoreName'] = $dataRow['StoreName'];
        $ArrayStockData['PlantID'] = $dataRow['PlantID'];
        $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
        $ArrayStockData['Coldstore'] = $dataRow['UnitID'];

        $ArrayStockData['PoNumber'] = $dataRow['PoNo'];


        $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
        $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayStockData['Grade'] = $dataRow['Grade'];

        $ArrayStockData['Mcs'] = $dataRow['Mcs'];
        $ArrayStockData['LooseBags'] = $dataRow['Pouches'];
        $ArrayStockData['UnitID'] = $dataRow['UnitID'];


        $ArrayStockData['Sizes'] = $dataRow['Sizes'];
        $ArrayStockData['Colours'] = $dataRow['Colour'];
        $ArrayStockData['Spacers'] = $dataRow['Spacers'];
        $ArrayStockData['Count'] = $dataRow['Count'];
        $ArrayStockData['Brand'] = $dataRow['Brand'];
        $ArrayStockData['BrandName'] = $dataRow['BrandName'];
        $ArrayStockData['BillNo'] = $dataRow['BillNo'];
        $ArrayStockData['CreatedOn'] = $dataRow['CreatedOn'];

        $ArrayStockData['PackingTypeID'] = $dataRow['PackingTypeID'];
        $ArrayStockData['SlabWeight'] = $dataRow['SlabWeight'];
         $ArrayStockData['Variety'] = $dataRow['Variety'];
        $ArrayStockData['Dimention'] = $dataRow['Dimention'];

        return $ArrayStockData;
    }

    function getStockList($RequestBody)
    {

        $StockID = $RequestBody['StockID'];
        $BarCodeID = $RequestBody['BarCodeID'];


        if ($StockID != '') {
            //  $Query = " and StockID='$StockID'";
        }


        $dataRow = null;

        $sqlQuery = "SELECT CPSD.*,SPD.PoID,APS.PackingStyle,RPS.StationName,CSS.StoreName,SBM.BrandName,SPD.PoNo,
                    CPSD.SlabWeight AS SlabWeight
                    FROM `CsPackingStoreData` AS CPSD
                    LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPSD.PoNo
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPSD.PackingStyleID
                    LEFT JOIN CsStores AS CSS ON CSS.StoreID=CPSD.UnitID
                    LEFT JOIN RmProcessingStations AS RPS ON RPS.ProcessingStationID=CPSD.PlantID
                    LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=CPSD.Brand
                    WHERE  ID='$StockID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['BarCodeID'] = $BarCodeID;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayStockData['StockID'] = $dataRow['ID'];
            $ArrayStockData['BarCodeID'] = $BarCodeID;
            $ArrayStockData['TransactionDate'] = date('d-m-Y', strtotime($dataRow['TransactionDate']));
            $ArrayStockData['Group'] = $dataRow['Group'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
           // $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['Mcs'] = $dataRow['Mcs'];
            $ArrayStockData['LooseBags'] = $dataRow['Pouches'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];

            $ArrayStockData['Sizes'] = $dataRow['Sizes'];
            $ArrayStockData['Colours'] = $dataRow['Colour'];
            $ArrayStockData['Spacers'] = $dataRow['Spacers'];
            $ArrayStockData['Count'] = $dataRow['Count'];
            $ArrayStockData['Brand'] = $dataRow['Brand'];
            $ArrayStockData['BrandName'] = $dataRow['BrandName'];
            $ArrayStockData['BillNo'] = $dataRow['BillNo'];
            $ArrayStockData['CreatedOn'] = $dataRow['CreatedOn'];

            $ArrayStockData['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayStockData['SlabWeight'] = $dataRow['SlabWeight'];
             $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['Dimention'] = $dataRow['Dimention'];
            $AryStockData[] = $ArrayStockData;
        }

        $Data['Data'] = $AryStockData;
        //  $AryStockData['BarCodeID'] = $BarCodeID;
        return $Data;
    }
    public function postStockDetails($RequestBody)
    {
        if($RequestBody['methodType'] == 'placePaletteFromLayOut'){
            $data = $this->postPlacePalletDetails($RequestBody);
            return $data;
        }

        $PostedUserID = $RequestBody['PostedUserId'];;
        $StockID = $RequestBody['StockID'];
        $TransactionDate = $RequestBody['TransactionDate'];
        $BarCodeID = $RequestBody['BarCodeID'];
        $UnitID = $RequestBody['UnitID'];
        $PlantID = $RequestBody['TxtProductionPlant'];
        $AvalMcs = $RequestBody['Mcs'];
        $LooseBags = $RequestBody['LooseBags'];
        $Group = $RequestBody['Group'];

        $Grade = $RequestBody['Grade'];
        $PackingStyleID = $RequestBody['PckgStyle'];

        $PoID = $RequestBody['PoNo'];


        $Sizes = $RequestBody['Sizes'];
        $Colours = $RequestBody['Colours'];
        $Spacers = $RequestBody['Spacers'];
        $Brand = $RequestBody['Brand'];
        $BillNo = $RequestBody['BillNo'];
        $txtCounts = $RequestBody['txtCounts'];

        $PackingTypeID = $RequestBody['drpPackingType'];
        $SlabWeight = $RequestBody['drpSlabWeight'];
        $Variety = $RequestBody['drpVariety'];
        $txtDimention = $RequestBody['txtDimention'];

        if ($BarCodeID == '') {
            $BarCodeID = $this->getNewBarcode($RequestBody);
        }

        $ProductDetails = [];

        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];

        if ($this->Error == null && $StockID == '') {
            $sqlQuery = "INSERT INTO CsPackingStoreData
                        (`TransactionDate`,`PoNo`,`PackingStyleID`,`Brand`,`PlantID`,`UnitID`,`BillNo`,`BarCodeID`,`Group`,Grade,`Mcs`,`Pouches`,`Sizes`,`Colour`,`Spacers`,`Count`,`CreatedOn`,`CreatedBy`,
                        PackingTypeID,SlabWeight,Variety,Dimention)
                        VALUES
                        ('$TransactionDate','$PoID','$PackingStyleID','$Brand','$PlantID','$UnitID','$BillNo','$BarCodeID','$Group','$Grade','$AvalMcs','$LooseBags','$Sizes','$Colours','$Spacers','$txtCounts','$CreatedDateTime','$PostedUserID',
                        '$PackingTypeID','$SlabWeight','$Variety','$txtDimention')";
                       // echo '<pre>';print_r($sqlQuery);die;
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            $StockID = $this->conn->lastInsertId();
            $this->auditLogger->logInsert('CsPackingStoreData', $StockID, $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID), $RequestBody);
            $RequestBody['StockID'] = $StockID;
            $RequestBody['BarCodeID'] = $BarCodeID;

            $this->Error = null;
            $this->Msg = 'Posted Successfully';
            return $this->getStockDetails($RequestBody);
        } else if ($this->Error == null && $StockID != '') {

            //if ($RequestBody['Type'] == 'ProductUpdate') {
            //'$LooseBags','$Sizes','$Colours','$Spacers','$CreatedDateTime','$PostedUserID')";
                $oldPackRow = $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID);
                $sqlQuery = "UPDATE CsPackingStoreData SET
                                TransactionDate='$TransactionDate',
                                `PoNo`='$PoID',
                                `PackingStyleID`='$PackingStyleID',
                                `Brand`='$Brand',
                                `PlantID`='$PlantID',
                                `UnitID`='$UnitID',
                                `BillNo`='$BillNo',
                                `BarCodeID`='$BarCodeID',
                                `Group`='$Group',
                                Grade = '$Grade',
                                `Mcs`='$AvalMcs',
                                `Pouches`='$LooseBags',
                                `Sizes`='$Sizes',
                                `Colour`='$Colours',
                                `Count` = '$txtCounts',
                                `Spacers`='$Spacers',
                                `UpdatedOn`='$CreatedDateTime',
                                `UpdatedBy`='$PostedUserID',
                                PackingTypeID='$PackingTypeID',
                                SlabWeight='$SlabWeight',
                                Variety='$Variety',
                                Dimention = '$txtDimention'
                            WHERE `ID`=$StockID";

                // `UnitID`='$UnitID',`ActualKgs`='$ActualKgs',`AvalKgs`='$ActualKgs', //uma 04-10-2024
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $this->auditLogger->logUpdate('CsPackingStoreData', $StockID, $oldPackRow, $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID), $RequestBody);
                if ($stmt) {
                    $RequestBody['BarCodeID'] = $BarCodeID;
                    $this->Error = null;
                    $this->Msg = 'Updated Successfully';
                    return $this->getStockDetails($RequestBody);
                }
            /* } else {
                $this->Error = null;
                $this->Msg = 'No Access To Edit';
                // return $Data;
                return $this->getStockDetails($RequestBody);
            } */
        }



        // else {
        //     return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        // }
    }
    function truncateDecimal($number, $decimals = 2) {
        $factor = pow(10, $decimals);
        return ($number * $factor) / $factor;
    }

    function getNewBarcode($RequestBody = array() )
    {



        $SqlBarCodes = "SELECT BarCodeID FROM CsBarCodes ORDER by BarCodeID DESC LIMIT 1";
        $StmtBarCodes = $this->conn->prepare($SqlBarCodes);
        $StmtBarCodes->execute();
        $RecentBarCode = $StmtBarCodes->fetch(PDO::FETCH_ASSOC);
        $LastBarCodeID = $RecentBarCode['BarCodeID'];

        if ($LastBarCodeID == '') {
            $LastBarCodeID = 1;
        } else {
            $LastBarCodeID = $LastBarCodeID + 1;
        }


        date_default_timezone_set("Asia/Kolkata");
        $BarCodeDate = date("Y-m-d");
        $BarCodeValue = str_replace("-", "", $BarCodeDate);
        $BarCodeValue = $BarCodeValue . $LastBarCodeID;

        $sqlInsertBarCodeData = "INSERT INTO `CsBarCodes`(`BarCodeDate`, `BarCodeValue`, `BarCodeStatus`, `OnFloor`, `PostedUserID`)
        VALUES ('$BarCodeDate','$BarCodeValue','1','0','1')";

        $stmtInsertBarCodeData = $this->conn->prepare($sqlInsertBarCodeData);
        $stmtInsertBarCodeData->execute();
        $BarCodeID = $this->conn->lastInsertId();
        $this->auditLogger->logInsert('CsBarCodes', $BarCodeID, $this->auditLogger->fetchRowById('CsBarCodes', 'BarCodeID', $BarCodeID), $RequestBody);
        return $BarCodeID;
    }

    public function deleteStockData($RequestBody)
    {

        $StockID = $RequestBody['StockID'];
        $ProductionID = $RequestBody['ProductionID'];
        $BarCodeID = $RequestBody['BarCodeID'];

        $oldPackRow2 = $this->auditLogger->fetchRowById('CsPackingStoreData', 'ID', $StockID);
        $sqlDelete = "DELETE FROM CsPackingStoreData WHERE ID=$StockID";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        $this->auditLogger->logDelete('CsPackingStoreData', $StockID, $oldPackRow2,$RequestBody);

        if ($stmtDelete) {
            $this->Error = null;
            $this->Msg = 'Deleted Successfully';
            return $this->getStockDetails($RequestBody);
        } else {
            $this->Error = 'Unable to Delete';
            $this->Msg = 'Unable to Delete';
            return $this->getStockDetails($RequestBody);
        }
    }

    public function getPoNumbers($RequestBody)
    {
        $Plant = $RequestBody['ProductionPlantID'];
        $Query = '';
        if ($Plant != '') {
            $Query = "AND ProductionPlantID=$Plant";
        }
        $sqlQuery = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
        left join ScrmISFDetails  ISF on ISF.PIID=SPD.PoID
        WHERE SPD.Status in (4) AND ISF.FDAContainerStatus REGEXP 'Return Container' $Query order by PoID ASC";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    public function getDrpDowns($type){
        if(!empty($type)){
            $sql = "SELECT `Value` FROM CsInwardGroupParameters WHERE `Type`='$type' AND `Status`=1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }
        return false;
    }
    public function getBrandListByPoId($PoId){
        if(!empty($PoId)){
            $Sql = "SELECT `BrandID`,`BrandName`
                    FROM ScrmBrandsMaster SBM
                    WHERE `BrandID` IN(
                        SELECT `BrandName` FROM ScrmPIVarietyList SVL
                        LEFT JOIN ScrmPoGradeData SPGD ON SPGD.`VarietyID`=SVL.`VarietyID` 
                        WHERE `InvId`=$PoId
                    )";
            $stmt = $this->conn->prepare($Sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }
        return false;
    }
    public function postPlacePalletDetails($RequestBody)
    {
        $BarCodeID = $RequestBody['BarCodeID'];
        $RowID = $RequestBody['RackID'];
        $StockID = $RequestBody['StockID'];

        $sqlBarcodeCount = "SELECT   RackName,BarCodeID,
        (SELECT count(*) FROM `CsStoresRackData` WHERE `BarCodeID`='$BarCodeID') as Count  ,
        (SELECT RackName FROM CsStoresRackData WHERE BarCodeID='$BarCodeID') as FilledRackname FROM `CsStoresRackData` WHERE  RowID='$RowID'";  //uma
        $stmtBarcodeCount = $this->conn->prepare($sqlBarcodeCount);
        $stmtBarcodeCount->execute();

        // $errorInfo = $stmtBarcodeCount->errorInfo();
        // var_dump($errorInfo);

        $dataRow = $stmtBarcodeCount->fetch(PDO::FETCH_ASSOC);
        // var_dump($dataRow);
        // exit;
        $count = $dataRow['Count'];
        $RackName = $dataRow['RackName'];
        $PresentBarCodeID = $dataRow['BarCodeID'];
        $FilledRackname = $dataRow['FilledRackname'];

        if ($PresentBarCodeID != 0) {
            $Data['Error'] = 'This Rack Number  is filled with another Bar Code ' . $PresentBarCodeID;
            $Data['Msg'] = 'Data Upload Failed';

        } else if ($count != 0) {
            $Data['Error'] = 'This Bar Code is filled in another Rack Number ' . $FilledRackname;
            $Data['Msg'] = 'Data Upload Failed';

        } else if ($PresentBarCodeID == 0 && $count == 0) {
            $SqlRackData = "UPDATE `CsStoresRackData` SET `BarCodeID`='$BarCodeID',`PlacedPackingStoreID` = '$StockID', `Status`= 1 WHERE `RowID`='$RowID'";
            $stmtRackData = $this->conn->prepare($SqlRackData);
            $stmtRackData->execute();

            if ($stmtRackData) {
                $Data['Error'] = null;
                $Data['Msg'] = 'Data Updated';
                $sqlUpdateBarcodes = "UPDATE `CsPackingStoreData` SET `StockFloorStatus`= 1, `StoreRackID` = '$RowID' WHERE `BarCodeID`='$BarCodeID' AND `ID`='$StockID'";
                $stmtUpdateBarcodes = $this->conn->prepare($sqlUpdateBarcodes);
                $stmtUpdateBarcodes->execute();
            } else {
                $Data['Error'] = 'Data Upload Failed';
                $Data['Msg'] = 'Data Upload Failed';
            }
        } else {
            // $Data['Error'] = 'the Bar Code is filled in another Rack Number ' . $RackName;
            $Data['Error'] = 'the Bar Code is filled in another Rack Number ' . $FilledRackname;
            // $Data['Error'] = 'hi';
            $Data['Msg'] = 'Data Upload Failed';
        }
        return $Data;
    }
    function ConvertToDummy($RequestBody = []){
        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $PostedUserID = $RequestBody['PostedUser'];
        if(!empty($RequestBody['Dummy'])){
            foreach($RequestBody['Dummy'] as $DummyRow){
                $BarCodeID = $this->getNewBarcode($RequestBody);
                $groupColumn = 'Mcs';$utilizeGrpColmn = 'UtilizedMcs';
                if($DummyRow['group'] == 'Pouches'){
                    $groupColumn = 'Pouches';
                    $utilizeGrpColmn = 'UtilizedPouches';
                }
                $val = $DummyRow['val'];
                $id = $DummyRow['id'];
                $PackingType = '`PackingTypeID`';
                if($DummyRow['PackingType']){
                    $PackingType = "'".$DummyRow['PackingType']."'";
                }
                $PoNo = "''";
                if($DummyRow['PoNo']){
                    $PoNo = $DummyRow['PoNo'];
                }
                $Brand = '`Brand`';
                if($DummyRow['Brand']){
                    $Brand = $DummyRow['Brand'];
                }
                $sqlQuery = "INSERT INTO CsPackingStoreData 
                        (`TransactionDate`,`PoNo`,`PackingStyleID`,`Brand`,`PlantID`,`UnitID`,`BillNo`,`BarCodeID`,`Group`,Grade,$groupColumn,`Sizes`,`Colour`,DummyPOID,`CreatedOn`,`CreatedBy`,SlabWeight,PackingTypeID,Variety)

                        SELECT `TransactionDate`,$PoNo AS `PoNo`, `PackingStyleID`,$Brand AS `Brand`,`PlantID`,`UnitID`,`BillNo`,'$BarCodeID' AS `BarCodeID`,`Group`,Grade,'$val' AS groupColumn,`Sizes`,`Colour`,PoNo
                        ,'$CreatedDateTime' AS `CreatedOn`,'$PostedUserID' AS `CreatedBy`,SlabWeight,$PackingType as PackingTypeID,Variety
                        FROM CsPackingStoreData WHERE ID = $id";
                        //echo '<pre>';print_r($sqlQuery);die;
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $StockID = $this->conn->lastInsertId();
                if($StockID ){
                    //$UpdateSql = "UPDATE CsPackingStoreData SET DummyStock=$val ,UpdatedBy='$PostedUserID',UpdatedOn='$CreatedDateTime' WHERE ID = $id";
                    $UpdateSql = "UPDATE CsPackingStoreData SET DummyStock= (
                                    SELECT (`DummyStock`+$val) FROM `CsPackingStoreData` WHERE `ID`= $id
                                ) ,UpdatedBy='$PostedUserID',UpdatedOn='$CreatedDateTime' WHERE ID = $id";
                    $stmt = $this->conn->prepare($UpdateSql);
                    $updateStatus = $stmt->execute();
                    if(!empty($updateStatus)){
                        $this->UpdateRackStatus($id);
                    }
                }
            }
        }
        if(!empty($RequestBody['Damage'])){
            foreach($RequestBody['Damage'] as $DamageRow){                
                $id = $DamageRow['id'];
                $val = $DamageRow['val'];
                $groupColumn = 'Mcs';$utilizeGrpColmn = 'UtilizedMcs';
                if($DamageRow['group'] == 'Pouches'){
                    $groupColumn = 'Pouches';
                    $utilizeGrpColmn = 'UtilizedPouches';
                }
                $UpdateSql2 = "UPDATE CsPackingStoreData SET DamagedStock= (
                                    SELECT (`DamagedStock`+$val) FROM `CsPackingStoreData` WHERE `ID`= $id
                                ) ,UpdatedBy='$PostedUserID',UpdatedOn='$CreatedDateTime' WHERE ID = $id";
                $stmt2 = $this->conn->prepare($UpdateSql2);                
                $updateStatus2 = $stmt2->execute();
                if(!empty($updateStatus2)){
                    $this->UpdateRackStatus($id);
                }
            }
        }
        if(!empty($RequestBody['IssuedQty'])){
            foreach($RequestBody['IssuedQty'] as $IssuedQtyRow){                
                $id = $IssuedQtyRow['id'];
                $val = $IssuedQtyRow['val'];
                $group = $IssuedQtyRow['group'];
                $columnName = $utilizeColumnName = '';$utilizeColumnNameMain = '';
                $UtilizeColumnName = $IssuedQtyRow['UtilizeColumnName'];
                if($IssuedQtyRow['group']){
                    if($IssuedQtyRow['group'] == 'Mcs'){
                        $columnName = 'Mcs'; 
                        $utilizeColumnNameMain = 'UtilizedMcs';
                        $utilizeColumnName = '(`UtilizedMcs`+`DummyStock`+`DamagedStock`)';
                    }elseif($IssuedQtyRow['group'] == 'Pouches'){
                        $columnName = 'Pouches'; 
                        $utilizeColumnNameMain = 'UtilizedPouches';
                        $utilizeColumnName = '(`UtilizedPouches`+`DummyStock`+`DamagedStock`)';
                    }elseif($IssuedQtyRow['group'] == 'Spacers'){
                        $columnName = 'Spacers'; 
                        $utilizeColumnNameMain = $utilizeColumnName = 'UtilizedSpacers';
                    }elseif(in_array($IssuedQtyRow['group'],['Stickers','Tape Rolls','Count Slips'])){
                        $columnName = 'Count'; 
                        $utilizeColumnNameMain = $utilizeColumnName = 'UtilizedCountVal';
                    }
                    $UpdateSql2 = "UPDATE CsPackingStoreData SET $utilizeColumnNameMain= (
                                    SELECT (`$utilizeColumnNameMain`+$val) FROM `CsPackingStoreData` WHERE `ID`= $id
                                ) ,UpdatedBy='$PostedUserID',UpdatedOn='$CreatedDateTime' WHERE ID = $id";
                    $stmt2 = $this->conn->prepare($UpdateSql2);                
                    $updateStatus2 = $stmt2->execute();
                    $selectSql = "SELECT ($columnName - $utilizeColumnName) AS Diff FROM `CsPackingStoreData` WHERE `ID`= $id";
                    $stmtSelect = $this->conn->prepare($selectSql);
                    $stmtSelect->execute();//Fetching all stock ids in side the rack
                    $res = $stmtSelect->fetch(PDO::FETCH_ASSOC);
                    $diff =  $res['Diff'];
                    if($diff <= 0){
                        $this->UpdateRackStatus($id);
                    }
                }
            }
        }
        return true;
    }
    function UpdateRackStatus($id){
        if(!empty($id)){
            $rackPlaceIds = "SELECT `PlacedPackingStoreID` FROM `CsStoresRackData` WHERE FIND_IN_SET($id,`PlacedPackingStoreID`)";
            $stmtrack = $this->conn->prepare($rackPlaceIds);
            $stmtrack->execute();//Fetching all stock ids in side the rack
            $PlacedPackingStoreID = $stmtrack->fetch(PDO::FETCH_ASSOC)['PlacedPackingStoreID'];
            if(!empty($PlacedPackingStoreID)){
                $selectSql = "SELECT SUM(CASE 
                                    WHEN `Group` = 'Mcs' THEN (Mcs - (`UtilizedMcs`+`DummyStock`+`DamagedStock`)) 
                                    WHEN `Group` = 'Pouches' THEN (`Pouches` - (`UtilizedPouches`+`DummyStock`+`DamagedStock`)) 
                                    WHEN `Group` = 'Spacers' THEN (`Spacers` - UtilizedSpacers) 
                                    WHEN FIND_IN_SET(`Group`,'Count Slips,Stickers,Tape Rolls') THEN (`Count` - `UtilizedCountVal`)
                                    END)
                                    AS diff, StoreRackID
                                FROM CsPackingStoreData WHERE `ID` IN($PlacedPackingStoreID)
                                GROUP BY StoreRackID ";
                $stmtSelect = $this->conn->prepare($selectSql);
                $stmtSelect->execute();
                $data = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
                foreach($data as $row){
                    if($row['diff'] <= 0){
                        $StoreRackID = $row['StoreRackID'];
                        $SqlRackData = "UPDATE `CsStoresRackData` SET `Status` ='0', BarCodeID='0',PlacedPackingStoreID='' WHERE RowID ='$StoreRackID'";
                        $StmtRackData = $this->conn->prepare($SqlRackData);
                        $StmtRackData->execute();
                    }
                }
            }            
        }
    }    
    /*from inward page*/ 
    function updateDamageQty($RequestBody){
        if(!empty($RequestBody['DamagedStock']) && !empty($RequestBody['fieldType'])){
            $AryCurrentDate = getCurrentDateTime(); 
            $CreatedDateTime = $AryCurrentDate['TimeStamp'];
            $PostedUserId = $RequestBody['PostedUserId'];
            $avlUsedQty = $this->getAvlUsedQty($RequestBody['Unit'], $RequestBody['PackingStyle'], $RequestBody['group'], $RequestBody['fieldType']);
            $column = $RequestBody['fieldType'];//3-2-1/
            $damageVal = $damageQty = $RequestBody['DamagedStock'];//3
            foreach($avlUsedQty as $row){
                $damageQty += $row['DamagedStock'];//3+0=3
                $damageVal = $row['ReUsedQty'] - $damageVal;//4-3=1
                //echo '<pre>'; print_r($row);die;
                if($damageVal < 0){
                    $damageVal = abs($damageVal);//-1
                    $updateSql = "UPDATE CsPackingStoreData SET  ReUsedQty=0,DamagedStock='".($row['ReUsedQty'] + $row['DamagedStock'])."',`UpdatedOn`='$CreatedDateTime',`UpdatedBy`='$PostedUserId' WHERE `ID`='".$row['ID']."'";
                }else{
                    $updateSql = "UPDATE CsPackingStoreData SET  ReUsedQty=$damageVal,DamagedStock='$damageQty',`UpdatedOn`='$CreatedDateTime',`UpdatedBy`='$PostedUserId' WHERE `ID`='".$row['ID']."'";
                    $damageVal = 0;
                }               
                $damageQty = $damageVal;//1
                $updateSql = $this->conn->prepare($updateSql);
                $updateSql->execute();
            }
            if($damageVal == 0){
                $data = array('Error' => null, 'Msg' => 'Successfully Updated.');
            }else{
                $data = array('Error' => null, 'Msg' => 'Failed to Updated.');
            }
            return $data;
        }
        $data = array('Error' => 'Parameters Missed, Please check once', 'Msg' => null);
        return $data;
    }
    function getAvlUsedQty($ColdStores, $PackingStyleID, $group, $columnName){
        if(!empty($ColdStores) && !empty($PackingStyleID) && !empty($group) ){
            $PackColumn = " AND PackingStyleID=$PackingStyleID ";
            if($group == 'Pouches'){
                $PackColumn = " AND SlabWeight=(SELECT CONCAT(SlabWeight,' ',Uom) FROM AdminPackingStyles WHERE `PCKID`=$PackingStyleID) ";
            }
            $sql = "SELECT ID,`ReUsedQty`,CASE 
                        WHEN ReUsedQtyUtilizedCount IS NULL OR ReUsedQtyUtilizedCount = '' 
                        THEN 0
                        ELSE LENGTH(ReUsedQtyUtilizedCount) - LENGTH(REPLACE(ReUsedQtyUtilizedCount, ',', '')) + 1
                        END -- calculating comma seperated value
                        AS UtilizeVal,DamagedStock 
                    FROM CsPackingStoreData 
                    WHERE `Group` = '$group' 
                    $PackColumn
                    AND  `UnitID` = $ColdStores                    
                    AND (ReUsedQty != '' OR ReUsedQty != '0' OR ReUsedQty IS NULL)
                    ORDER BY `ID` ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    /*from inward page end*/ 
    function getSlabWeight()
    {
        $sqlQuery = "SELECT DISTINCT(CONCAT(`SlabWeight`,' ',`Uom`)) AS Weight FROM AdminPackingStyles WHERE `Status`=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getVariety(){
        $sqlQuery = "SELECT `ShortName` FROM `ScrmPIVarietyList` GROUP BY `ShortName`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
