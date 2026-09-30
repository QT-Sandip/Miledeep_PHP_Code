<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RmBookingController
{
    private $conn;
    private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error=null;
    private $Msg='';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

	public function getBookingDetails($RequestBody)
    {
		$BookingID=$RequestBody['BookingID'];
		$BreakUpData=$this->getBookingBreakUp($BookingID);
		$BookingData=$this->getBookingData($BookingID);
		$Data['Error']=null;
		$Data['Msg']=null;
		$Data['BreakUpData']=$BreakUpData;
		$Data['BookingData']=$BookingData;

		return $Data;
	}

    public function postBookingDetails($RequestBody)
    {

		
		
            $PostedUserID = $RequestBody['PostedUserID'];
			$BookingNumber = $RequestBody['BookingNumber'];
			$BookingStationID = $RequestBody['BookingStationID'];
			$VehicalID = $RequestBody['VehicalID'];
			$AgentID = $RequestBody['AgentID'];
			$DriverID =$RequestBody['DriverID'];
			$GraderID = $RequestBody['GraderID'];
			$BookingID = $RequestBody['BookingID'];
			$Species = $RequestBody['Species'];
			
			$PurchaseCategoryID=$RequestBody['PurchaseCategoryID'];

			$FarmAddress=$RequestBody['FarmAddress'];
			$BookingDate=date("Y-m-d", strtotime($RequestBody['BookingDate']));

			if($BookingID==''){			
			$BookingNumber=$this->utilController->getFormatedBookingNumber($BookingStationID,$BookingNumber);
			$BookingNoAval=$this->utilController->checkBookingNoAval($BookingDate,$BookingNumber,$BookingID);
			
			if(!$BookingNoAval){			
                $this->Error="Booking Number Already Exist";
				}else if($PostedUserID==''){
				$this->Error="Session Expired,Please Login and Try Again";
				}	
			}
			
								
			
			
			$AryCurrentDate=getCurrentDateTime();
			
			$CreatedDateTime=$AryCurrentDate['TimeStamp'];			
			$Year=$AryCurrentDate['Year'];	
			
			$stmt=false;
			
			if($this->Error==null && $BookingID==''){
				$sqlQuery="INSERT INTO `RmBookingDetatils`
				(`PostedUserID`, `Species`,`BookingNumber`, 
				`BookingStationID`, `VehicalID`, `AgentID`,
				`DriverID`, `GraderID`, `PurchaseCategoryID`,
			    `FarmAddress`,`CreatedDateTime`, `BookingDate`, 
				`LotStatus`) VALUES  
				('$PostedUserID','$Species','$BookingNumber',	
				'$BookingStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID',
				'$FarmAddress','$CreatedDateTime','$BookingDate',
				'1')";																
				
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				$BookingID = $this->conn->lastInsertId();
                
            }else if($BookingID!=''){
				$sqlQuery="Update `RmBookingDetatils` set
				 `BookingNumber`='$BookingNumber',
				 `Species`='$Species',
				`BookingStationID`='$BookingStationID',
				 `VehicalID`='$VehicalID',
				 `AgentID`='$AgentID',
				 `DriverID`='$DriverID',
				`GraderID`='$GraderID',
				`PurchaseCategoryID`='$PurchaseCategoryID',
			    `FarmAddress`='$FarmAddress',
				`CreatedDateTime`='$CreatedDateTime',
				`BookingDate`='$BookingDate', 
				`IsEdited`=1 where 	RmBookingID ='$BookingID'";																
				
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();

              
            }

			
			if($stmt){                  
				
				$this->postBookingBreakUp($BookingID,$RequestBody);
				//$BreakUpData=$this->getBookingBreakUp($BookingID);
				return $this->getBookingDetails($RequestBody);
			}else{
				return $Data = array('Error' => $this->Error,"Msg" =>$this->Error);
			}

				/*if($stmt){			
                    $Data = array('Error' => null ,"Msg" => "Booking Posted Successful", "BreakUpData"=>$BreakUpData);			
				}else{
                    $Data = array('Error' => $this->Error,"Msg" =>$this->Error);		
				}*/

       // return $Data;
    }

    function postBookingBreakUp($BookingID,$RequestBody){

		$BookingCount=0;
		$BookingWeight=0;
		$BookingRate=0;

		if(isset($RequestBody['BookingCount'])){
        $BookingCount=$RequestBody['BookingCount'];
		}
		if(isset($RequestBody['BookingWeight'])){
        $BookingWeight=$RequestBody['BookingWeight'];
		}
		if(isset($RequestBody['BookingRate'])){
        $BookingRate=$RequestBody['BookingRate'];
		}

       

		if($BookingCount> 0 &&  $BookingWeight >0 && $BookingRate>0){

            $sqlQuery="INSERT INTO `RmBookingBreakUpDetails`
				( `BookingID`, `Count`, `Weight`, `Rate`) VALUES  
				('$BookingID',' $BookingCount','$BookingWeight','$BookingRate')";																
				
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
		}

    }


	function getBookingData($BookingId){
		$sqlFields="`RmBookingID`,`Species`, `PostedUserID`, `BookingNumber`, `BookingStationID`, `VehicalID`, `AgentID`, `DriverID`, `GraderID`, `PurchaseCategoryID`, `FarmAddress`, `CreatedDateTime`, `BookingDate`, `IsEdited`, `LotStatus`";
            $sqlQuery="SELECT  $sqlFields FROM `RmBookingDetatils` WHERE  RmBookingID='$BookingId'";																	
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
					$Data[]=$dataRow;
				}
				return $Data;
    }

	function getBookingBreakUp($BookingId){
		$sqlFields="`BookingBrkUpID`, `BookingID`, `Count`, `Weight`, `Rate`, `BrkUpStatus`";
            $sqlQuery="select $sqlFields  from `RmBookingBreakUpDetails` where BookingID='$BookingId'";																	
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
					$Data[]=$dataRow;
				}

				return $Data;
    }
}
?>