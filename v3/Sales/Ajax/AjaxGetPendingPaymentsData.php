<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$ClientID = $_SESSION['ClientID'];
$DisplayFDAfields = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;
$FreezeCol = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;

// temp conditions for SME
$JoinQuery = isset($ClientID) && $ClientID === 'SMEV3' ? ' left join ScrmRealizationDetails as SRD on POD.PoID=SRD.PoID' : '';
$ClientQuery = isset($ClientID) && $ClientID === 'SMEV3' ? 'and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 )
and SCI.PaymentType="Negotiations" and SRD.RezStatus=0' : 'and POD.PoID not in(SELECT PoID from ScrmRealizationDetails where RezStatus=1)';

$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);
$Consignee = trim($_POST["Consignee"]);
$Country = trim($_POST["Country"]);
$Brand = trim($_POST["Brand"]);
$Month = trim($_POST["Month"]);
$PendingContractsType = trim($_POST["PendingContractsType"]);
$FdaRelease = trim($_POST["FdaRelease"]);

if ($PendingContractsType == '') {
	$ClientQuery = 'and POD.PoID not in(SELECT PoID from ScrmRealizationDetails where RezStatus=1)';
} else if ($PendingContractsType == 'RDP') {
	$ClientQuery = 'and POD.PoID  in (SELECT PIID FROM `ScrmPaymentCollections` where Status=1 )
	and SCI.PaymentType="Negotiations" and POD.PoID  in (SELECT PoID FROM `ScrmRealizationDetails` where RezStatus=0)';
} else if ($PendingContractsType == 'NGCP') {
	$ClientQuery = 'and POD.PoID not in(SELECT PIID FROM `ScrmPaymentCollections` where Status=1 and Type=2) and SCI.PaymentType="Negotiations"';
} else if ($PendingContractsType == 'COLP') {
	$ClientQuery = 'and POD.PoID not in(SELECT PIID FROM `ScrmPaymentCollections` where Status=1 and Type=1) and SCI.PaymentType="Collections"';
}

// echo $Type."<br>";
// echo $Year."<br>";
// echo $Exporter."<br>";
// echo $Processor."<br>";
// echo $Consignee."<br>";
// echo $Country."<br>";

$api = GetPendingPaymentsData_API;
$bodyContent = array(
	'Year' => $Year,
	'Exporter' => $Exporter,
	'Consignee' => $Consignee,
	'Brand' => $Brand,
	'Month' => $Month,
	'Country' => $Country,
	'PendingContractsType' => $PendingContractsType,
	'FdaRelease' => $FdaRelease,
	'JoinQuery' => $JoinQuery,
	'ClientQuery' => $ClientQuery,
	'FromDate' => trim($_POST["FromDate"]),
	'ToDate' => trim($_POST["ToDate"]),
	'Bank' => trim($_POST["Bank"]),
	'PaymentStatus' => trim($_POST["PaymentStatus"])
);


$resGetPendingPaymentsDataAPI = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resGetPendingPaymentsDataAPI, true);

$resList = $resGetPendingPaymentsDataAPI->Data->Data;
// print_r($resList);
// exit;

function pendingPaymentDateOrder($dateValue)
{
	$dateValue = trim((string) $dateValue);

	if ($dateValue === '' || $dateValue === '--' || $dateValue === '0000-00-00') {
		return 0;
	}

	$dateValue = preg_replace('/\s+/', ' ', $dateValue);
	$formats = array(
		'd-m-Y',
		'd/m/Y',
		'd.m.Y',
		'Y-m-d',
		'Y/m/d',
		'd-M-Y',
		'd M Y',
		'd-M-y',
		'd M y',
		'd-m-Y H:i:s',
		'd/m/Y H:i:s',
		'Y-m-d H:i:s'
	);

	foreach ($formats as $format) {
		$date = DateTime::createFromFormat($format, $dateValue);
		$errors = DateTime::getLastErrors();

		if ($date && (!$errors || ($errors['warning_count'] == 0 && $errors['error_count'] == 0))) {
			return $date->format('Ymd');
		}
	}

	$timestamp = strtotime(str_replace('/', '-', $dateValue));
	return $timestamp ? date('Ymd', $timestamp) : 0;
}

?>

<div>

	<table id="key-datatableFullscreen" data-paging="false" class="table  wrap w-100 table-bordered">
		<thead class="thead-light">
			<tr>
				<!-- <th style="min-width: 50px;" class="sticky sno">S No.</th>
			<th class="sticky pndPayInvNo">InvNo </th>
			<th class="sticky pndPayPoNo" style="min-width:100px;">PoNo</th>
			<th style="min-width: 180px;">Consignee</th>
			<th>Destination</th>
			<th>Country</th>
			<th>DeliveredDate</th>
			<th>InvoiceValue</th>
			<th>Realization</th> -->
				<th style="min-width: 60px;" class="<?php echo empty($FreezeCol) ? 'sticky sno' : '';?>">S.No</th>
				<th style="min-width: 140px;" class="<?php echo empty($FreezeCol) ? 'sticky invNo' : '' ;?>">Inv No</th>
				<th style="min-width: <?php echo empty($FreezeCol) ? '120' : '200';?>px;">PO NO</th>
				<th style="min-width: <?php echo empty($FreezeCol) ? '400' : '250';?>px;">Buyer Name</th>
				<th style="min-width: <?php echo empty($FreezeCol) ? '100' : '130';?>px;">Container No</th>
				<th style="min-width: 120px;">Port</th>
				<th style="min-width: 400px;">Payment Terms</th>
				<th style="min-width: 120px;">No. of Carton</th>
				<th style="min-width: 160px;">Invoice Value USD</th>
				<th style="min-width: 160px;">Invoice Value INR</th>
				<th style="min-width: 200px;">Remaining Invoice VALUE</th>
				<th style="min-width: 120px;">On board Date</th>
				<?php if ($DisplayFDAfields) { ?>
					<th style="min-width: 200px;">Mother Vessel Date</th>
				<?php } ?>
				<th style="min-width: 120px;">ETA Date</th>
				<th style="min-width: 150px;">ETA Modify Date </th>
				<th style="min-width: 160px;">Gate OutFull Date</th>
				<th style="min-width: 120px;">FDA EU Release Date</th>

				<?php if ($DisplayFDAfields) { ?>
					<th style="min-width: 120px;">FDA Container Status</th>
					<th style="min-width: 200px;">Bank Details</th>
				<?php } ?>
				<th style="min-width: 140px;">Due on Date</th>
				<th style="min-width: 120px;">FDA Status</th>
			</tr>
		</thead>
		<tbody>
			<?php // $i = 1; 
			$totalCarton = $totalInvValUSD = $totalRemainInvValUSD = $totalInvValINR = 0;
			foreach ($resList as $item) {
				$totalCarton += $item->Mcs;
				$totalInvValINR += (float) str_replace(',', '', $item->TotalInvoiceValueINR);
				$totalInvValUSD += (float) str_replace(',', '', $item->TotalInvoiceValue);
				$totalRemainInvValUSD += (float) str_replace(',', '', $item->RemainingInvoiceValue);
				?>
				<!-- <tr>
				<td class="sticky sno"><?php echo $item->Sno ?></td>
				<td class="sticky pndPayInvNo"><?php echo $item->CInvNo ?></td>
				<td class="sticky pndPayPoNo"><?php echo $item->PoNo ?></td>
				<td><?php echo $item->ConsigneeName ?></td>
				<td><?php echo $item->PortOfDischarge ?></td>
				<td><?php echo $item->DestinationCountry ?></td>
				<td><?php echo $item->DeliveredDate ?></td>
				<td><?php echo $item->TotalInvoiceValue ?></td>
				<td><?php echo $item->RemainingInvoiceValue ?></td>
			</tr> -->
				<tr>
					<td class="<?php echo empty($FreezeCol) ? 'sticky sno' : '' ;?>"><?php echo $item->Sno ?></td>
					<td class="<?php echo empty($FreezeCol) ? 'sticky invNo bodyInvNo' : '' ;?>"><?php echo $item->CInvNo ?></td>
					<td><?php echo $item->PoNo ?></td>
					<td><?php echo $item->ConsigneeName ?></td>
					<td><?php echo $item->ContainerNo ?></td>
					<td><?php echo $item->PortOfDischarge ?></td>
					<td><?php echo $item->PaymentName ?></td>
					<td class="Mcs"><?php echo $item->Mcs ?></td>
					<?php if (strtoupper(trim((string) $item->UnitCurrency)) === 'USD') { ?>
						<td class="TotalInvoiceValue"><?php echo $item->TotalInvoiceValue ?></td>
						<td class="TotalInvoiceValueINR">--</td>
					<?php } else if (strtoupper(trim((string) $item->UnitCurrency)) === 'INR') { ?>
							<td class="TotalInvoiceValue">--</td>
							<td class="TotalInvoiceValueINR"><?php echo $item->TotalInvoiceValueINR ?></td>
					<?php } else { ?>
						<td class="TotalInvoiceValue"><?php echo !empty($item->TotalInvoiceValue) ? $item->TotalInvoiceValue : '--'; ?></td>
						<td class="TotalInvoiceValueINR"><?php echo !empty($item->TotalInvoiceValueINR) ? $item->TotalInvoiceValueINR : '--'; ?></td>
					<?php } ?>
					<td class="bodyTotInvoiceValueUSD RemainingInvoiceValue"><?php echo $item->RemainingInvoiceValue ?></td>
					<td data-order="<?php echo pendingPaymentDateOrder($item->ShippingOnboardDt); ?>"><?php echo $item->ShippingOnboardDt ?></td>
					<?php if ($DisplayFDAfields) { ?>
						<td data-order="<?php echo pendingPaymentDateOrder($item->DepartureDate); ?>"><?php echo $item->DepartureDate ?></td>
					<?php } ?>
					<td data-order="<?php echo pendingPaymentDateOrder($item->ETADate); ?>"><?php echo $item->ETADate ?></td>
					<td data-order="<?php echo pendingPaymentDateOrder($item->ETAModifyDate); ?>"><?php echo $item->ETAModifyDate ?></td>
					<td data-order="<?php echo pendingPaymentDateOrder($item->GetOutFull); ?>"><?php echo $item->GetOutFull ?></td>
					<td data-order="<?php echo pendingPaymentDateOrder($item->FDAEUReleaseCFIADate); ?>"><?php echo $item->FDAEUReleaseCFIADate ?></td>
					<?php if ($DisplayFDAfields) { ?>
						<td><?php echo $item->FDAContainerStatus ?></td>
						<td><?php echo $item->BankDetails ?></td>
					<?php } ?>
					<td data-order="<?php echo pendingPaymentDateOrder($item->DueOn); ?>"><?php echo $item->DueOn ?></td>
				<td><?php echo (!empty(trim($item->FDAContainerStatus))) ? $item->FDAContainerStatus : '--'; ?></td>

				</tr>
				<?php

			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td class="<?php echo empty($FreezeCol) ? 'sticky sno' : '' ;?>"></td>
				<td class="<?php echo empty($FreezeCol) ? 'sticky invNo' : '';?>"><b>Totals :</b></td>
				<!-- <td class="sticky" colspan="5"></td> -->
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td id="totalCarton"><b><?php echo number_format($totalCarton, 0); ?></b></td>
				<td id="totalInvValUSD"><b><?php echo number_format($totalInvValUSD, 2); ?></b></td>
				<td id="totalInvValINR"><b><?php echo number_format($totalInvValINR, 2); ?></b></td>
				<td id="totalRemainInvValUSD"><b><?php echo number_format($totalRemainInvValUSD, 2); ?></b></td>
				<!-- <td colspan="6"></td> -->
				<td></td>
				<?php if ($DisplayFDAfields) { ?>
					<td></td>
				<?php }?>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<?php if ($DisplayFDAfields) {?>
					<td></td>
					<td></td>
				<?php }?>
				<td></td>
				<td></td>
			</tr>
			</tfoot>
	</table>
</div>