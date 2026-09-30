<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER'];
$PurchaseStationID=trim($_POST['PurchaseStationID']);
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
/*if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}*/
//
if($PurchaseStationID!=""){
	$Query=' and POD.ProcessorID='.$PurchaseStationID;
}


	IF($TYPE=='POLIST'){

    $sql="SELECT *,SSC.Name as PoStatus FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID

WHERE  (SDB.TransportedDate ='' or SDB.TransportedDate is null) $Query order by PoID desc";    //ProductionStartedDate!='' and

	}

/*ELSE IF($TYPE=='SHIPPEDPO'){
	 $sql="SELECT * FROM $TblScrmPoData as POD 
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join $TblScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
WHERE POD.InvDate between '$DateStart' and '$DateEnd'  AND Status=3 $Query order by PoID desc";

}*/
$stmt=$iconn->query($sql);
//echo $sql;

$data=' <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

      <tr>
	  <th>ID</th>
	  <th>P.Inv No	</th>	
	  <th>P.Inv Date</th>
	  <th>Po No	</th>	
	  <th>Po Date</th>
	  <th>Country</th>	
	  <th>Buyer</th>
	  <th>Status</th>
	  <th>Actions</th>
		</tr>

    </thead>
	<tbody>';
				

				
  while($row= $stmt->fetch_array()){
	
			 
$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

if($DO_SAMPLING=='01-01-1970'){
	$DO_SAMPLING='*****';
}

$PO_TIME_STAMP = strtotime($row['InvDate']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);
 
$PODate = strtotime($row['PoDate']);
$PO_Date = date("d-m-Y", $PODate);

if($PO_Date=='01-01-1970'){
	$PO_Date='*****';
} 

if($INVOICE_DATE=='01-01-1970'){
	
	$INVOICE_DATE='****';
}

// $ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$ConsigneeName= $row['ConsigneeName'];
$PoNumber=mb_strimwidth($row['PoNo'], 0, 15, "...");
$PoStatus=$row['PoStatus'];
			$CLR=$row['Color'];
			$PoID=$row['PoID'];
$SqlStockSum="SELECT SUM(AvalMcs) AS StoreStock FROM `CsStockData` WHERE PoID='$PoID'";
$stmtStockSum=$iconn->query($SqlStockSum); 
$rowStockSum= $stmtStockSum->fetch_array();
$StoreStock=$rowStockSum['StoreStock'];

$SqlPOSum="SELECT SUM(Mcs) AS POStock FROM `ScrmPoGradeData` WHERE InvId='$PoID'";
$stmtPOSum=$iconn->query($SqlPOSum); 
$rowPOSum= $stmtPOSum->fetch_array();
$POStock=$rowPOSum['POStock'];

$Percent=round((($StoreStock/$POStock)*100),2).'%';


	$data.=' 
	<td id="'.$row['ID'].'" > '.$row['PoID'].'</td>
    
	<td id="'.$row['ID'].'" > '.$row['InvNo'].'</td>
	<td id="'.$row['ID'].'" > '. $INVOICE_DATE .'</td>
	<td id="'.$row['ID'].'" > '.$PoNumber.'</td>
	<td id="'.$row['ID'].'" > '. $PO_Date.'</td>
	<td id="'.$row['ID'].'" > '.$row['name'].'</td>	
	<td id="'.$row['ID'].'" > '.$ConsigneeName.'</td>	
		';
		$data.=' 
	<td id="'.$row['ID'].'" >	<div  class=" badge badge-warning font-size-10 "
	style="width:70px;background:'.$CLR.';color:white"
					 >
							 '.$Percent.'
							 </div>	</td>
		
		';
	$data.='   <td > 
	  
	
	 <a target="_blank" href="CreateShipmentStatus.php?PoID='.$row['PoID'].'" 
	 class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
	title="Shipment Create" data-original-title="View">
	<i class="dripicons-preview font-size-18"></i> </a>
									 
						 
						 
	
	  
	 <!-- <div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                 
                  
					
                    
					
                  
					<li><a target="_blank" 
					href="AvalPoRepList.php?PoID='.$row['PoID'].'"><i class="fa fa-eye"></i> View </a>
					</li>
                   
                <li>			   	
<button id="'.$row['PoID'].'" style="width:160px;"
	onClick="reply_click(this.id)" class="btn btn-danger btn-sm btn-flat"> Delete</button>
	
</li> 




                  </ul>
                </div> -->
				
				</td></tr>';
	  
}
	 echo $data .='</tbody></table>';

 
?>
