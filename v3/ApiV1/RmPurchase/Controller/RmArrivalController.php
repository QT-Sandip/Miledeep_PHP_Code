<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class RmArrivalController
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

	public function getArrivalDetails($RequestBody)
    {
		$ArrivalID=$RequestBody['ArrivalID'];
		$BreakUpData=$this->getArrivalBreakUp($ArrivalID);
		$ArrivalData=$this->getArrivalData($ArrivalID);
		$Data['Error']=null;
		$Data['Msg']=null;
		$Data['BreakUpData']=$BreakUpData;
		$Data['ArrivalData']=$ArrivalData;

		return $Data;
	}

    public function postArrivalDetails($RequestBody)
    {
		
            $PostedUserID = $RequestBody['PostedUserID'];
			$ArrivalNumber = $RequestBody['ArrivalNumber'];
			$ArrivalStationID = $RequestBody['ArrivalStationID'];
			$VehicalID = $RequestBody['VehicalID'];
			$AgentID = $RequestBody['AgentID'];
			$DriverID =$RequestBody['DriverID'];
			$GraderID = $RequestBody['GraderID'];
			$ArrivalID = $RequestBody['ArrivalID'];
			
			$PurchaseCategoryID=$RequestBody['PurchaseCategoryID'];

			$FarmAddress=$RequestBody['FarmAddress'];
			$ArrivalDate=date("Y-m-d", strtotime($RequestBody['ArrivalDate']));

			if($ArrivalID==''){			
			$ArrivalNumber=$this->utilController->getFormatedArrivalNumber($ArrivalStationID,$ArrivalNumber);
			$ArrivalNoAval=$this->utilController->checkArrivalNoAval($ArrivalDate,$ArrivalNumber,$ArrivalID);
			
			if(!$ArrivalNoAval){			
                $this->Error="Arrival Number Already Exist";
				}else if($PostedUserID==''){
				$this->Error="Session Expired,Please Login and Try Again";
				}	
			}
			
								
			
			
			$AryCurrentDate=getCurrentDateTime();
			
			$CreatedDateTime=$AryCurrentDate['TimeStamp'];			
			$Year=$AryCurrentDate['Year'];	
			
			$stmt=false;
			
			if($this->Error==null && $ArrivalID==''){
				$sqlQuery="INSERT INTO `RmArrivalDetatils`
				(`PostedUserID`, `ArrivalNumber`, 
				`ArrivalStationID`, `VehicalID`, `AgentID`,
				`DriverID`, `GraderID`, `PurchaseCategoryID`,
			    `FarmAddress`,`CreatedDateTime`, `ArrivalDate`, 
				`LotStatus`) VALUES  
				('$PostedUserID','$ArrivalNumber',	
				'$ArrivalStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID',
				'$FarmAddress','$CreatedDateTime','$ArrivalDate',
				'1')";																
				
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				$ArrivalID = $this->conn->lastInsertId();
                
            }else if($ArrivalID!=''){
				$sqlQuery="Update `RmArrivalDetatils` set
				 `ArrivalNumber`='$ArrivalNumber',
				`ArrivalStationID`='$ArrivalStationID',
				 `VehicalID`='$VehicalID',
				 `AgentID`='$AgentID',
				 `DriverID`='$DriverID',
				`GraderID`='$GraderID',
				`PurchaseCategoryID`='$PurchaseCategoryID',
			    `FarmAddress`='$FarmAddress',
				`CreatedDateTime`='$CreatedDateTime',
				`ArrivalDate`='$ArrivalDate', 
				`IsEdited`=1 where 	RmArrivalID ='$ArrivalID'";																
				
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();

              
            }

			

			if($stmt){                  
				
				$this->postArrivalBreakUp($ArrivalID,$RequestBody);
				$BreakUpData=$this->getArrivalBreakUp($ArrivalID);
			}

				if($stmt){			
                    $Data = array('Error' => null ,"Msg" => "Arrival Posted Successful", "BreakUpData"=>$BreakUpData,"ArrivalData"=>'');			
				}else{
                    $Data = array('Error' => $this->Error,"Msg" =>$this->Error);		
				}

        return $Data;
    }

    function postArrivalBreakUp($ArrivalID,$RequestBody){
        $ArrivalCount=$RequestBody['ArrivalCount'];
        $ArrivalWeight=$RequestBody['ArrivalWeight'];
        $ArrivalRate=$RequestBody['ArrivalRate'];
       
		if($ArrivalCount> 0 &&  $ArrivalWeight >0 && $ArrivalRate>0){

            $sqlQuery="INSERT INTO `RmArrivalBreakUpDetails`
				( `ArrivalID`, `Count`, `Weight`, `Rate`) VALUES  
				('$ArrivalID',' $ArrivalCount','$ArrivalWeight','$ArrivalRate')";																
				
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
		}

    }


	function getArrivalData($ArrivalID){
		$sqlFields="`RmArrivalID`, `PostedUserID`, `ArrivalNumber`, `ArrivalStationID`, `VehicalID`, `AgentID`, `DriverID`, `GraderID`,
         `PurchaseCategoryID`, `FarmAddress`, `CreatedDateTime`, `ArrivalDate`, `IsEdited`, `LotStatus`";
            $sqlQuery="SELECT  $sqlFields FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";																	
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
					$Data[]=$dataRow;
				}
				return $Data;
    }

	function getArrivalBreakUp($ArrivalID){
		$sqlFields="`ArrivalBrkUpID`, `ArrivalID`, `Count`, `Weight`, `Rate`, `BrkUpStatus`";
            $sqlQuery="select $sqlFields  from `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";																	
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                $Data=array();
				while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
					$Data[]=$dataRow;
				}
				return $Data;
    }
}
?>
