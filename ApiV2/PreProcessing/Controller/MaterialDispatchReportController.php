<?php 
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';


class MaterialDispatchReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->restApi = new REST();

    }
    function getToken()
	{
		$authToken = $this->restApi->getRequestHeader();
		return $payload = Token::Verify($authToken);
	}

    function getMaterialDispatch($RequestBody)
    {
        $Data=[];
        $DataType=$RequestBody['DataType'];
       
        // if($DataType=="getArrivals"){
        //     $Data['ArrivalsList'] =$this->getArrivals($RequestBody);
        //  }

        //  else if($DataType=="getDispatchMaterialList"){            

            $Data['DispatchMaterialList'] =$this->getDispatchMaterialDetails($RequestBody);
            // 
        //  }

        if (empty($this->Msg)) {
            // Only set Msg if it's not already set
            $this->Msg = "Success";  // Default message
        }
       

        $responseData['Error'] = $this->Error;
        $responseData['Msg'] = $this->Msg;

        $responseData['Data'] = $Data;

        return $responseData;
    }
  
    function getDispatchMaterialDetails($RequestBody)
    {

        $FromStation = $RequestBody['FromStation'];
        $ToStation = $RequestBody['ToStation'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DispatchMaterialID = $RequestBody['DispatchMaterialID'];     
        $Query="";

        // if($DispatchMaterialID==''){
            $DateFilter=" TransferredDate Between '$FromDate' and '$ToDate'";

            if($ToStation!=''){
                $Query .= " and ToStationID='$ToStation'";
            }
            if($FromStation!=''){
               $Query .=" and  FromStationID='$FromStation' "; 
            }

            
        // }
//         else{
// $Query .=" MaterialDispatchID ='$DispatchMaterialID'";
//         }
             

        $sqlFields = " PVSF.UnitName as FromStation,PVST.UnitName as ToStation,TransferredDate,DATE_FORMAT(PGMD.TransferredDate, '%d-%m-%Y') AS FormattedTransferDate, 
        PGMD.Grade, PHVL.ShortName as Variety,Quantity,DispatchedCrates,ReceivedCrates,PGMD.Status,PGMD.VarietyID,FromStationID,ToStationID,MaterialDispatchID,PGMD.ArrivalID,
        RAD.ArrivalNumber ";  

        $sqlQuery = "SELECT $sqlFields
FROM `PpcGradingMaterialDispatchData` PGMD
left join PpcVAStations PVSF on PVSF.StationID=PGMD.FromStationID
left join PpcVAStations PVST on PVST.StationID=PGMD.ToStationID
left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=PGMD.VarietyID
left join RmArrivalDetatils as RAD on RAD.RmArrivalID=PGMD.ArrivalID
WHERE  $DateFilter $Query  ";  

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
       

        // $i = 0;
        $ArrayMaterialDispatchData=[];
        
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
//             $EditAndDeleteAccess=1;
//             $StatusName="Pending";
//             $StatusColour="red";


//             $Status=$dataRow['Status'];

//             if($Status==1){
// $EditAndDeleteAccess=0;
// $StatusName="Received";
// $StatusColour="green";
//             }
          
            $MaterialDispatchData['SNo'] = ++$i;
            $MaterialDispatchData['BatchNumber'] = $dataRow['ArrivalNumber'];          
            $MaterialDispatchData['FromStationName'] = $dataRow['FromStation']; 
            $MaterialDispatchData['ToStationName'] = $dataRow['ToStation']; 
            $MaterialDispatchData['FormattedTransferDate'] = $dataRow['FormattedTransferDate']; 
            $MaterialDispatchData['Grade'] = $dataRow['Grade']; 
            $MaterialDispatchData['VarietyName'] = $dataRow['Variety']; 
            $MaterialDispatchData['Quantity'] = $dataRow['Quantity']; 
            $MaterialDispatchData['DispatchedCrates'] = $dataRow['DispatchedCrates']; 



            // $MaterialDispatchData['MaterialDispatchID'] = $dataRow['MaterialDispatchID']; 
            // $MaterialDispatchData['ArrivalID'] = $dataRow['ArrivalID']; 
            
            // $MaterialDispatchData['FromStationID'] = $dataRow['FromStationID']; 
            // $MaterialDispatchData['ToStationID'] = $dataRow['ToStationID']; 
            // $MaterialDispatchData['TransferredDate'] = $dataRow['TransferredDate']; 
           
            // $MaterialDispatchData['VarietyID'] = $dataRow['VarietyID']; 
            // $MaterialDispatchData['ReceivedCrates'] = $dataRow['ReceivedCrates']; 
            // $MaterialDispatchData['Status'] = $Status; 
            // $MaterialDispatchData['StatusName'] = $StatusName; 
            // $MaterialDispatchData['StatusColour'] = $StatusColour; 

            
            // $MaterialDispatchData['EditAndDeleteAccess'] = $EditAndDeleteAccess;             
            // $MaterialDispatchData['Difference'] = $dataRow['ReceivedCrates']-$dataRow['DispatchedCrates']; 

            $ArrayMaterialDispatchData[]=$MaterialDispatchData;

              }
              if (empty($ArrayMaterialDispatchData)) {
                if (empty($this->Msg)) {
                $this->Msg = "No data found."; 
                }// Set the message when no data is found
            } else {
                if (empty($this->Msg)) {
                $this->Msg = "Success"; // Set success message
                }
            }
        
        return $ArrayMaterialDispatchData;
    }


   
}
