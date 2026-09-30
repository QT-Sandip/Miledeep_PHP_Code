<?php

include("../RestAPI.php");

$PoID = trim($_POST['PoID']);
$Status = trim($_POST['Status']);

$api = GetClearingAgentBreakupListAPI;

$bodyContent = array("PoID" => $PoID);

$resGetClearingAgentBreakupListAPI = CallAPI($api, "POST", "", $bodyContent);

$resList = $resGetClearingAgentBreakupListAPI->Data->getClearingAgentBreakupList;

// var_dump($resList);


?>
<?php
$htmlResponse = ' <table id="key-datatable" data-paging="false" class="table  wrap w-100">
	<thead class="thead-light">
		<tr>
			<th><b>Category</b></th>
			<th>Bill Number</th>
			<th>Bill Date</th>
			<th>Bill Amount</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>';
$i = 1;
foreach ($resList as $item) {
	$htmlResponse .= '<tr>
	<td>' . $item->TypeofCharges . '</td>
	<td>' . $item->BillNumber . '</td>
	<td>' . $item->BillDate . '</td>
	<td>' . $item->BillAmount . '</td>
	<td>';

	if ($Status != 1) {
		$htmlResponse .= '
			<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px"
				data-id="' . $item->BreakupID . '" title="Delete">
				<i class="mdi mdi-trash-can font-size-16"></i>
			</button>';
	}

$htmlResponse .= '</td></tr>';
}
$htmlResponse .= '</tbody>

</table>';
$response = array('html' => $htmlResponse, 'json' => $resList, "Error" => $error, "Msg" => $msg);
echo json_encode($response);
?>