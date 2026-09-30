<?php
//session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$Type = trim($_POST["Type"]);
$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);

// echo $BillsType . " -- " . $Year ." -- ". $Exporter." -- ".$Consignee ." -- ". $Country."</br>"; 

$api = GetForwardContractorList_API;

$bodyContent = array("BillsType" => $Type, "Year" => $Year, "Exporter" => $Exporter);

$resGetForwardContractorListAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetForwardContractorListAPI->Data->ForwardContractorsList;
// echo json_encode($resGetForwardContractorListAPI, JSON_PRETTY_PRINT);exit;
// var_dump($resGetForwardContractsListAPI);

?>
<table id="key-datatable" data-paging="false" class="table wrap w-100">
	<thead class="thead-light">
		<tr>
			<th>S.No</th>
			<th>Booked Date</th>
			<th>Spot Rate</th>
			<th>Forward Contract No </th>
			<th>Current Date</th>
			<th>Diff</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Month</th>
			<th>Booked Rate</th>
			<th>Booked Value</th>
			<th>Utilisation </th>
			<th>Balance US$</th>
			<?php if ($Type == "0") { ?>
				<th style="width: 80px;">Actions</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php 
			$TotalBalanceUSD = $TotalBookedValue = $TotalUtilisation = 0;
			foreach ($resList as $item) { 
				$TotalBookedValue += $item->BookedValue;
				$TotalUtilisation += $item->Utilisation;
				$TotalBalanceUSD += $item->BalanceUSD;
				?>

			<tr>
				<td><?php echo $item->Sno ?></td>
				<td><?php echo date("d-m-Y", strtotime($item->BookedDate)) ?></td>
				<td><?php echo $item->SpotRate ?></td>
				<td><?php echo $item->ForwardContractNo ?></td>
				<td><?php echo date("d-m-Y", strtotime($item->CurrentDate)) ?></td>
				<td><?php echo $item->Diff; ?></td>
				<td><?php echo date("d-m-Y", strtotime($item->StartDate)) ?></td>
				<td><?php echo date("d-m-Y", strtotime($item->EndDate)) ?></td>
				<td><?php echo !empty($item->Month) ? date("F", mktime(0, 0, 0, $item->Month, 10)) : ""; ?></td>
				<td><?php echo $item->BookedRate ?></td>
				<td class="BookedValue"><?php echo number_format($item->BookedValue, 2) ?></td>
				<td class="Utilisation"><?php echo number_format($item->Utilisation, 2) ?></td>
				<td class="BalanceUSD"><?php echo number_format($item->BalanceUSD, 2) ?></td>

				<!-- <td>
				<?php
				// if($BillsType == "0") {
				// 	echo "<span class='btn btn-link badge badge-warning font-size-10' style='background: #666666;'>Pending</span>";
				// }else{
				// 	echo "<span class='btn btn-link badge badge-warning font-size-10' style='background: #025009;'>Completed</span>";
				// }
				?>
			</td> -->
				<?php if ($Type == "0") { ?>
					<td class="text-center">

						<?php // if($Type == "0") { ?>
						<a href='<?php echo $resGetForwardContractorListAPI->Data->EditLink . $item->FCID ?>'
							class='mx-23 text-primary' title='Edit Forward Contract'>
							<i class='mdi mdi-pencil font-size-18'></i>
						</a>
						<?php // } else { ?>
						<!-- <a href='<?php // echo $resGetForwardContractorListAPI->Data->AddLink . $item->FCID ?>' class='mx-2 text-primary' title='Add Forward Contract'>
					<i class='mdi mdi-plus' style="font-size: 20px"></i>
				</a> -->
						<?php // } ?>
					</td>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td></td>
			<td>Totals</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td id="TotalBookedValue"><?php echo number_format($TotalBookedValue, 2);?></td>
			<td id="TotalUtilisation"><?php echo number_format($TotalUtilisation, 2);?></td>
			<td id="TotalBalanceUSD"><?php echo number_format($TotalBalanceUSD, 2);?></td>
			<?php if ($Type == "0") { ?>
				<td></td>
			<?php }?>
		</tr>
	</tfoot>
</table>