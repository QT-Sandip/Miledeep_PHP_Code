<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
?>

<link href="https://cdn.datatables.net/fixedcolumns/3.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet" />

<?PHP
$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

  $AccessType=$_SESSION["ACCESS_TYPE"]; 
$YEAR=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER']; 
 $Buyer=$_POST['Buyer']; 
 $Country=$_POST['Country']; 
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
 $_SESSION["SelectedYear"]=$_POST['YEAR'];

//
	
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}
 
if($YEAR!=''){
	
 $sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID
 FROM $TblScrmPIData as POD 
left join $ScrmProductionData as SPD on SPD.PIID=POD.PoID
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
WHERE POD.InvDate between '$DateStart' and '$DateEnd'  AND Status>=0  and Status!=3 
$Query   order by PoID desc";  

 //echo $sql;
$stmt=$iconn->query($sql);


$data=' <table id="key-datatable"  class="table table-bordered" 
	style="background:#fff;margin-top:6px !important;margin-bottom:-6px !important; height:100px"
										>
				
<thead>

         <tr>
	   <th>ID</th>	
	   <th>P.Inv No</th>
	   <th>P.Inv Date</th>
	   <th>Po No</th>
	   <th>Po Date</th>
	   <th>Buyer</th>
	   <th>LastUpdatedDate</th>
	   <th>No.of.Mcs</th>		
	   <th>Destination</th>		
	   <th>Buyer Insptn Status</th>		
	   <th>Loading Indent Status</th>		
	   <th>Booked Containers	</th>		
	   <th>Vehicle Status	</th>		
	   <th>Remarks</th>		
	   
       <th>Actions</th>
		</tr>

    </thead>
	<tbody>';
				

				
  while($row= $stmt->fetch_array()){
	
			 


$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));
$PoDate = date('d-m-Y',strtotime($row['PoDate']));
$InvoiceDate = date('d-m-Y',strtotime($row['InvDate']));

if($PoDate=='01-01-1970'){
	$PoDate='*****';
} 

if($InvoiceDate=='01-01-1970'){
	
	$InvoiceDate='****';
}

if($LastUpdatedDate=='01-01-1970'){
	$LastUpdatedDate='*****';
}

if($row['AvalMcs']==''){
	$AvalMcs='0';
} 
else{
	$AvalMcs=$row['AvalMcs'];
}



$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");

		
		

$PoID=$row['PoID'];
		
		
	$data.='<tr> <td> '. $row['PoID'].'</td>
			

	    <td> '.  $row['InvNo'].'</td>
		<td> '. $InvoiceDate .'</td>
		<td> '. $PoNo.'</td>       
		       
		<td> '. $PoDate.'</td>       
		<td> '.$ConsigneeName  .'</td>
		
		
		<td> '.$LastUpdatedDate.'</td>
		
		<td> 
		  <input  type="text" class="form-control"  name="AvalMcs" id="AvalMcs'.$row["PoID"].'"
				   value="'.$AvalMcs.'"</td>
				   
				  <td> 
		  <input  type="text" class="form-control"  name="Destination" id="Destination'.$row["PoID"].'"
				   value="'.$row['Destination'].'"</td>
				   <td> 
		  <input  type="text" class="form-control"  name="BuyerInseptionStatus" id="BuyerInseptionStatus'.$row["PoID"].'"
				   value="'.$row['BuyerInseptionStatus'].'"</td>

				   <td> 
		  <input  type="text" class="form-control"  name="LoadingIndentStatus" id="LoadingIndentStatus'.$row["PoID"].'"
				   value="'.$row['LoadingIndentStatus'].'"</td>
				   				   <td> 
		  <input  type="text" class="form-control"  name="BookedContainers" id="BookedContainers'.$row["PoID"].'"
				   value="'.$row['BookedContainers'].'"</td>
				   <td> 
		  <input  type="text" class="form-control"  name="VehicleStatus" id="VehicleStatus'.$row["PoID"].'"
				   value="'.$row['VehicleStatus'].'"</td>
				   <td> 
		  <input  type="text" class="form-control"  name="Remarks" id="Remarks'.$row["PoID"].'"
				   value="'.$row['Remarks'].'"</td>
						
		</td>
		
	
			<td> ';
			if($AvalMcs==0){
			
		$data .='	<button  id="BtnAddMcs"  value="'.$row["PoID"].'" align="Center" class="btn btn-success btn-sm btn-flat">
						 Add </button>
						 
						
						 
						 ';
						  
						  

			}
			else{
			
		$data .='	<button  id="BtnAddMcs"  value="'.$row["PoID"].'" align="Center" class="btn btn-success btn-sm btn-flat">
						 Edit </button>
						
						
						 ';
					
			}
        $data .='  </td>
			             
						
		</tr>';
	  
}
	 echo $data .='</tbody></table>';

					
					



}


 
?>
<script src="https://cdn.datatables.net/fixedcolumns/3.3.0/js/dataTables.fixedColumns.min.js"></script>
<script>
$(document).ready( function () {
    // $('#example1').DataTable();
    var table = $('#key-datatable').DataTable( {
        scrollY:"400px",
         scrollX:true,
          //sScrollX: "100%",
  sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true
        /*"columnDefs": [
            { "width": "20%", "targets": 0 }
          ]*/
    } );
} );
</script>
