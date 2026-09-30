<?php
//ini_set('display_errors', 1);
$OptionsType = isset($_POST['Type']) ? $_POST['Type'] : '';
$DocType = isset($_POST['DocType']) ? $_POST['DocType'] : '';
$InvID = isset($_POST['InvId']) ? $_POST['InvId'] : '';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$TblQcAvalReports = 'QcAvalReportsV1';
$TblQcPoLabSamplingType = 'QcPoLabSamplingType';

if ($OptionsType == 'SaveLabSamplingType') {
	$PoID = isset($_POST['PoID']) ? trim($_POST['PoID']) : '';
	$SamplingType = isset($_POST['SamplingType']) ? (int) $_POST['SamplingType'] : 0;

	header('Content-Type: application/json');

	if ($PoID == '' || !in_array($SamplingType, array(1, 2, 3))) {
		echo json_encode(array('success' => false, 'message' => 'Invalid sampling type'));
		exit;
	}

	$PoID = mysqli_real_escape_string($iconn, $PoID);
	$SqlDeleteSamplingType = "DELETE FROM `$TblQcPoLabSamplingType` WHERE `poid`='$PoID'";
	$iconn->query($SqlDeleteSamplingType);

	$SqlInsertSamplingType = "INSERT INTO `$TblQcPoLabSamplingType` (`poid`, `type`) VALUES ('$PoID', '$SamplingType')";
	$SaveSamplingType = $iconn->query($SqlInsertSamplingType);

	echo json_encode(array(
		'success' => (bool) $SaveSamplingType,
		'message' => $SaveSamplingType ? 'Saved' : 'Unable to save sampling type'
	));
	exit;
}

$SqlInv = "SELECT *,(SELECT COUNT(*) from $TblQcPoVarityList WHERE InvID='$InvID') as Vlist,
(SELECT COUNT(*) from $TblQcCodeList WHERE InvID='$InvID') as Clist
FROM `ScrmPoData` WHERE PoID='$InvID'";

$stmt_inv = $iconn->query($SqlInv);
$row_inv = $stmt_inv->fetch_array();
$DestinationCountry = $row_inv['DestinationCountry'];
$ReportCat = $row_inv['TypeCountry'] . ',3';
$ReportCat = '1,3';

if ($OptionsType == 'DocList') {

	?>




	<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

		<thead class="thead-light">
			<tr>
				<th>Available Reports</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php

			if ($row_inv['Clist'] == 0) {


				$QueryParms = 'PoID=' . $InvID;
				$QueryParms = urlEncode64($QueryParms);

				?>
				<tr>
					<td>Code List</td>
					<td>
						<a target="" href="DocClCreate.php?Q=<?php echo $QueryParms ?>" class="mr-3 text-primary"
							data-toggle="tooltip" data-placement="top" title="" data-original-title="Add"><i
								class="mdi mdi-plus font-size-18"></i></a>
					</td>
				</tr>
			<?php } else {
				$SqlAvalRep = "SELECT * FROM `$TblQcAvalReports` where ReportType=$DocType 
				and ReportCat in($ReportCat) and OptionType !=1 and ReportStatus=1 Order by ReportName asc";
				$StmtAval = $iconn->query($SqlAvalRep);
				// ECHO $SqlAvalRep; 
				// EXIT;
		
				while ($RowAvalRep = $StmtAval->fetch_array()) {
					$TableName = $RowAvalRep['TableName'];
					$QueryID = $RowAvalRep['QueryID'];
					$PoID = $RowAvalRep['PoID'];
					$ReportName = $RowAvalRep['ReportName'];
					$DisplayId = $RowAvalRep['DisplayParam'];
					$DocId = $RowAvalRep['ID'];

					//sriram
					$GroupingFields = $RowAvalRep['GroupingFields'];

					if (!empty($GroupingFields)) {
						// $GroupingFieldsArray = explode(',', $GroupingFields);
						// $GroupingFieldsArray = array_map('trim', $GroupingFieldsArray);
						$join = "LEFT JOIN ScrmPoGradeData as SPDG on QCC.PoGradeID=SPDG.ID LEFT JOIN ScrmPIVarietyList as PVL on PVL.VarietyID=SPDG.VarietyID";
						// $join = " LEFT JOIN ScrmPIVarietyList as PVL on PVL.VarietyID=QCC.VarietyID";
						$fields = ",TypeProduct,ShortName,GROUP_CONCAT($QueryID) AS $QueryID";
						$grouping = "GROUP BY $GroupingFields";
					} else {
						$join = "";
						$fields = "";
						$grouping = "";
					}

					//sriram
					$SqlReport = "select QCC.*$fields from $TableName AS QCC $join where QCC.$PoID='$InvID' $grouping";
					// $SqlReport = "SELECT * , GROUP_CONCAT($QueryID) AS groupIds
					//   FROM `$TableName` 
					//   WHERE `$PoID` = '$InvID'
					//   GROUP BY DayCode,VarietyID";
					$StmtReports = $iconn->query($SqlReport);
					while ($RowRep = $StmtReports->fetch_array()) {

						$QueryParamVal = $RowRep["$QueryID"];

						if ($DisplayId != '') {
							$DisplayName = $RowRep["$DisplayId"];
						} else {
							$DisplayName = '';
						}

						$RowAval['Count'] = 1;

						if ($DisplayName != '') {
							$DisplayName = ' (' . $DisplayName . ')';
						}

						if ($RowAval['Count'] > 0) {
							$QueryParms = '';

							$QueryParms = 'PoID=' . $InvID;
							$QueryParms .= '&RID=' . $QueryParamVal;
							$QueryParms .= '&ReportName=' . $ReportName;
							// $QueryParms .= '&groupIds=' . $RowRep["groupIds"];
		
							$QueryParms = urlEncode64($QueryParms);

							?>

							<tr>
								<td><?php echo $ReportName . $DisplayName ?> </td>
								<td>


									<a target="" href="<?php echo $RowAvalRep['EditLink'] . $QueryParms ?>" class="mr-3 text-primary"
										data-toggle="tooltip" data-placement="top" title="Edit" data-original-title="Add"><i
											class="mdi mdi-pencil font-size-18"></i></a>

									<a target="_blank" href="<?php echo $RowAvalRep['PrintLink'] . $QueryParms ?>" class="mr-3 text-primary"
										data-toggle="tooltip" data-placement="top" title="Print" data-original-title="Add"><i
											class="mdi mdi-printer font-size-18"></i></a>

									<?php if ($DocType == '5') { ?>
										<a target="_blank" href="<?php echo "Reports/shimp-two-excel-report?Q=" . $QueryParms ?>"
											class="mr-3 text-success btnDownloadExcel" data-toggle="tooltip" data-placement="top"
											title="Download Excel" data-original-title="Add"> <i class="mdi mdi-file-excel font-size-18"></i>
										</a>
									<?php } ?>
								</td>
							</tr>

							<?php

						}
					}
				}
			}

			?>





		</tbody>

	</table>
	<?php
} else if ($OptionsType == 'DocCreate') {

	$SqlInv = "SELECT *,
(SELECT COUNT(*) from $TblQcPoVarityList WHERE InvID='$InvID') as Vlist,
(SELECT COUNT(*) from $TblQcCodeList WHERE InvID='$InvID') as Clist
FROM `$TblScrmPoData` WHERE PoID='$InvID'";
	$stmt_inv = $iconn->query($SqlInv);
	$row_inv = $stmt_inv->fetch_array();

	/*$SqlAvalRep="SELECT * FROM `$TblQcAvalReports` 
						where ReportType=$DocType  and ReportCat='$ReportCat' and OptionType !=2 and ReportStatus=1 Order by ReportName asc"; */
	$SqlAvalRep = "SELECT * FROM `$TblQcAvalReports` 
where ReportType=$DocType  and ReportCat in($ReportCat) and OptionType !=2 and ReportStatus=1 Order by ReportName asc";
	$StmtAval = $iconn->query($SqlAvalRep);

	?>

		<table id="scroll-vertical-datatable" class="table table-striped 
		table-bordered dt-responsive nowrap">
			<thead>
				<tr>
					<th>Available Reports</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>

				<?php

				$QueryParms = 'PoID=' . $InvID;
				$QueryParms = urlEncode64($QueryParms);


				while ($RowAvalRep = $StmtAval->fetch_array()) {
					$TableName = $RowAvalRep['TableName'];
					$ReportName = $RowAvalRep['ReportName'];
					$SamplingType = '';
					$ShowSamplingCreate = true;
					$IsExternalLabsSamplingLetter = ($ReportName == 'External Labs Sampling Letter');

					if ($IsExternalLabsSamplingLetter) {
						$EscapedInvID = mysqli_real_escape_string($iconn, $InvID);
						$SqlLabSamplingType = "SELECT `type` FROM `$TblQcPoLabSamplingType` WHERE `poid`='$EscapedInvID' LIMIT 1";
						$StmtLabSamplingType = $iconn->query($SqlLabSamplingType);
						if ($StmtLabSamplingType && $RowLabSamplingType = $StmtLabSamplingType->fetch_array()) {
							$SamplingType = $RowLabSamplingType['type'];
						}
						$ShowSamplingCreate = ($SamplingType != '');
					}
					?>

					<tr>
						<td><?php echo $ReportName ?></td>
						<td>
						<?php if ($IsExternalLabsSamplingLetter) { ?>
								<select class="form-control form-control-sm d-inline-block mr-2 qc-lab-sampling-type"
									style="width: 140px;" data-poid="<?php echo htmlspecialchars($InvID, ENT_QUOTES); ?>">
									<option value="" disabled <?php echo ($SamplingType == '') ? 'selected' : ''; ?>>Select Type</option>
									<option value="1" <?php echo ($SamplingType == '1') ? 'selected' : ''; ?>>Regular</option>
									<option value="2" <?php echo ($SamplingType == '2') ? 'selected' : ''; ?>>Buyer</option>
									<option value="3" <?php echo ($SamplingType == '3') ? 'selected' : ''; ?>>Both</option>
								</select>
						<?php } ?>
							<a target="" href="<?php echo $RowAvalRep['AddLink'] . $QueryParms ?>"
								class="mr-3 text-primary <?php echo $IsExternalLabsSamplingLetter ? 'qc-lab-sampling-create-link' : ''; ?>"
								style="<?php echo ($IsExternalLabsSamplingLetter && !$ShowSamplingCreate) ? 'display:none;' : ''; ?>"
								data-toggle="tooltip" data-placement="top" title="" data-original-title="Add"><i
									class="mdi mdi-plus font-size-18"></i></a>
						</td>
					</tr>

			<?php } ?>

			</tbody>

		</table>
<?php } ?>