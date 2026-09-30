<style>
table tr td{
	
	    font-size: 15px;
    text-align: left;
}

table tr th{
	
	    font-size: 12px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}


</style>

<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

$AccessType=$_SESSION["ACCESS_TYPE"];
$YEAR=$_POST['YEAR'];
$EXPORTER=$_GET['EXPORTER'];
$TYPE=$_GET['TYPE'];


 $_SESSION["SelectedYear"]=$_POST['YEAR'];

$CurrentYear=date('Y');

 $DateFilter=getFinancialYear($CurrentYear);

	$Query='';
	
if($EXPORTER!=" "){
	$Query='and POD.ExporterID in ('.$EXPORTER.')';
}



/* $sql="SELECT BuyerName,POD.ExporterID,SCI.PiID,ISF.ContainerNo as ctnnmbr,PoNo,COALESCE(DATEDIFF(EstimatedETA,BLDate),0) as ETADays ,ProcessorID from  ScrmCustomInvoices  SCI 

left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.PortofDischarge
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmBuyerDetails as SBD on POD.ConsigneeID=SBD.BuyerID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
LEFT JOIN ScrmShipmentDetailsBreif as SDB on SDB.PiID=SCI.PiID
WHERE SDB.TransportedDate!='' 
and (SDB.ClosedDate ='' or SDB.ClosedDate is null)  $Query order by SCI.PiID desc"; */

 $sql="SELECT ConsigneeName,POD.ExporterID,SCI.PiID,ISF.ContainerNo as ctnnmbr,PoNo,COALESCE(DATEDIFF(EstimatedETA,BLDate),0) as ETADays ,ProcessorID ,BLNo,
 SPC.Name as FinalDestination,SCL.name as DestinationCountry,ShippingOnboardDt,ETA_POD,
 DeliveryTerms,LinerName
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmCountriesList  SCL on SCL.id=POD.DestinationCountry
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
WHERE SCI.CInvDate $DateFilter   $Query order by SCI.PiID desc";  

$stmt=$iconn->query($sql);

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



		//( date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )	  	
			  
			  $S_NO=0;
			  $width=11;
			 // $width_s=10;
			  //$width_w=$width+5;
              
			    $content='	<table  id="key-datatable" data-paging="false" 
class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
	
	
	
	
	
      <tr>
	  <th>S.No</th>
	  <th>Inv No</th>
	  <th>Buyer</th>
	  <th>PO NO</th>
	  <th>Processor</th>
	  <th>Product</th>
	  <th>B/L.No</th>
	  <th>Container No</th>
	  <th>Destination</th>
	  <th>On board Date</th>
	  <th>ETA Date</th>	
	  <th>Gate OutFull</th>	
	  <th>Empty CY Date</th>	
	  <th>Transit Days</th>	
	  <th>Transit Days as per Liner</th>	
	   <th>Liners</th>
	   <th>CY/SD</th>
	   <th>Entry Number</th>
	  
	  </tr>

    </thead>
	<tbody>';
				
$SNo=1;
				
  while($row= $stmt->fetch_array()){
$PoID=$row['PiID'];
$ExporterID=$row['ExporterID'];
$ProcessorID=$row['ProcessorID'];
$PoNo=$row['PoNo'];
$CInvNo=getInvoiceNumber($iconn,$ExporterID,$PoID);
$BLNo=$row['BLNo'];
$ConsigneeName=$row['ConsigneeName'];
$ProcessorData=getExporterDetails($ProcessorID );
$ProcesorApprovalNo=$ProcessorData['ExpApprovalNo'];
$FinalDestination=$row['FinalDestination'].',<br>'.$row['DestinationCountry'];
$ContainerNo = $row['ctnnmbr'];
$DeliveryTerms = $row['DeliveryTerms'];
$LinerName = $row['LinerName'];
$ShippingOnboardDt = getFormatedDatelines($row['ShippingOnboardDt']);
$ETA_POD = getFormatedDatelines($row['ETA_POD']);


$SqlProduct="SELECT ProductDescription  FROM `ScrmPoGradeData` as SPD
left join ScrmPIVarietyList as SPV on SPV.VarietyID=SPD.VarietyID
  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand  Order by SPD.ID asc"; 
			$StmtProduct=$iconn->query($SqlProduct);
			unset($AryProductDescription);
			
			while($RowProduct=$StmtProduct->fetch_assoc()){
				
			$AryProductDescription[]=$RowProduct['ProductDescription'];
			
			$ProductDescription=implode(',<br>',$AryProductDescription);

			}

/*
$SqlTrack="SELECT LinerName,ContainerTrackingUrl FROM `$ScrmLogisticsDetails`EBF 
left join $ScrmLinerMaster SLM on SLM.LinerID=EBF.SteamshipCompany where PiID='$PoID'";

$StmtTrack=$iconn->query($SqlTrack);
$RowTrack=$StmtTrack->fetch_array();*/
$ContainerTrackingUrl=$row['ContainerTrackingUrl'];
$LinerName=$row['LinerName'];



  $EstimatedETA= strtotime($row['EstimatedETA']);
   $EstimatedETA = date("d-m-Y", $EstimatedETA);
   $EstimatedETA = $row['EstimatedETA'];
  $ETARemarks=$row['ETARemarks'];
  $BLNo=$row['BLNo'];
  $ETADays=$row['ETADays'];
 
 
		 
//$ConsigneeName=mb_strimwidth($row['BuyerName'], 0, 20, "...");
if($EstimatedETA=='01-01-1970'){
	
	$EstimatedETA='--';
}
	
	$content.='<tr> 
	<td id="'.$row['PiID'].'" > '. $SNo.'</td>
	<td id="'.$row['PiID'].'" > '. $CInvNo.'</td>
	<td id="'.$row['PiID'].'" > '. $ConsigneeName.'</td>
	<td id="'.$row['PiID'].'" > '. $PoNo.'</td>
	<td id="'.$row['PiID'].'" > '. $ProcesorApprovalNo.'</td>
	<td id="'.$row['PiID'].'" > '. $ProductDescription.'</td>
	<td id="'.$row['PiID'].'" > '. $BLNo.'</td>
	<td id="'.$row['PiID'].'" > '. $ContainerNo.'</td>
	<td id="'.$row['PiID'].'" > '. $FinalDestination.'</td>
	<td id="'.$row['PiID'].'" > '. $ShippingOnboardDt.'</td>
	<td id="'.$row['PiID'].'" > '. $ETA_POD.'</td>
	<td id="'.$row['PiID'].'" > </td>
	<td id="'.$row['PiID'].'" ></td>
	<td id="'.$row['PiID'].'" ></td>
	<td id="'.$row['PiID'].'" ></td>
	<td id="'.$row['PiID'].'" > '. $LinerName.'</td>
	<td id="'.$row['PiID'].'" > '. $DeliveryTerms.'</td>
	<td id="'.$row['PiID'].'" ></td>

	
	

						 
               </tr>
					';
			
	  $SNo++;
}
	 echo $content .='</tbody></table>';


	echo $content;	


?>