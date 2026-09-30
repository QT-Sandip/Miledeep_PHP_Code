<?php

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmProductionListController
{

    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetRmArrivalListDetails($RequestBody)
    {

        $ArrivalDate = $RequestBody['ArrivalDate'];
         $Type = $RequestBody['Type'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $Supplier = $RequestBody['Supplier'];
        $PsId = $RequestBody['PsId'];
        $DependantLots = $RequestBody['DependantLots']; //need to get this key from particular page
        $RequestFrom = $RequestBody['RequestFrom'];
        $ConsolidateLotCount = $RequestBody['ConsolidateLotCount'];

        $DependantLots = true;
        $Query = '';





        if (isset($ArrivalPlant) && $ArrivalPlant != '') {
            $Query .= " and RLD.ProcessingStationID='$ArrivalPlant'";
        }

        if (isset($Supplier) && $Supplier != '') {
            $Query .= " and RLD.RmSupplierID='$Supplier'";
        }

        if (isset($PsId) && $PsId != '') {
            $Query .= " and RLD.ArrivalStationID='$PsId'";
        }

        // if($DependantLots){


        if ($Type == 'ValueAddition' || $Type == 'Soaking') {
            $where = '';
            if($Type == 'ValueAddition'){
                $where = " WHERE CreatedFrom=1";
                if($RequestBody['Version'] == 0){
                    $where = " WHERE CreatedFrom=0";
                }

            }
            $Query .= " and RmLotID in (SELECT LotID FROM `PpcHLGradingDetails` $where)";
        }

        else if ($Type == 'Grading'){
            $Query .= " and RmLotID in (SELECT LotID FROM `PpcDeheading`  WHERE CreatedFrom=1)";
        }else if ($Type == 'HON_PACKING'){
            $Query .= " and RLD.PurchaseCategoryID=2 ";
        }
        // }

        $SqlFields = " ArrivalNumber,RLD.RmLotID as RmArrivalID,ArrivalDate,
        ProductionWeight ,RLD.PurchaseCount,PurchaseWeight ";
        if($RequestFrom == 'V2' && $ConsolidateLotCount == 0){
            $SqlFields .= ',SUM(ProductionWeight) AS ProductionWeight,SUM(PurchaseWeight) AS PurchaseWeight';
            $groupBy = 'GROUP BY RLD.RmReferenceID,RLD.PurchaseCount';
        }else{
            $SqlFields .= ',ProductionWeight,PurchaseWeight';
            $groupBy = '';
        }

        // echo
         $sqlRmArrivalQuery = "SELECT  $SqlFields FROM " . $this->db_table . " as RAD 
        LEFT JOIN RmLotDetatils as RLD on RAD.RmArrivalID=RLD.RmReferenceID 
        WHERE RAD.ArrivalDate='$ArrivalDate'  $Query and RLD.LotStatus='1' 
        $groupBy
        order by ArrivalDate,ArrivalNumber desc, PurchaseCount asc";
        // and RLD.LotStatus='1'
        // echo $sqlRmArrivalQuery;
        // EXIT;
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrivalID = $dataRow['RmArrivalID'];
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];

            $ArrayBookingList['RmArrivalID'] = $ArrivalID;


            $ArrivalCount = $dataRow['PurchaseCount'] == '0.00' ? $dataRow['PurchaseCount'] : $dataRow['Count'];
            if ($dataRow['PurchaseCount'] > 0) {
                $ArrivalCount = $dataRow['PurchaseCount'];
            } else {
                $ArrivalCount = $dataRow['Count'];
            }

            if ($dataRow['ProductionWeight'] > 0) {
                $ArrivalWeight = $dataRow['ProductionWeight'];
            } else {
                $ArrivalWeight = $dataRow['PurchaseWeight'];
            }


            $ArrayBookingList['ArrivalCount'] = sprintf('%0.2f', $ArrivalCount);
            $ArrayBookingList['ArrivalWeight'] = sprintf('%0.3f', $ArrivalWeight);


            $Data[] = $ArrayBookingList;
        }
        return $Data;
    }
}
