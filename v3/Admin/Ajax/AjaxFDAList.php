<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

 $AccessType=$_SESSION["ACCESS_TYPE"];
$YEAR=$_POST['YEAR'];
 $EXPORTER=$_POST['EXPORTER']; 
$TYPE=$_POST['TYPE'];
$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';
//
	
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}

if($YEAR!=''){
	
IF($TYPE=='POLIST'){
		
		

 /* $sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID FROM $TblScrmPIData as POD 
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join $TblScrmCountriesList as  SCL on SCL.id=POD.DestinationCountry
left join $ScrmCustomInvoice as SCI on SCI.PiID=POD.PoID
WHERE POD.InvDate between '$DateStart' and '$DateEnd'  AND Status>=0  $Query order by PoID desc";  */


//$sql="SELECT * from  $ScrmCustomInvoice WHERE CInvDate between '$DateStart' and '$DateEnd' and SBDate!=''   $Query order by PiID desc";  
 $sql="SELECT * from  ScrmCustomInvoices  SCI 
left join $TblScrmPIData  POD on POD.PoID=SCI.PiID
left join $ScrmEirAgentNDiscountDetails  SAD on SAD.PoRefNumber=SCI.PiID
left join $TblScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID

WHERE CInvDate between '$DateStart' and '$DateEnd' and  CHAR_LENGTH(BillRefNo)> 15  $Query order by PiID desc";  

	}


$stmt=$iconn->query($sql);

//echo $sql; exit;
$data=' <table id="key-datatable" 
										class="table dt-responsive wrap w-100">
				
<thead>

      <tr>
	  <th>Report ID</th>
	  <th>Buyer</th>
	  <th>Container No</th>
	  <th>Bill RefNo</th>
	  <th>C.Inv No</th>
	  <th>C.Inv Date</th>
	  <th>FDA Entry Number</th>		
	  <th>FDA Entry Date</th>		
	  <th>FDA/EU Release/CFIA Date</th>		
	  
	  </tr>

    </thead>
	<tbody>';
				

				
  while($row= $stmt->fetch_array()){
	
			 


$ContainerNo = $row['ContainerNo'];

$PoID=$row['PiID'];


 /*$SqlTrack="SELECT FdaEntryDate,FdaNEuReleaseDate,FdaEntryNumber
 FROM `$ScrmEIRBankNFieghtDetaails`EBF 
left join $ScrmLinerMaster SLM on SLM.LinerID=EBF.ShippingCompany
left join $ScrmEirFdaNOtherDetails SEF on SEF.PoRefNumber=EBF.PoRefNumber
 where EBF.PoRefNumber='$PoID'"; */
  $SqlTrack="SELECT FdaEntryDate,FdaNEuReleaseDate,FdaEntryNumber
 FROM `$ScrmEirFdaNOtherDetails` where PoRefNumber='$PoID'"; 
$StmtTrack=$iconn->query($SqlTrack);
$RowTrack=$StmtTrack->fetch_array();
//$ContainerTrackingUrl=$RowTrack['ContainerTrackingUrl'];



		
	
	$CInvDate=getFormatedDate($row['CInvDate'])	;
	$FdaDate=getFormatedDate($RowTrack['FdaEntryDate'])	;
	$FdaReleaseDate=getFormatedDate($RowTrack['FdaNEuReleaseDate'])	;
	

	$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");

	$data.='<tr> 
	<td id="'.$row['PiID'].'" > '. $row['PiID'].'</td>
	<td id="'.$row['PiID'].'" > '. $ConsigneeName.'</td>
	<td id="'.$row['PiID'].'" > '. $row['ContainerNo'].'</td>
	<td id="'.$row['PiID'].'" > '. $row['BillRefNo'].'</td>
	    <td id="'.$row['PiID'].'" > '. $row['CInvNo'] .'</td>
		<td id="'.$row['PiID'].'" > '. $CInvDate.'</td>
		<td id="'.$row['PiID'].'" > '. $RowTrack['FdaEntryNumber'].'</td>
		<td id="'.$row['PiID'].'" > '.$FdaDate.'</td>
		<td id="'.$row['PiID'].'" > '.$FdaReleaseDate.'</td>
       <!-- <td> <input  type="date" class="form-control"  name="ETA" id="ETA'.$row['PiID'].'"
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
				
					 </td>--></tr>
					';
					
	
		
	//$data .='	';
	  
}
	 echo $data .='</tbody></table>';

					
					
}


 
?>
