<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

 $AccessType=$_SESSION["ACCESS_TYPE"];
$YEAR=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER']; 
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
	
		
		

   $sql="SELECT * from  $ScrmCustomInvoices  SCI 
left join $ScrmShipmentDetails  SD on SD.PiID=SCI.PiID
left join $TblScrmPIData  POD on POD.PoID=SCI.PiID
left join $ScrmEirAgentNDiscountDetails  SAD on SAD.PoRefNumber=SCI.PiID
left join $ScrmEirFdaNOtherDetails  SFD on SFD.PoRefNumber=SCI.PiID
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID

WHERE CInvDate between '$DateStart' and '$DateEnd' and BillStatus!=1 and  CHAR_LENGTH(BillRefNo)> 15  $Query order by SCI.PiID desc";  



$stmt=$iconn->query($sql);


$data=' <table id="key-datatable" 
										class="table dt-responsive wrap w-100">
				
<thead>

      <tr>
	  <th>ID</th>
	  <th>C.Inv No</th>
	  <th>C.Inv Date</th>
	  <th>Buyer</th>
	  <th>Container No</th>
	  <th>Bill Ref No</th>
	  <th>ETA</th>		
	  <th>Remarks</th>		
	  <th>Actions</th>		
	  
	  </tr>

    </thead>
	<tbody>';
				

				
  while($row= $stmt->fetch_array()){
	
			 


$ContainerNo = $row['ContainerNo'];
$CInvDate = strtotime($row['CInvDate']);
$CInvDate = date("d-m-Y", $CInvDate);
$PoID=$row['PiID'];


$SqlTrack="SELECT LinerName,ContainerTrackingUrl FROM `$ScrmLogisticsDetails`EBF 
left join $ScrmLinerMaster SLM on SLM.LinerID=EBF.SteamshipCompany where PiID='$PoID'";
$StmtTrack=$iconn->query($SqlTrack);
$RowTrack=$StmtTrack->fetch_array();
$ContainerTrackingUrl=$RowTrack['ContainerTrackingUrl'];

if($CInvDate=='01-01-1970'){
	
	$CInvDate='****';
}


		
		

		
  $SqlRepCount="SELECT FbdEta,FbdEtaRemarks FROM `$ScrmLogisticsDetails` WHERE PiID='$PoID'"; 
		$StmtRepCount=$iconn->query($SqlRepCount);
		$RowTenPlus=$StmtRepCount->fetch_array();
		 $FbdEta=$RowTenPlus['FbdEta'];
		 $FbdEtaRemarks=$RowTenPlus['FbdEtaRemarks'];
		 
$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");

		
	$data.='<tr> 
	<td id="'.$row['PiID'].'" > '. $row['PiID'].'</td>
	<td id="'.$row['PiID'].'" > '. $row['CInvNo'] .'</td>
	<td id="'.$row['PiID'].'" > '. $CInvDate.'</td>
	<td id="'.$row['PiID'].'" > '. $ConsigneeName.'</td>
	<td id="'.$row['PiID'].'" > '. $row['ContainerNo'].'</td>
	<td id="'.$row['PiID'].'" > '. $row['BillRefNo'].'</td>
        <td> <input  type="date" class="form-control"  name="ETA" id="ETA'.$row['PiID'].'"
				  placeholder="" value="'.$FbdEta.'"></td>	
          <td>	   <input  type="text" class="form-control"  name="FbdEtaRemarks" id="FbdEtaRemarks'.$row['PiID'].'"
				  placeholder="" value="'.$FbdEtaRemarks.'"> </td>		
				 
				
				<td>
           		 
					 
		   <button class="btn btn-link text-primary BtnEditETA" data-id="'.$row['PiID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-pencil font-size-18"></i></button> 						
			
	
		
		<a target="_blank" href="DocAddEIRReport.php?id='.$row['PiID'].'" title="Add Export Information"><i class="mdi mdi-plus font-size-18"></i></a>

						 
                   <input type="hidden"  id="ContainerNo" value="'.$ContainerNo.'">

				<button id="BtnTrack" value="'.$RowTrack['ContainerTrackingUrl'].'" type="button" class="btn btn-success btn-sm btn-flat">Track</button>
				
					 </td></tr>
					';
					
	
		
	//$data .='	';
	  
}
	 echo $data .='</tbody></table>';

					
					
}


 
?>
