<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmArrivalListController
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
        $PsId = $RequestBody['PsId'];

        $Query='';

        
        
        if($PsId!=''){
            $Query="and RLD.ArrivalStationID='$PsId'";
        }

        $SqlFields = " RmArrivalID ,GraderID,AgentID,AbStatus,Name,Colour,
        PurchaseType,ArrivalNumber,LotStatus,FarmAddress
        ,ArrivalDate";

       $sqlRmArrivalQuery = "SELECT  $SqlFields FROM " . $this->db_table . " as RLD 
        left join RmPurchaseStations as RPS on RLD.ArrivalStationID=RPS.PurchaseStationID 
        left join QcRmAbReport as QCAB on RLD.RmArrivalID=QCAB.LotID
        left join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus
        left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID
        where ArrivalDate ='$ArrivalDate' and LotStatus='1' $Query order by ArrivalDate,ArrivalNumber desc"; 
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();
      
        $i = 0;
        $Data=array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayBookingList['PurchaseType'] = $dataRow['PurchaseType'];
            $GraderID = $dataRow['GraderID'];
            $ArrayBookingList['Grader'] = $this->utilController->getUserName($GraderID);
            $AgentID = $dataRow['AgentID'];
            $ArrayBookingList['Agent'] = $this->utilController->getUserName($AgentID);
            $ArrivalID = $dataRow['RmArrivalID'];
            $WeightDetails=$this->getBreakUpDetails($ArrivalID);
            $ArrayBookingList['ArrivalCount'] = $WeightDetails['ArrivalCount'];
            $ArrayBookingList['ArrivalWeight'] =$WeightDetails['ArrivalWeight'];
            $ArrayBookingList['ArrivalRate'] =$WeightDetails['ArrivalRate'];
            $Data[] = $ArrayBookingList;


        }
        return $Data;
    }


    
    public function getBreakUpDetails($ArrivalID)
    {
       
        $sqlQuery = "SELECT GROUP_CONCAT(Count) as ArrivalCount,GROUP_CONCAT(Weight) as ArrivalWeight,GROUP_CONCAT(Rate) as ArrivalRate 
        FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        
        if( $stmt->rowCount() ==0){
            $data['ArrivalCount']='NA';
            $data['ArrivalWeight']='NA';
            $data['BookingRate']='NA';
      
        }else{
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount']=$dataRow['ArrivalCount'];
            $data['ArrivalWeight']=$dataRow['ArrivalWeight'];
            $data['ArrivalRate']=$dataRow['ArrivalRate'];
       }	
        return $data;
    }

}
?>

