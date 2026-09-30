<?php
//session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

if(!empty($_POST["MethodType"]) && $_POST["MethodType"] == 'AddEditCertificate'){
	$api = StoreCertificateNumber_API;
	$bodyContent = $_POST;
	$res = CallAPI($api, "POST", "", $bodyContent);
	echo json_encode($res);die;
}
// $BillsType = trim($_POST["BillsType"]);
$Year = trim($_POST["Year"]);
$Exporter = trim($_POST["Exporter"]);
$Consignee = trim($_POST["Consignee"]);
$Country = trim($_POST["Country"]);
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$Hide10PercentColumn = !empty($ClientConfigData->Hide10PercentChargeInInsuranceScreen)?$ClientConfigData->Hide10PercentChargeInInsuranceScreen:'';//DFLVE
$EnableCertificationNoAndDebitNote = !empty($ClientConfigData->EnableCertificationNoAndDebitNote) ? trim($ClientConfigData->EnableCertificationNoAndDebitNote):0; //anil
//1. Insurance Type Chnaged Rejection to Transit & rejection, 2. Enable Add/Edit Certification Number, 3. Dispaly Consignee Name at Insurane Payments, 4. Add new field i.e. Debit Note in Insurance Payment Form.


$api = GetPendingInsuranceList_API;

$bodyContent = array(
			'BillsType'=>"0",
            'Year'=>$Year,
            'Exporter'=>$Exporter,
            'Consignee'=> (int)$Consignee,
            'Country'=>$Country);        

$resGetShipingBillsAPI = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($_SESSION['ClientID']);die;
$List = $resGetShipingBillsAPI->Data->Data;
// echo json_encode($List);exit;
// var_dump($List);

?>
<table  id="key-datatable" data-paging="false" class="table  wrap w-100" >
	<thead class="thead-light">
		<tr>
			<th class="cellCheckbox text-center" style="min-width: 80px;">
				<input type="checkbox" class="big-checkbox" id="checkall" />
			</th>
			<th style='min-width:55px;'>S.No</th>
			<th style='min-width:55px;'>ID</th>	
			<th style='min-width:100px;'>Inv No</th>
			<th style='min-width:120px;'>Vessel Name</th>
			<th style='min-width:150px;'>Shipping Onboard Dt</th>
			<th style='min-width:120px;'>Container No</th>
			<th style='min-width:120px;'>Port of Loading</th>
			<th style='min-width:150px;'>Port Of Discharge</th>
			<th style='min-width:150px;'>Destination Country</th>
			<th style='min-width:150px;'>Total Invoice Value</th>
			<?php 
			if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>
				<th style='min-width:120px;'>Invoice Value(%)</th>
			<?php }?>
			<th <?php 
				if(!empty($EnableCertificationNoAndDebitNote)){?>
				style='min-width:100px;'
				<?php }?>
				>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($List as $item) { ?>		
		<tr>
			<td class="tdCheckList text-center">
				<input type="checkbox" class="rowCheck big-checkbox"  value="<?php echo $item->PoID ?>" />
			</td>
			<td><?php echo $item->Sno ?></td>	
			<td><?php echo $item->PoID ?></td>
            <td><?php echo $item->CInvNo ?></td>
			<td><?php echo $item->VesselName; ?></td>
			<td><?php echo $item->ShippingOnboardDt ?></td>
			<td><?php echo $item->ContainerNo; ?></td>
			<td><?php echo $item->PortofLoading; ?></td>
			<td><?php echo $item->PortOfDischarge; ?></td>
			<td><?php echo $item->DestinationCountry; ?></td>
			<td><?php
			if(!empty($Hide10PercentColumn)  && $Hide10PercentColumn != '0000-00-00'){
				echo ($item->CInvDate >= $Hide10PercentColumn) ? $item->TotalInvoiceValue : $item->InvoiceValuewithPercentage;
			}else{
				echo $item->TotalInvoiceValue;
			}
			 ?></td>
			<?php 
			if(empty($Hide10PercentColumn)  || $Hide10PercentColumn == '0000-00-00'){?>
				<td><?php echo $item->InvoiceValuewithPercentage; ?></td>
			<?php }?>
			 <td>		
				<!--<a href='<?php //echo $resGetShipingBillsAPI->Data->Link.$item->PoID ?>' target="_blank" class='mr-3 text-primary' title='Custom Invoice'><i class='mdi mdi-eye font-size-18'></i></a>-->
				<a href='<?php echo $item->Link ?>' target="_blank" class='mr-3 text-primary' title='View'><i class='mdi mdi-eye font-size-18'></i></a>
				<?php 
				if(!empty($EnableCertificationNoAndDebitNote)){?>
					<a href='javascript:void(0);' data-certification_id="<?php echo $item->CertificationID ?>"  data-inv_id="<?php echo $item->PoID ?>" data-certification_number="<?php echo $item->CertificationNumber ?>" class='mr-2 text-success AddEditCertification' title='Add/Edit Certification Number' data-toggle="modal" data-target="#ModalAddCertification" ><i class='mdi mdi-plus font-size-20'></i></a>
				<?php } ?>
			</td> 
		</tr>
		<?php } ?>
	</tbody>
</table>



