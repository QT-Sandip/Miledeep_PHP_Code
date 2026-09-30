<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["ExpID"]);
$Consignee = trim($_POST["ConsigneeID"]);
$Month = trim($_POST["Month"]);
$Country = trim($_POST["CountryID"]);
$Brand = trim($_POST["Brand"]);
$Processor = trim($_POST["Processor"]);
$ReqType = trim($_POST["ReqType"]);
$api = API_ORDERS_BUYERS_DETAILS;
$bodyContent = array(
	"ExpID" => $Exporter,
	"ConsigneeID" => $Consignee,
	"CountryID" => $Country,
	"Brand" => $Brand,
	"Month" => $Month,
	"Year" => $Year,
	"Processor" => $Processor,
	"ReqType" => $ReqType,
	'FromDate' => trim($_POST["FromDate"]),
	'ToDate' => trim($_POST["ToDate"]),
	'DashBoard' => trim($_POST["DashBoard"]),
);
$hidepricevalue = false;
if( isset($_POST['frompage']) && $_POST['frompage'] == 'csdashboard' ){
	$hidepricevalue = true;
}
$resBuyerDetails = CallAPI($api, "GET", "", $bodyContent);



$error = $resBuyerDetails->Error;

if (isset($error) && !empty($error)) {
	echo "Error: " . $error;
} else {

	$resList = $resBuyerDetails->Data;

?>
	<div id="buttons"></div>
	<table id="key-datatableFullscreen" data-paging="false" class="table wrap w-150 table-bordered">
		<thead class="thead-light">
			<tr>
				<th style="min-width:50px;" class="sticky sno">S No.</th>
				<th style="min-width:200px;" class="sticky buyer">Po No</th>
				<th style="min-width:120px;" class="sticky destination">Po Date</th>
				<th style="min-width:180px;" class="brand">Brand</th>
				<th style="min-width:180px;" class="variety">Variety</th>
				<th style="min-width:180px;" class="grade">Grade</th>
				<th style="min-width:95px;" class="mcs">Mcs</th>
				<?php if(!$hidepricevalue){?>
				<th style="min-width:180px;" class="price">Price</th>
				<?php } ?>
				<th style="min-width:130px;" class="lbs">Qty</th>
				<th style="min-width:70px;" class="unit">Unit</th>
				<?php if(!$hidepricevalue){?>
				<th style="min-width:120px;" class="value">Value</th>
				<?php } ?>
				<th style="min-width:120px;" class="status">Status</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($resList as $item) {
				// Iterate through the BreakUpData for each item
				foreach ($item->BreakUpData as $itemBreak) {
			?>
					<tr>
						<td style="vertical-align: middle" class="sticky sno"><?php echo $item->SNO ?></td>
						<td style="vertical-align: middle;" class="sticky buyer"><?php echo $item->PoNo ?></td>
						<td style="vertical-align: middle" class="sticky destination"><?php echo $item->PoDate ?></td>
						<td style="min-width: 180px;" class="brand"><?php echo $itemBreak->Brand ?>&nbsp;</td>
						<td style="min-width: 180px;" class="variety"><?php echo $itemBreak->Variety ?>&nbsp;</td>
						<td style="min-width: 180px;" class="grade"><?php echo $itemBreak->Grade ?>&nbsp;</td>
						<td style="min-width: 95px;" class="mcs"><?php echo $itemBreak->Mcs ?>&nbsp;</td>
						<?php if(!$hidepricevalue){?>
						<td style="min-width: 180px;" class="price"><?php echo $itemBreak->Price ?>&nbsp;</td>
						<?php } ?>
						<td style="min-width: 130px;" class="lbs"><?php echo $itemBreak->Lbs ?>&nbsp;</td>
						<td style="min-width: 70px;" class="unit"><?php echo $item->QtyUnit ?>&nbsp;</td>
						<?php if(!$hidepricevalue){?>
						<td style="min-width: 120px;" class="value"><?php echo $itemBreak->PriceLbs ?>&nbsp;</td>
						<?php } ?>
						<td style="vertical-align: middle" class="status"><?php echo $item->Status ?></td>
					</tr>
			<?php
				}
			}
			?>
		</tbody>
	</table>

<?php } ?>
