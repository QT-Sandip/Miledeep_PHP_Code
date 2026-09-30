<?php 


include("../RestAPI.php");

$Exporter = trim($_POST['Exporter']);
$FromDate = trim($_POST['FromDate']);
$ToDate = trim($_POST['ToDate']);
//$Cancelled = trim($_POST['Cancelled']);
$Status = trim($_POST['Status']);
$FDAExmStatus = trim($_POST['FDAExmStatus']);

$api = GetFDAExaminationReportDataAPI;

$bodyContent = array(
	"Exporter"=>$Exporter,
	"FromDate"=>$FromDate,
	"ToDate"=>$ToDate,
	"Status"=>$Status,
	"FDAEStatus"=>$FDAExmStatus
);

// var_dump($bodyContent);
// exit;
//  print_r($bodyContent);
// exit;

$resGetFDAExaminationReportDataAPI = CallAPI($api, "POST", "", $bodyContent);

$resList = $resGetFDAExaminationReportDataAPI->Data->getFDAExminationReportData;

// echo print_r($resGetFDAExaminationReportDataAPI);
// exit;
?>

<table id="key-datatable" data-paging="false" class="table  wrap w-100">
    <thead class="thead-light">
        <tr>
            <th style="min-width: 60px" class="sticky etaStatusCol1">S No.</th>
            <th style="min-width: 150px" class="sticky etaStatusCol1">Invoice No</th>
            <th style="min-width: 250px">Buyer / PO.NO</th>
            <th style="min-width: 250px">Variety</th>
            <th style="min-width: 120px">Destination</th>
            <th style="min-width: 120px">Container No</th>
            <th style="min-width: 120px">Arrival Date</th>
            <th style="min-width: 150px">Entry No</th>
            <th style="min-width: 120px">Examination</th>
            <th style="min-width: 120px">Notice Date</th>
            <th style="min-width: 200px">Sample Collection Date</th>
            <th style="min-width: 200px">Sample Received Date</th>
            <th style="min-width: 150px">FDA/EU Release/CFIA Date</th>
            <th style="min-width: 320px">Status</th>
            <th style="min-width: 100px">FDA Exam</th>
			<th style="min-width: 250px">Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach($resList as $item) { ?>
        <tr>
            <td class="sticky etaStatusCol1"><?php echo $item->sno ?></td>
            <td class="sticky etaStatusCol1"><?php echo $item->InvNo ?></td>
            <td><?php echo $item->BuyerPoNo ?></td>
            <td><?php echo $item->Variety ?></td>
            <td><?php echo $item->DestinationCountry ?></td>
            <td><?php echo $item->Containerno ?></td>
            <td><?php echo $item->ArrivalDate ?></td>
            <td><?php echo $item->EntryNo ?></td>
            <td><?php echo $item->Examination ?></td>
            <td><?php echo $item->NoticeDate ?></td>
            <td><?php echo $item->SampleCollectionDate ?></td>
            <td><?php echo $item->SampleReceivedDate ?></td>
            <td><?php echo $item->FDAEUReleaseCFIADate ?></td>
            <td><?php echo $item->Status ?></td>
            <td><?php echo $item->FDAExam ?></td>
			<!-- <td><?php echo $item->Remarks ?></td> -->
			 <td>
    <div class="remarks-text">
        <?php echo htmlspecialchars($item->Remarks); ?>
    </div>
</td>
        </tr>
        <?php } ?>
    </tbody>

</table>



<?php
/* 
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$Header='ORDER`S LIST REPORT';



 $Year=$_POST['Year']; 
 $Exporter=$_POST['Exporter']; 
 $Consignee=$_POST['Consignee']; 
 $Country=$_POST['Country']; 
 
 
 $DateFilter=getFinancialYear($Year);
 
$Query='';
if($Exporter!=''){
$Query.="and SPD.ExporterID in ($Exporter)";
}

if($Consignee!=''){
$Query.="and SPD.ConsigneeID in ($Consignee)";
}
if($Country!=''){
$Query.="and SPD.DestinationCountry in ($Country)";
}



$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';
$date_end=$to_date.' 23:59:59';  
		
// $sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
// 					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
// 					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
// 					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
// 					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
// 					WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
// 					$Query order by PoID desc";

// 			$stmt=$iconn->query($sql);					
// 			$RowData=$stmt-> fetch_array();
//              $PoID=$RowData['PoID'];
// 			 $AryPoID=explode(',',$PoID);
// 			 $OrderCount= Count($AryPoID);

		
	
	
// 		$SqlGroup="SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
// 		FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
// 		$StmtGroup=$iconn->query($SqlGroup);
		
		
// 		while($RowData=$StmtGroup-> fetch_array()){
		
// 		$AryData= getTableDate($RowData['VarietyID'],$PoID);	
		
// 		if($AryData['RowCount']>0){
// 			//echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName']  .'</h3>';
			
// 			echo $AryData['TableData'];
// 		}
// 		}
	

	
	//function getTableDate($VarietyID,$PoID){
	global $iconn,$Query;
	  $sql="SELECT BuyerName,SCI.PiID,PoNo,PODate,PreparedBy,SCL.name as DestinationCountry,ISF.ContainerNo as ctnnmbr
FROM ScrmCustomInvoices as  SCI 
left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
left join ScrmBuyerDetails as SBD on SPD.BuyerID=SBD.BuyerID
left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID

WHERE  SCI.CInvDate $DateFilter $Query ";


$stmt=$iconn->query($sql);
	



$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-150" >										
		
<thead class="thead-light">
				   <th  align="center"   >S No.</th>              
				   <th  align="center"  >Invoice No</th>
				   <th  align="center"  >Buyer / PO.NO</th>
				   <th  align="center"  >Variety</th>					      	   
				   <th  align="center"  >Destination</th>	
				   <th  align="center"  >Container No</th>
                   <th  align="center"  >Arrival Date</th>
				   <th  align="center"  >Entry No</th>			
				   <th  align="center"  >Examination</th>						   
				   <th  align="center" >Notice Date</th>				   
				   <th  align="center"  >NoticeDate POPUP</th>		
				   <th  align="center"  >Sample Collection Date</th>	 		
				   <th  align="center"  >Sample Received Date</th>			
				   <th  align="center"  >Notes</th>		
				   <th  align="center"  >Production Dates</th>			
				   <th  align="center"  >Status</th>	
		
    </thead>
	<tbody>';
				
$Sno=1;
				
  while($row= $stmt->fetch_array()){
	  $PoID=$row['PiID'];
	  $BuyerName=$row['BuyerName'];
	  $PoNo=$row['PoNo'];
	  $DestinationCountry=$row['DestinationCountry'];
	  $ContainerNo = $row['ctnnmbr'];

	
	$CInvNo=getInvoiceNumber($iconn,$ExporterID,$PoID);
		 


$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));



//$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");
 $SqlVarietyMcs="SELECT Description,Variety
			FROM `ScrmPoGradeData`  WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand 
			 Order by ScrmPoGradeData.ID asc"; 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
			unset($AryDescription);
			unset($AryVariety);
			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
				
			$AryDescription[]=$VarietyMcsRow['Description'];
			$AryVariety[]=$VarietyMcsRow['Variety'];
			$Variety=implode('<br>',$AryVariety);
			$Description=implode('<br>',$AryDescription);

			}
			if($Description==''){
			$Description=$Variety;
             }
			 else{
				$Description; 
			 }	
	$data.='<tr> 
	<td> '.$Sno.'</td>		
	<td> '. $CInvNo.'</td> 
	<td> '.$BuyerName.' / <br>'.$PoNo.'</td> 
	<td>'.$Description.'</td>
	<td> '. $DestinationCountry.' </td>
	<td> '. $ContainerNo.'</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	';
				   
	
$Sno++;	 
}
  $data .='</tbody></table>';
$AryData['TableData']=$data;
//$AryData['RowCount']=$Rcount;
	

	 echo $data;
	
	 
	//}
*/


?>