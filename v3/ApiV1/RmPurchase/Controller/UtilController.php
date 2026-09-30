<?php

include_once '../Global/Util.php';
class UtilController
{

    private $conn;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getFormatedBookingNumber($BookingStationID,$BookingNumber)
    {
       

        $sqlQuery = "SELECT LotCode FROM `RmPurchaseStations` WHERE PurchaseStationID='$BookingStationID'";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $LotCode=$dataRow['LotCode'];
			$BookingNumber =  str_replace("O","0",$BookingNumber);
			$BookingNumber = ltrim($BookingNumber, "0");
			$BookingNumber = preg_replace("/[^0-9]/", "", $BookingNumber );
			$BookingNumber = sprintf( '%04d', $BookingNumber);			
			$BookingNumber = $LotCode.$BookingNumber;

        return $BookingNumber;
    }


    public function checkBookingNoAval($BookingDate,$BookingNumber,$LotId)
    {
        $Qry='';
            if($LotId!=''){
			$Qry="and RmBookingID!='$LotId'";	
			}
			
			$CheckYear=explode('-', $BookingDate); 		
			
			if($CheckYear[1] > 3){
			$DateStart=$CheckYear[0].'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]+1).'-03-31'; //2022-03-31
			}else{
			$DateStart=($CheckYear[0]-1).'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]).'-03-31'; //2022-03-31
			}

        $sqlQuery = "SELECT * FROM `RmBookingDetatils`WHERE BookingNumber='$BookingNumber' and BookingDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
       
        if( $stmt->rowCount() =='0'){
            return true;
        }else{
            return false;
        }
      
    }

    public function getFormatedArrivalNumber($ArrivalStationID,$ArrivalNumber)
    {
       

        $sqlQuery = "SELECT LotCode FROM `RmPurchaseStations` WHERE PurchaseStationID='$ArrivalStationID'";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $LotCode=$dataRow['LotCode'];
			$ArrivalNumber =  str_replace("O","0",$ArrivalNumber);
			$ArrivalNumber = ltrim($ArrivalNumber, "0");
			$ArrivalNumber = preg_replace("/[^0-9]/", "", $ArrivalNumber );
			$ArrivalNumber = sprintf( '%04d', $ArrivalNumber);			
			$ArrivalNumber = $LotCode.$ArrivalNumber;

        return $ArrivalNumber;
    }

    public function checkArrivalNoAval($ArrivalDate,$ArrivalNumber,$LotId)
    {
        $Qry='';
            if($LotId!=''){
			$Qry="and RmArrivalID!='$LotId'";	
			}
			
			$CheckYear=explode('-', $ArrivalDate); 		
			
			if($CheckYear[1] > 3){
			$DateStart=$CheckYear[0].'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]+1).'-03-31'; //2022-03-31
			}else{
			$DateStart=($CheckYear[0]-1).'-04-01';  //2021-04-01
			$DateEnd=($CheckYear[0]).'-03-31'; //2022-03-31
			}

        $sqlQuery = "SELECT * FROM `RmArrivalDetatils`WHERE ArrivalNumber='$ArrivalNumber' and ArrivalDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
       
        if( $stmt->rowCount() =='0'){
            return true;
        }else{
            return false;
        }
      
    }

    public function getUserName($ID)
    {
       
        $sqlQuery = "SELECT UserName FROM `RmUsersList` WHERE ID='$ID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if( $stmt->rowCount() =='0'){
            $UserName='';
        }else{
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserName=$dataRow['UserName'];
        }	
        return $UserName;
    }





}

?> 