<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$DailyBatchID=$_POST['DailyBatchId'];

$TYPE=$_POST['TYPE']; 


  $SqlBatch="SELECT ContrctrName,Date,SubContractorName,BatchID FROM  PpcDailyBatchDetails as PDB  
left join PpcSubContractorMaster as PSC  on PSC.SubContactorID=PDB.BatchID
left join PpcContractMaster as PCM  on PCM.ContractorID=PSC.ContactorID
 WHERE ID='$DailyBatchID'"; 
$StmtBatch=$iconn->query($SqlBatch);	
$RowBatch=$StmtBatch->fetch_array();

$BatchID=$RowBatch['BatchID'];
$Date=getFormatedDatelines($RowBatch['Date']);
$Batch=$RowBatch['ContrctrName'].'/'.$RowBatch['SubContractorName'].'(Batch)';


//displaydata($iconn,$DailyBatchID);



 /*if($TYPE=='AddBreakupDetails'){
	$PostedDateTime=date('Y-m-d h:i:s');
$PostedUserId=$_SESSION['UserID'];
$HLQty=$_POST['HLQty'];
$LotID=$_POST['LotID'];
$BatchID=$_POST['BatchID'];
$Count=$_POST['Count'];
$VarietyID=$_POST['VarietyId'];
$Datee=$RowBatch['Date'];

$SqlWeight="SELECT HLWeight  FROM PpcHLGradingBreakup where LotID='$LotID'  and HLCount='$Count'";
$StmtWeight=$iconn->query($SqlWeight);
$RowWeight=$StmtWeight->fetch_array();
$Weight=$RowWeight['HLWeight'];


	   if($HLQty<=$Weight){
		  $SqlUpdate="INSERT INTO `PpcDailyBatchPeelingBreakUpDeatils`(`DailyBatchID`, `LotID`, `HLQty`, `PostedUserId`, `PostedDateTime`, `BatchId`, `Count`, `VarietyID`) VALUES ('$DailyBatchID','$LotID','$HLQty','$PostedUserId','$PostedDateTime','$BatchID','$Count','$VarietyID')";
		 
  	 $StmtUpdate=$iconn->query($SqlUpdate);
	  }	
		
//}
else{

}
	
	
}*/

  $SqlBreakup="SELECT * FROM `PpcDailyBatchPeelingBreakUpDeatils` as PDB 
left join RmLotDetatils as  RLD on RLD.RmLotID=PDB.LotID
left join ScrmPIVarietyList_old as  SPV on SPV.VarietyID=PDB.VarietyID
WHERE DailyBatchID='$DailyBatchID' order by Count  asc ";
$StmtBreakup=$iconn->query($SqlBreakup);
$Count=mysqli_num_rows($StmtBreakup);

if($Count!=0){
	


$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">										
		
<thead class="thead-light">
          
				 
                   <th>S.No</th>
	  <th>Batch</th>
	  <th>Date</th>
	  <th>Lot No</th>
	  <th>Variety</th>
	  <th>Count</th>
	  <th>Quantity(Kgs)</th>
	  <th>Actions</th>
	  
      </thead>
    <tbody>';

	$PURCHASE_AMOUNT=0;
	$S_NO=1;

	while($RowBreakup=$StmtBreakup->fetch_array()){

if($RowBreakup['LotID']==0){
$LotNumber='Melting';
}
else{
	$LotNumber=$RowBreakup['LotNumber'];
}

				$content .= '<tr>

                <td  id="" >'.$S_NO.'</td>
	<td id="" > '.$RowBatch['SubContractorName'].' </td>
	<td id="" > '.$Date.' </td>
	<td id="" > '.$LotNumber.' </td>
	<td id="" > '.$RowBreakup['ShortName'].' </td>
	<td id="" > '.$RowBreakup['Count'].' </td>
	<td id="" > '.$RowBreakup['HLQty'].' </td>
	<td id="" > <button class="btn btn-link text-danger Delete" 
		data-id="'.$RowBreakup['BreakupID'].'" data-value="'.$RowBreakup['DailyBatchID'].'"
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button> </td>';
				 
				$content.=  ' </tr>';
$S_NO++;

	}
	$content.='</tbody></table>'	;

echo $content;
}

//}

?>
