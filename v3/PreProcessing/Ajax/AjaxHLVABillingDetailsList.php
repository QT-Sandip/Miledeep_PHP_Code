<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$HlVaApproval = $ArrayClientConfigData['HlVaApproval'];

$Month = $_POST['Month'];
$Month = sprintf("%02d", $Month);
$Year = $_POST['Year'];
$EntryType = $_POST['EntryType'];
$FromDate = $_POST['FromDate'];
$ToDate = $_POST['ToDate'];
$Type = $_POST['EntryType'];
$ContractorID = $_POST['ContractorID'];
$AccessType = $_SESSION['AccessType'];
$DeptID = $_SESSION['DeptID'];
$StationID = $_POST['PsId'];
$ApprovalStatus = $_POST['approvalStatus'];




$Query = "where Date between '$FromDate'  and '$ToDate'  ";
if ($ContractorID != '') {
	$Query .= "and  ContactorID='$ContractorID'";
}
if ($Type != '') {
	$Query .= "and Type='$Type'";
}
if ($StationID != '') {
	$Query .= "and StationID='$StationID'";
}

if ($ApprovalStatus != '') {
	$Query .= "and ApprovalStatus='$ApprovalStatus'";
}

$GroupBy = '';

if ($FromDate != $ToDate) {
	$GroupBy = "GROUP BY SubContactorID";
}

$SqlDetails = "SELECT PSCM.*,PDBD.ID,PDBD.ApprovalStatus FROM `PpcSubContractorMaster` AS PSCM 
LEFT JOIN PpcDailyBatchDetails AS PDBD ON PSCM.SubContactorID=PDBD.BatchID $Query $GroupBy";
/*  echo $SqlDetails="SELECT SubContactorID,PDBD.Date,SubContractorName,PSC.AutoCharge,PO.Name as Shift,PDBD.Remarks,PDBD.Extras,PDBD.Adjustments,PDBD.Persons  FROM PpcDailyBatchDetails PDBD 
 left join PpcSubContractorMaster as PSC on PSC.SubContactorID=PDBD.BatchID 
 left join PpcContractMaster as PCM on PCM.ContractorID=PSC.ContactorID 
 left join PpcOptions as PO on PO.OptID=PDBD.ShiftID 
 where SubContactorID in (select DISTINCT BatchID from PpcDailyBatchDetails)  $Query"; exit;*/

$StmtSubContractor = $iconn->query($SqlDetails);
?>

<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

	<thead class="thead-light">


		<th align="CENTER">Sub Contractor Name </th>
		<th align="CENTER">From Date</th>
		<th align="CENTER">To Date</th>
		<th align="CENTER">Actions</th>
	</thead>
	<tbody>



		<?php
		while ($RowSubContractor = $StmtSubContractor->fetch_array()) {

			$BatchID = '';

			$BatchID = $RowSubContractor['SubContactorID'];

			$QueryParms = 'Month=' . $FromDate . '&Year=' . $ToDate . '&ContractorID=' . $ContractorID . '&EntryType=' . $EntryType . '&BatchId=' . $BatchID;
			//	$QueryParms=urlEncode64($QueryParms);
		
			?>

			<tr>

				<td align="left" width="10%">
					<font size="'.$text.'"><?php echo $RowSubContractor['SubContractorName']; ?></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $FromDate;
					; ?></B></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><?php echo $ToDate; ?></font>
				</td>

				<td align="left" width="5%">
					<font size="'.$text.'">

						<a target="_blank" href="Reports/hl-pd-bill-print.php?<?php echo $QueryParms ?>"
							class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top" title="PD Bill Print"
							data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
						<?php
						if ($AccessType != 3 && $HlVaApproval != 0 && $RowSubContractor['ApprovalStatus'] == 0 && $FromDate == $ToDate) { ?>
							<i class="text-primary mdi mdi-check-decagram font-size-18" id="hlva-approval"
								data-batchid="<?php echo $RowSubContractor['ID']; ?>" style="cursor: pointer;"></i>
						<?php } ?>
					</font>
				</td>

			</tr>


		<?php } ?>
	</tbody>
</table>