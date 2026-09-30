<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmBookingListController
{

    private $conn;
    private $db_table = "RmBookingDetatils";
   private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetRmBookingListDetails($RequestBody)
    {
        $BookingDate = $RequestBody['BookingDate'];
        $Type = $RequestBody['Type'];
        $PsId = $RequestBody['PsId'];

        $Query='';

        
        
        if($PsId!=''){
            $Query="and RLD.BookingStationID='$PsId'";
        }

        $SqlFields = " RmBookingID,GraderID,AgentID,AbStatus,Name,Colour,
        PurchaseType,BookingNumber,LotStatus,FarmAddress
        ,BookingDate";

       $sqlBanksQuery = "SELECT  $SqlFields FROM " . $this->db_table . " as RLD 
        left join RmPurchaseStations as RPS on RLD.BookingStationID=RPS.PurchaseStationID 
        left join QcRmAbReport as QCAB on RLD.RmBookingID=QCAB.LotID
        left join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus
        left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID
       where BookingDate ='$BookingDate' and LotStatus='1' $Query order by BookingDate,BookingNumber desc"; 
        $stmt = $this->conn->prepare($sqlBanksQuery);
        $stmt->execute();
      
        $i = 0;
        $Data=array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['BookingID'] = $dataRow['RmBookingID'];
            $ArrayBookingList['Species'] = 'VM';
            $ArrayBookingList['BookingNumber'] = $dataRow['BookingNumber'];
            $ArrayBookingList['BookingDate'] = $dataRow['BookingDate'];
            $ArrayBookingList['PurchaseType'] = $dataRow['PurchaseType'];
            $GraderID = $dataRow['GraderID'];
            $ArrayBookingList['Grader'] = $this->utilController->getUserName($GraderID);
            $AgentID = $dataRow['AgentID'];
            $ArrayBookingList['Agent'] = $this->utilController->getUserName($AgentID);
            $RmBookingID = $dataRow['RmBookingID'];
            $WeightDetails=$this->getBreakUpDetails($RmBookingID);
            $ArrayBookingList['BookingCount'] = $WeightDetails['BookingCount'];
            $ArrayBookingList['BookingWeight'] =$WeightDetails['BookingWeight'];
            $ArrayBookingList['BookingRate'] =$WeightDetails['BookingRate'];
            $Data[] = $ArrayBookingList;


        }
        return $Data;
    }


    
    public function getBreakUpDetails($BookingID)
    {
       
        $sqlQuery = "SELECT GROUP_CONCAT(Count) as BookingCount,GROUP_CONCAT(Weight) as BookingWeight,GROUP_CONCAT(Rate) as BookingRate 
        FROM `RmBookingBreakUpDetails` WHERE BookingID='$BookingID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        
        if( $stmt->rowCount() ==0){
            $data['BookingCount']='NA';
            $data['BookingWeight']='NA';
            $data['BookingRate']='NA';
      
        }else{
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['BookingCount']=$dataRow['BookingCount'];
            $data['BookingWeight']=$dataRow['BookingWeight'];
            $data['BookingRate']=$dataRow['BookingRate'];
       }	
        return $data;
    }

}
?>
