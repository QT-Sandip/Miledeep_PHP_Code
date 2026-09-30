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


 

$SqlBreakup="SELECT * FROM `PpcDailyBatchBreakUpDeatils` as PDB 
left join RmLotDetatils as  RLD on RLD.RmLotID=PDB.LotID
WHERE DailyBatchID='$DailyBatchID' ORDER BY PurchaseCount ASC";
$StmtBreakup=$iconn->query($SqlBreakup);
$Count=mysqli_num_rows($StmtBreakup);

if($Count!=0){
	


$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">										
		
<thead class="thead-light">
          
				 
                   <th>S.No</th>
	  <th>Batch</th>
	  <th>Date</th>
	  <th>Lot No</th>
	  <th>Count</th>
	  <th>Quantity(Kgs)</th>
	  <th>Actions</th>
	  
      </thead>
    <tbody>';

	$PURCHASE_AMOUNT=0;
	$S_NO=1;

	while($RowBreakup=$StmtBreakup->fetch_array()){

		

				$content .= '<tr>

                <td  id="" >'.$S_NO.'</td>
	<td id="" > '.$RowBatch['SubContractorName'].' </td>
	<td id="" > '.$Date.' </td>
	<td id="" > '.$RowBreakup['LotNumber'].' </td>
	<td id="" > '.$RowBreakup['PurchaseCount'].' </td>
	<td id="" > '.$RowBreakup['HLQty'].' </td>
	<td id="" > <button class="btn btn-link text-danger Delete" 
		data-id="'.$RowBreakup['BreakupID'].'" data-value="'.$RowBreakup['DailyBatchID'].'"
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button> </td>
	';
				 
				$content.=  ' </tr>';
$S_NO++;

	}
	$content.='</tbody></table>'	;

echo $content;
}
?>
