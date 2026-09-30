<?php

include ("../RestAPI.php");


$FromDate = trim($_POST['FromDate']);
$ToDate = trim($_POST['ToDate']);
$StatusType = trim($_POST['StatusType']);
$Country = trim($_POST['Country']);

$api = GetClearingAgentDetailsAPI;

$bodyContent = array(
	"FromDate"=>$FromDate,
	"ToDate"=>$ToDate,
	"Year"=>"",
	"StatusType"=>$StatusType,
   "Country"=>$Country);

	$resGetClearingAgentDetailsAPI = CallAPI($api, "POST", "", $bodyContent);
	
	// var_dump($bodyContent);
	// var_dump($resGetClearingAgentDetailsAPI);
 
	$resList = $resGetClearingAgentDetailsAPI->Data->ClearingAgentsList; 
?>



<table  id="key-datatable" data-paging="false" class="table  wrap w-100" >
   <thead class="thead-light">
      <tr>
         <th  style="min-width: 60px;">S.No</th>
         <th  style="min-width: 120px;">Inv No</th>
         <th  style="min-width: 120px;">Consignee</th>
         <th  style="min-width: 120px;">BLNo</th>
         <th  style="min-width: 220px;">Port Of Discharge</th>
         <th  style="min-width: 180px;">Notify Party Name</th>
         <th  style="min-width: 120px;">Actions</th>
      </tr>
   </thead>
   <tbody>
      <?php $i = 1; foreach($resList as $item) { ?>
		<tr>
            <td><?php echo $i ?></td>
            <td><?php echo $item->InvoiceNo ?></td>
            <td><?php echo $item->Consignee ?></td>
            <td><?php echo $item->BLNo ?></td>
            <td><?php echo $item->PortOfDischarge ?></td>
            <td><?php echo $item->NotifyPartyName ?></td>
            <td>
				<?php if($StatusType == 0) {  ?>
				<a href="ClearingAgentForm?id=<?php echo $item->PoID ?>" class="mr-3 text-success aViewDocRpt" style="margin-top: 4px;" data-id="<?php echo $item->PoID ?>" title="View">
					<i class="mdi mdi-plus" style="font-size: 19px;"></i>
				</a>
				<?php } ?>
				
            <?php if($StatusType == 1) {  ?>
               <a href="ClearingAgentForm?id=<?php echo $item->PoID ?>" class="mr-3 text-success aViewDocRpt" style="margin-top: 4px;" data-id="<?php echo $item->PoID ?>" title="View">
					<i class="dripicons-preview font-size-18"></i>
				</a>
				 <button class="btn btn-link mr-2 text-danger btnDeleteClearingAgentDetails" data-PoId="<?php echo $item->PoID ?>" style="padding: 0px;margin-top: -10px;"  title="Delete">
					<i class="mdi mdi-trash-can font-size-16"></i>
				</button>
				<?php } ?>
				
			</td>
		</tr>
      <?php $i++; } ?>

   </tbody>
</table>



