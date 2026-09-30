<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");

$YEAR=$_POST['YEAR'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';

$EXPORTER=$_POST['EXPORTER'];
$TYPE=$_POST['TYPE'];
if($YEAR==''){
	$Query='';
}
else{
	
}

if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}

	IF($TYPE=='POLIST'){

    
	
$sql="SELECT *,SSC.Name as PoStatus ,SPC.Name as Port FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on POD.DestinationCountry=SCL.id 
left join ScrmPortCities as SPC on POD.PortOfDischarge	=SPC.PortId 
left join ScrmShipmentStatusCodes as SSC on POD.Status=SSC.Code
left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID

WHERE ProductionStartedDate!=''and (TransportedDate='' or TransportedDate is null)   $Query order by PoID desc";   
	}

$stmt=$iconn->query($sql);



$data=' <table  id="key-datatable" class="table table-bordered"  data-paging="false"
	style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px"

				
<thead>

      <tr>
	  <th>ID</th>
	  <th>P.Inv No	</th>	
	  <th>P.Inv Date</th>
	  <th>Po No	</th>	
	  <th>Po Date</th>
	  <th>Country</th>	
	  <th>Buyer</th>
	  <th>Port</th>
	  <th>Ship by date</th>
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

$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNumber=mb_strimwidth($row['PoNo'], 0, 15, "...");
$PoStatus=$row['PoStatus'];
			$CLR=$row['Color'];
 
	$data.=' 
	<td id="'.$row['ID'].'" > '.$row['PoID'].'</td>
    
	<td id="'.$row['ID'].'" > '.$row['InvNo'].'</td>
	<td id="'.$row['ID'].'" > '. $INVOICE_DATE .'</td>
	<td id="'.$row['ID'].'" > '.$PoNumber.'</td>
	<td id="'.$row['ID'].'" > '. $PO_Date.'</td>
	<td id="'.$row['ID'].'" > '.$row['name'].'</td>	
	<td id="'.$row['ID'].'" > '.$ConsigneeName.'</td>	
	<td id="'.$row['ID'].'" > '.$row['Port'].'</td>	
	<td id="'.$row['ID'].'" > '.getFormatedDate($row['ShipByDate']).'</td>	
		';
		$data.=' 
	<td id="'.$row['ID'].'" >	<div  class=" badge badge-warning font-size-10 "
	style="width:70px;background:'.$CLR.';color:white"
					 >
							 '.$PoStatus.'
							 </div>	</td>
		
		';
	$data.='   <td > 
	  
	 <!-- <a href="AvalPoRepList.php?PoID='.$row['PoID'].'" class="mr-3 text-success" data-toggle="tooltip" data-placement="top"
			title="" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
	 </a>-->
	 <a href="DocAddReports.php?PoID='.$row['PoID'].'" 
	 class="mr-3 text-success"
	 data-toggle="tooltip" data-placement="top"
						 title="Add Invoices" data-original-title="View"><i class="dripicons-preview font-size-18"></i>
						 </a>
						 
						 <a href="CreateShipmentStatus.php?PoID='.$row['PoID'].'" 
	 class="mr-3 text-primary"
	 data-toggle="tooltip" data-placement="top"
						 title="Update Status" data-original-title="View"><i class="mdi mdi-pencil font-size-18"></i>
						 </a>
						 
	<!--<button class="btn btn-link text-primary BtnViewPoStatus" data-id="'.$row['PoID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="Shipped" value=""
							data-original-title="Delete"><i class="mdi mdi-ferry font-size-18"></i></button> 	-->					
	
						 
						 
						 <button class="btn btn-link text-danger Delete" data-id="'.$row['PoID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
	
	
	  
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



		                   <li> <button id="BtnViewPoStatus" style="width:160px;"
						   value= "'.$row['PoID'].'" align="Center" 
						   class="btn btn-success btn-sm btn-flat">Shipped</button></li>

                  </ul>
                </div> -->
				
				</td></tr>';
	  
}
	 echo $data .='</tbody></table>';


//}


 
?>
