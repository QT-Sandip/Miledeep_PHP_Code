<?php
//session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$BillsType = trim($_POST["BillsType"]);
$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);
$Consignee = trim($_POST["Consignee"]);
$Country = trim($_POST["Country"]);
$ClientID = $_SESSION['ClientID'];

$HasNegotiationDiscount = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;

// echo $BillsType . " -- " . $Year ." -- ". $Exporter." -- ".$Consignee ." -- ". $Country."</br>"; 

$api = GetRealizationList_API;

$bodyContent = array("BillsType" => $BillsType, "Year" => $Year, "Exporter" => $Exporter, "Consignee" => $Consignee, "Country" => $Country);

$resGetRealizationListAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetRealizationListAPI->Data->RealizationList;

?>
<table id="key-datatable" data-paging="false" class="table  wrap w-100">
	<thead class="thead-light">
		<tr>
			<th>S.No</th>
			<th>Invoice No.</th>
			<th>Invoice Date</th>
			<th>Transaction Type</th>
			<th>Consignee Name</th>
			<th>Bank Ref</th>
			<th>Status</th>
			<th style="width: 70px;">Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($resList as $item) { ?>

			<tr>
				<td><?php echo $item->Sno ?></td>
				<td><?php echo $item->CInvNo ?></td>
				<td><?php echo $item->CInvDate ?></td>
				<td><?php echo $item->TransactionType ?></td>
				<td><?php echo $item->ConsigneeName ?></td>
				<td><?php echo $item->BankRefNo ?></td>

				<?php if ($BillsType == "1") { ?>

					<td>
						<span class="btn btn-link badge badge-success font-size-10 complete">
							Completed
						</span>
					</td>
					<td class="text-center">
						<a href="<?= $resGetRealizationListAPI->Data->Link . $item->PoID ?>" class="mx-2 text-primary"
							title="Edit Collections">
							<i class="mdi mdi-pencil font-size-18"></i>
						</a>
					</td>

				<?php } else { ?>

					<td>
						<span class="btn btn-link badge badge-danger font-size-10 pending">
							Pending
						</span>
					</td>

					<td class="">
						<a href="<?= $resGetRealizationListAPI->Data->Link . $item->PoID ?>" class=" text-primary"
							title="Add Collections">
							<i class="mdi mdi-plus font-size-18"></i>
						</a>

						<?php if ($HasNegotiationDiscount) {


							?>
							<a href="AddCollection?PIID=<?= $item->PoID ?>&TransactionType=<?= $item->TransactionType ?>"
								class=" text-primary" target="_blank" title="Edit Collections">
								<i class="mdi mdi-pencil font-size-18"></i>
							</a>
						<?php } ?>
					</td>

				<?php } ?>

			</tr>
		<?php } ?>
	</tbody>
</table>
