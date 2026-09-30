<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class StoreRackDetailsController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getRackDetails($RequestBody)
    {
        if(!empty($RequestBody['methodType']) && $RequestBody['methodType'] == 'validateRackId'){
           return  $this->getAvailableRackWeight($RequestBody);
        }
        $BlockID = $RequestBody['BlockID'];
        $StoreID = $RequestBody['StoreID'];
        $FloorID = $RequestBody['FloorID'];
        if ($BlockID != '' && $StoreID != '') {
            $StockData = $this->getRackData($RequestBody);
            $FloorData = $this->getFloorData($RequestBody);
        }
        $BlockData = $this->getBlockData($RequestBody);
        $StoresData = $this->getStoresData($RequestBody);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['BlockData'] = $BlockData;
        $Data['FloorData'] = $FloorData;
        $Data['RackData'] = $StockData;
        $Data['StoresData'] = $StoresData;
        return $Data;
    }

    function getRackData($RequestBody)
    {
        $BlockID = $RequestBody['BlockID'];
        $BarCodeID = $RequestBody['BarCodeID'];
        $StoreID = $RequestBody['StoreID'];
        $FloorID = $RequestBody['FloorID'];

        if ($StoreID != '') {
            $Query = " and CSRD.StoreID='$StoreID' "; // 30-04-2024 edited empty rackNames 
        }
        if ($BlockID != '') {
            $Query .= " and CSRD.BlockID='$BlockID' and CSRD.Path !=0 AND CSRD.RackName!=''"; // 30-04-2024 edited empty rackNames 
        }

        if ($FloorID != '') {
            $Query .= " and CSRD.FloorID='$FloorID'";
        }

        $dataRow = null;
        $sqlFields = "CSRD.RowID as RackID,CSRD.BlockID,CSRD.FloorID,CSRD.RackName,CSRD.Status,CSRD.Path";

        $sqlQuery = "SELECT  $sqlFields FROM `CsStoresRackData` AS CSRD
            LEFT JOIN CsStoresFloorData AS CSFD ON CSRD.FloorID = CSFD.FloorID WHERE CSFD.Status = 1 $Query order by RackName";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $AryStockData[] = $dataRow;
        }
        // $AryStockData['Query'] = $sqlQuery;
        return $AryStockData;
    }

    function getFloorData($RequestBody)
    {
        $BlockID = $RequestBody['BlockID'];
        // $StoreID = $RequestBody['StoreID'];


        $dataRow = null;
        $sqlFields = "FloorID,ProcessingStationID,BlockID,FloorName";

        $sqlQuery = "SELECT  $sqlFields FROM `CsStoresFloorData` WHERE FloorID in (SELECT Floors FROM `CsStoresBlockData` WHERE BlockID=$BlockID)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $AryStockData[] = $dataRow;
        }
        return $AryStockData;
    }
    function getBlockData($RequestBody)
    {

        $dataRow = null;
        $sqlFields = "FloorID,ProcessingStationID,BlockID,FloorName";

        $sqlQuery = "SELECT BlockID,ProcessingStationID as PlantID,BlockName FROM `CsStoresBlockData` WHERE BlockStatus=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $AryStockData[] = $dataRow;
        }
        return $AryStockData;
    }
    function getStoresData($RequestBody)
    {

        $dataRow = null;
        // $sqlFields = "FloorID,ProcessingStationID,BlockID,FloorName";

        // $sqlQuery = "SELECT StoreID,StoreName,StoreBlocks FROM `CsStores`";
        $sqlQuery = "SELECT StoreID,StoreName,StoreBlocks FROM CsStores WHERE StoreStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //    $dataRow['StoreBlocks'] = explode(',',$dataRow['StoreBlocks']);
            $AryStockData[] = $dataRow;
        }
        return $AryStockData;
    }
    function getAvailableRackWeight($RequestBody){
        if(!empty($RequestBody['rackId']) && !empty($RequestBody['StockId'])  && !empty($RequestBody['StoreRackMaxMcs'])){
            $StockId = $RequestBody['StockId'];
            $rackId = $RequestBody['rackId'];
            $StoreRackMaxMcs = $RequestBody['StoreRackMaxMcs'];
            $sqlQuery = "SELECT SUM(`AvalMcs`) TotalAvalMcs FROM CsStockData WHERE FIND_IN_SET(`StockID` ,(
                            CONCAT($StockId,',',(SELECT `PlacedStockID` FROM `CsStoresRackData` WHERE RowID IN($rackId)))
                            ))>0";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
               // echo '<pre>';print_r( $stmt->fetch(PDO::FETCH_ASSOC)['TotalAvalMcs']);die;
            $TotalAvalMcs = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!empty( $TotalAvalMcs['TotalAvalMcs'])){
                if($TotalAvalMcs['TotalAvalMcs'] <= $StoreRackMaxMcs){
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Success';
                    $Data['TotalAvalMcs'] = $TotalAvalMcs['TotalAvalMcs'];
                    $Data['StoreRackMaxMcs'] = $StoreRackMaxMcs;
                }else{
                    $Data['Error'] = 'MCS exceeds for the selected rack. Please choose another rack.';
                    $Data['Msg'] = '';
                    $Data['TotalAvalMcs'] = $TotalAvalMcs['TotalAvalMcs'];
                    $Data['StoreRackMaxMcs'] = $StoreRackMaxMcs;
                }
            }else{
                $Data['Error'] = null;
                $Data['Msg'] = 'Success';
                $Data['TotalAvalMcs'] = 0;
                $Data['StoreRackMaxMcs'] = $StoreRackMaxMcs;
            }
            return $Data;
        }
    }
}
