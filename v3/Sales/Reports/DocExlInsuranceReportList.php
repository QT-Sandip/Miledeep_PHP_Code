<?php
//session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
// include ($cp."Includes/IncludePage.php");

include ("../RestAPI.php");

$InsuranceID = trim($_GET["ID"]);
$FileName = trim($_GET["name"]);

$api = GetPendingInsuranceList_API;
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$Hide10PercentColumn = !empty($ClientConfigData->Hide10PercentChargeInInsuranceScreen)?$ClientConfigData->Hide10PercentChargeInInsuranceScreen:'';//DFLVE


$bodyContent = array(
			'BillsType'=>"1",
            'InsuranceID'=>$InsuranceID);

$resGetShipingBillsAPI = CallAPI($api, "GET", "", $bodyContent);

$List = $resGetShipingBillsAPI->Data->Data;    
$ListTotals = $resGetShipingBillsAPI->Data->Totals;    

// header("Content-Type: application/xls");
// header("Content-Disposition: attachment; filename=" . $FileName . ".xls");

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename='.$FileName.'.xls');

$content = '
<table  id="key-datatable" data-paging="false" class="table  wrap w-100" border="1">
	<thead class="thead-light">
		<tr>			
			<th style="min-width:55px;">S.No</th>
			<th style="min-width:100px;">Inv No</th>
			<th style="min-width:220px;">Vessel Name</th>
			<th style="min-width:150px;">Shipping Onboard Date</th>
			<th style="min-width:120px;">Container No</th>
			<th style="min-width:120px;">Port of Loading</th>
			<th style="min-width:150px;">Port Of Discharge</th>
			<th style="min-width:150px;">Destination Country</th>
			<th style="min-width:150px;">Total Invoice Value</th>';
			$content .= $Hide10PercentColumn != 1?'<th style="min-width:120px;">Invoice Value(%)</th>':'';
			$content .= '
		</tr>
	</thead>
	<tbody>';
		foreach($List as $item) { 
		$content .='
		<tr>
			<td>'.$item->Sno.'</td>
            <td>'.$item->CInvNo.'</td>
			<td>'.$item->VesselName.'</td>
			<td>'.$item->ShippingOnboardDt.'</td>
			<td>'.$item->ContainerNo.'</td>
			<td>'.$item->PortofLoading.'</td>
			<td>'.$item->PortOfDischarge.'</td>
			<td>'.$item->DestinationCountry.'</td>
			<td>'.$item->TotalInvoiceValue.'</td>';
			$content .= $Hide10PercentColumn != 1?'<td>'.$item->InvoiceValuewithPercentage.'</td>':'';
			$content .= '
		</tr>';
		}
	$content .='
	</tbody>
	<tbody>
		<tr>
			<td style="font-weight: bold">Total</td>
            <td style="font-weight: bold">' . $ListTotals->TotalInvoices . '</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td style="font-weight: bold">' . $ListTotals->TotalInvoiceValueUSD . '</td>';
			$content .= $Hide10PercentColumn != 1?'<td style="font-weight: bold">' . $ListTotals->TotalInvoiceValuewithPercentage . '</td>':'';
			$content .= '
		</tr>
	</tbody>
</table>';

echo $content;

?>


