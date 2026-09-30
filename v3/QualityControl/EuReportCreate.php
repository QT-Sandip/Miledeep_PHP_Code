<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

if ($_POST['Type'] == 'Delete') {
    $euid = $_POST['euid'];
    $oldEureportRow = legacyAuditFetchRowById('eureport', 'euid', $euid, 'QualityControl');
    $SqlKitData = "
        SELECT 
            QRAB.KitBreakUpID,
            BreakUpwiseUsedWells,
            BreakUpwiseDamagedWells,
            UtilizedWells,
            BalanceWells,
            DamagedWells 
        FROM `QcRmAbUtilizedKitBreakupDetails` as QRAB 
        left join QcKitsBreakupDetails as QKBD on QKBD.KitBreakupID=QRAB.KitBreakUpID 
        WHERE AbReportID='$euid'
    ";

    $StmtKitData = $iconn->query($SqlKitData);
    while ($Data = $StmtKitData->fetch_array()) {
        $KitBreakUpID = $Data['KitBreakUpID'];
        $oldKitRow = legacyAuditFetchRowById('QcKitsBreakupDetails', 'KitBreakupID', $KitBreakUpID, 'QualityControl');
        $UpdatedUsedWells = $Data['UtilizedWells'] - $Data['BreakUpwiseUsedWells'];
        $UpdatedDamagedWells = $Data['DamagedWells'] - $Data['BreakUpwiseDamagedWells'];
        $UpdatedBalanceWells = $Data['BalanceWells'] + $Data['BreakUpwiseUsedWells'] + $Data['BreakUpwiseDamagedWells'];

        $SqlkitDataUpdate = "UPDATE `QcKitsBreakupDetails` SET `UtilizedWells`='$UpdatedUsedWells',
        `BalanceWells`='$UpdatedBalanceWells',`DamagedWells`='$UpdatedDamagedWells'
         WHERE `KitBreakupID`='$KitBreakUpID'";

        $StmtKitUpdate = $iconn->query($SqlkitDataUpdate);
        if ($StmtKitUpdate) {
            legacyAuditLogUpdateById('QcKitsBreakupDetails', 'KitBreakupID', $KitBreakUpID, $oldKitRow, $_POST, ['Remarks' => 'EU Report Delete - Kit Breakup Update'], 'QualityControl');
        }
    }
    $oldUtilizedKitsRow = legacyAuditFetchRowById('QcRmAbUtilizedKitBreakupDetails', 'ID', $euid, 'QualityControl');
    $SqlDeleteUtilizedKitsData = "DELETE FROM `QcRmAbUtilizedKitBreakupDetails` WHERE AbReportID='$euid'";
    $StmtDeleteUtilizedKitsData = $iconn->query($SqlDeleteUtilizedKitsData);

    $SqlDeleteAbReportData = "DELETE FROM `eureport` WHERE euid='$euid'";
    $StmtDeleteAbReportData = $iconn->query($SqlDeleteAbReportData);
    if ($StmtDeleteAbReportData) {
        legacyAuditLogDeleteById('eureport', 'euid', $euid, $oldEureportRow, ['Remarks' => 'EU Report Deleted'], 'QualityControl');
        $error = null;
        $Msg = 'Deleted Successfully';

    } else {
        $error = $iconn->error;
    }
    $data['error'] = $error;
    $data['Msg'] = $Msg;
    echo json_encode($Data);
    exit;
}

$PageTitleFolder = "Quality Control";
$PageTitle = "Eu Report";

$ClientConfigData = $_SESSION['ClientConfigData'];
$DefaultNoOfWells = $ClientConfigData->DefaultNoOfWells; // uncomment this line after auth-login.php converted to API modal
// $DefaultNoOfWells = $ClientConfigData['DefaultNoOfWells']; // remove this line after auth-login.php converted to API modal

$DOA = $_GET['DOA'];
$DOR = $_GET['DOR'];

$FromDate = $_GET['FromDate'];
$ToDate = $_GET['ToDate'];

$DateFilter = "between '$FromDate' and  '$ToDate'";

$SqlLabName = "SELECT * FROM `QcEiaLabNames` WHERE Status=1";
$StmtLabName = $iconn->query($SqlLabName);

$DateofAnalysis = date("Y-m-d", strtotime($DOA));

$SqlAbReportData = "
    SELECT 
        QRA.euid,
        QRA.SampleDetails,
        RLD.Pono,        
        QRA.Remarks as AbRemarks,
        
        (
            SELECT 
                group_concat(distinct KitBreakupLotNo) 
            FROM `QcRmAbUtilizedKitBreakupDetails` as QRAB 
            left join QcKitsBreakupDetails as QKBD on QKBD.KitBreakupID=QRAB.KitBreakUpID 
            WHERE AbReportID=QRA.euid and type='Eu'
        ) as KitBreakupLotNo, 
        (
            SELECT 
                sum(BreakUpwiseUsedWells - StandardUsedWells) 
            FROM `QcRmAbUtilizedKitBreakupDetails` 
            WHERE AbReportID=QRA.euid AND type='Eu'
        ) as UsedWells,
        (
            SELECT 
                sum(StandardUsedWells) 
            FROM `QcRmAbUtilizedKitBreakupDetails`
            WHERE AbReportID=QRA.euid and type='Eu'
        ) as StandardUsedWells,
        (
            SELECT 
                sum(BreakUpwiseDamagedWells) 
            FROM `QcRmAbUtilizedKitBreakupDetails` 
            WHERE AbReportID=QRA.euid and type='Eu'
        ) as DamagedWells, 
        Parameter,
        Result,
        Name,
        Shift,
        TestedBy 
        FROM eureport as QRA 
        left join ScrmPoData as RLD on RLD.PoId=QRA.Pono 
        left join QcABOptions as QAO on QAO.OptID=QRA.AbStatus
        where AnalysisDate='$DateofAnalysis' order by euid desc;";
//echo $SqlAbReportData; exit;
$StmtAbReportData = $iconn->query($SqlAbReportData);

$SqlDropDownOptions = "SELECT * FROM QcABOptions  ORDER BY SortOrder ASC   ";
$StmtDropDownOptions = $iconn->query($SqlDropDownOptions);
$ArrayDropDownOptions = array();
while ($RowDropDownOptions = $StmtDropDownOptions->FETCH_ARRAY()) {
    $ArrayDropDownOptions[] = $RowDropDownOptions;
}

$sql_LAB_USERS = "SELECT * FROM QcLabTechnologist where LbuStatus=1 ";
$stmt_LAB_USERS = $iconn->query($sql_LAB_USERS);
$stmtReceivedBy = $iconn->query($sql_LAB_USERS);
$stmtAnalyzedBy = $iconn->query($sql_LAB_USERS);
$stmtVerifiedBy = $iconn->query($sql_LAB_USERS);

$sql_po = "SELECT PoId,PoNo FROM `ScrmPoData` where InvDate $DateFilter ";
$stmt_po = $iconn->query($sql_po);
$polist_masters = array();
while ($row_po = $stmt_po->fetch_assoc()) {
    $polist_masters[] = $row_po;
}

extract($_POST);
extract($_GET);
$PostedDate = date("Y-m-d");


function updateKitsInfo($iconn, $LastInsertedID, $KitBreakUpLotIDs, $EnteredDamagedWells, $EnteredUsedWells, $KitOpeningDate, $StandardUsedWells)
{
    if ($KitBreakUpLotIDs) {
        $SqlGetKitID = "SELECT QKBD.KitID,QKBD.KitBreakupID,UtilizedWells,BalanceWells,DamagedWells,KitOpeningDate 
		FROM `QcKitsBreakupDetails` as QKBD WHERE QKBD.KitBreakupID in($KitBreakUpLotIDs) order by BalanceWells asc";
        $StmtGetKitBreakUpID = $iconn->query($SqlGetKitID);
        //echo $SqlGetKitID; exit;
        while ($RowgetKitBreakUpID = $StmtGetKitBreakUpID->fetch_assoc()) {

            $SqlWells = "SELECT sum(BreakUpwiseUsedWells) as TotalUsedWells,sum(BreakUpwiseDamagedWells) as TotalDamagedWells,sum(BreakUpwiseUsedWells+BreakUpwiseDamagedWells) as TotalUpdatedWells,sum(StandardUsedWells) as TotalStandardUsedWells FROM `QcRmAbUtilizedKitBreakupDetails` 
            where AbReportID='$LastInsertedID' AND type ='Eu'";
            $StmtWells = $iconn->query($SqlWells);
            $RowWells = $StmtWells->fetch_assoc();

            $KitID = $RowgetKitBreakUpID['KitID'];
            $KitLotBreakupID = $RowgetKitBreakUpID['KitBreakupID'];
            $BalanceWells = $RowgetKitBreakUpID['BalanceWells'];
            $OldKitOpeningDate = $RowgetKitBreakUpID['KitOpeningDate'];
            $OpeningDate = empty($OldKitOpeningDate) ? $KitOpeningDate : $OldKitOpeningDate;
            $TotalWellsEntered = $EnteredDamagedWells + $EnteredUsedWells;
            $BreakUpwiseDamagedWells = 0;
            $RemainingWells = $TotalWellsEntered - $RowWells['TotalUpdatedWells'];

            $StandardWells = ($StandardUsedWells == $RowWells['TotalStandardUsedWells']) ? 0 : $StandardUsedWells;


            if ($RemainingWells != 0) {

                if ($BalanceWells >= $TotalWellsEntered) {

                    $BreakUpwiseUsedWells = $EnteredUsedWells - $RowWells['TotalUsedWells'];
                    $BreakUpwiseDamagedWells = $EnteredDamagedWells - $RowWells['TotalDamagedWells'];

                } else if ($BalanceWells <= $RemainingWells) {
                    $BreakUpwiseUsedWells = $BalanceWells;

                } else if ($BalanceWells > $RemainingWells) {
                    $BreakUpwiseUsedWells = $RemainingWells;
                }

                $SqlInsertBreakUp = "INSERT INTO `QcRmAbUtilizedKitBreakupDetails`(`AbReportID`, `KitID`, `KitBreakUpID`,
			 `BreakUpwiseUsedWells`, `BreakUpwiseDamagedWells`, `StandardUsedWells`, `type`) VALUES ('$LastInsertedID','$KitID','$KitLotBreakupID',
			 '$BreakUpwiseUsedWells','$BreakUpwiseDamagedWells','$StandardWells','Eu')";
                $StmtInsertBreakUp = $iconn->query($SqlInsertBreakUp);

                if ($StmtInsertBreakUp) {
                    $insertedKitBreakupId = $iconn->insert_id;
                    legacyAuditLogInsertById('QcRmAbUtilizedKitBreakupDetails', 'ID', $insertedKitBreakupId, $_POST, ['Remarks' => 'EU Report - Kit Breakup Utilized Added'], 'QualityControl');

                    $SqlGetWells = "SELECT NoOfWells,UtilizedWells,QKBD.DamagedWells , sum(BreakUpwiseUsedWells) as ABUsedWells,
sum(QRAR.BreakUpwiseDamagedWells) as ABDamagedWells FROM `QcRmAbUtilizedKitBreakupDetails` as QRAR 
left join QcKitsBreakupDetails as QKBD on QKBD.KitBreakupID=QRAR.KitBreakUpID
 where QRAR.KitBreakUpID ='$KitLotBreakupID'";

                    $StmtGetWells = $iconn->query($SqlGetWells);
                    $RowGetWells = $StmtGetWells->fetch_assoc();
                    $TotalUtilizedWells = $RowGetWells['ABUsedWells'];
                    $TotalDamagedWells = $RowGetWells['ABDamagedWells'];
                    $RemainingBalanceWells = $RowGetWells['NoOfWells'] - ($TotalUtilizedWells + $TotalDamagedWells);

                    $oldKitBreakupRow = legacyAuditFetchRowById('QcKitsBreakupDetails', 'KitBreakupID', $KitLotBreakupID, 'QualityControl');
                    $SqlUpdatekitBreakupDetails = "UPDATE `QcKitsBreakupDetails` SET `KitOpeningDate`='$OpeningDate',`UtilizedWells`='$TotalUtilizedWells',
		`BalanceWells`='$RemainingBalanceWells',`DamagedWells`='$TotalDamagedWells' WHERE  `KitBreakupID`='$KitLotBreakupID'";

                    $StmtUpdatekitBreakupDetails = $iconn->query($SqlUpdatekitBreakupDetails);
                    if ($StmtUpdatekitBreakupDetails) {
                        legacyAuditLogUpdateById('QcKitsBreakupDetails', 'KitBreakupID', $KitLotBreakupID, $oldKitBreakupRow, $_POST, ['Remarks' => 'EU Report - Kit Breakup Updated'], 'QualityControl');
                    }
                }
            } else {

            }
        }
    }
}

if (isset($_POST['submit'])) {

    $DateofAnalysis = date("d-m-Y", strtotime($AnalysisDate));
    $DateofCompletion = date("d-m-Y", strtotime($ReportDate));

    $SelectedKitBreakUpLotIDs = isset($_POST['KitBreakupID']) ? $_POST['KitBreakupID'] : [];
    $KitBreakUpLotIDs = implode(',', $SelectedKitBreakUpLotIDs);

    $EnteredDamagedWells = $DamagedWells;
    $StandardUsedWells = 0;

    $SelectedLotIDs = isset($_POST['Pono']) ? $_POST['Pono'] : [];
    $Pono = implode(',', $SelectedLotIDs);

    $SelectedParameter = $Parameter;
    if ($SelectedParameter == 'AOZ') {
        $AOZ = $RESULT;
    } else if ($SelectedParameter == 'SEM') {
        $SEM = $RESULT;
    } else if ($SelectedParameter == 'CAP') {
        $CAP = $RESULT;
    } else if ($SelectedParameter == 'AHD') {
        $AHD = $RESULT;
    } else if ($SelectedParameter == 'AMOZ') {
        $AMOZ = $RESULT;
    }


    $sql_org_insert = "INSERT INTO eureport
	( 
        AnalysisDate, ReportDate, SampleDetails, Pono,
        Result, Sulphite , LoadingStatus, Remarks, Shift, 
        TestedBy,AbStatus, PostedDate, UserID, GatePassNo, SamplerName,
        ReceivedBy,AnalyzedBy,VerifiedBy,ExternalLab,ExternalSampleSendingDate,
        ExternalIssuedResultDate,ExternalResult,Parameter,SpkBlkStd,AbSampleCount,AbsampleQuantity
    )
	VALUES
	('$AnalysisDate','$ReportDate','$SAMPLE_DETAILS','$Pono',
	'$RESULT', '$SULPHITE', '$LOADING_STATUS','$REMARKS','$SHIFT',
    '$TESTED_BY','$AbStatus','$PostedDate','$UserID', '$GatePassNo','$SamplerName',
    '$ReceivedBy', '$AnalyzedBy','$VerifiedBy','$ExternalLab','$ExternalSampleSendingDate',
    '$ExternalIssuedResultDate','$ExternalResult','$Parameter','$SpkBlkStd','$COUNT','$QUANTITY'
    )";

    //echo "<pre>"; print_r($sql_org_insert); exit;
    $stmt1 = $iconn->query($sql_org_insert);
    $LastInsertedID = $iconn->insert_id;

    if ($stmt1) {
        legacyAuditLogInsertById('eureport', 'euid', $LastInsertedID, $_POST, ['Remarks' => 'EU Report Created'], 'QualityControl');
        if ($SpkBlkStd == 1) {

            $SqlAutoDeductCount = "SELECT count(*) as AutoDeductCount,(SELECT sum(StandardUsedWells) FROM `QcRmAbUtilizedKitBreakupDetails` 
        where AbReportID in (QRAB.euid))  as StandardUsedWells FROM `QcRmAbReport` as QRAB
 WHERE  AnalysisDate='$AnalysisDate' and Parameter='$Parameter'
        and Shift='$SHIFT' and SpkBlkStd='1' ";

            $StmtAutoDeductCount = $iconn->query($SqlAutoDeductCount);
            $RowAutoDeductCount = $StmtAutoDeductCount->fetch_array();
            $TotalStandardUsedWells = $RowAutoDeductCount['StandardUsedWells'];
            if ($TotalStandardUsedWells == 0) {
                $StandardUsedWells = 8;
                $EnteredUsedWells = ($StandardUsedWells + $UsedWells);
            } else {
                $EnteredUsedWells = $UsedWells;
            }
        } else {
            $EnteredUsedWells = $UsedWells;
        }
        updateKitsInfo($iconn, $LastInsertedID, $KitBreakUpLotIDs, $EnteredDamagedWells, $EnteredUsedWells, $KitOpeningDate, $StandardUsedWells);

        echo "<script>alert('Data posted successfully )</script>";

        header('Location: EuReportCreate.php?DOA=' . $DateofAnalysis . '&DOR=' . $DateofCompletion . '&FromDate=' . $FromDate . '&ToDate=' . $ToDate);
    }


}
?>

<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

    <?php
    include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <div class="modal fade" id="myModal" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title modheading"></h4>
                                </div>

                                <div class="modal-body">
                                    <div class="box-body" id="ATT_DATA">
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="form-group col-lg-3">
                                                <label for="PoId">Po No</label>
                                                <select class="form-control select2" name="Pono[]" id="Pono" multiple>
                                                    <option Value=""> Select Po No</option>
                                                    <?php foreach ($polist_masters as $podata) {
                                                        ?>
                                                        <option Value="<?php echo $podata['PoId']; ?>">
                                                            <?php echo $podata['PoNo']; ?>
                                                        </option>
                                                    <?php } ?>
                                                    <option Value="VALIDATION"> Validation</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="AnalysisDate">Analysis Date</label>
                                                <input required type="date" class="form-control" name='AnalysisDate'
                                                    id="AnalysisDate"
                                                    value="<?php echo date("Y-m-d", strtotime($DOA)); ?>"
                                                    max="<?php echo date("Y-m-d"); ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="ReportDate">Report Date</label>
                                                <input required type="date" class="form-control" name='ReportDate'
                                                    id="ReportDate"
                                                    value="<?php echo date("Y-m-d", strtotime($DOR)); ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="SAMPLE_TYPE">Sample Type</label>
                                                <select class="form-control select2" name="SAMPLE_TYPE"
                                                    id="SAMPLE_TYPE">

                                                    <option value="Farm Sample">Farm Sample</option>
                                                    <option value="Raw Material Sample">Raw Material Sample</option>
                                                    <option value="Grading Sample">Grading Sample</option>
                                                    <option value="Finished Product">Finished Product</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="SAMPLE_DETAILS">Sample Details</label>
                                                <input type="hidden" name="LOT_ID" id="LOT_ID">
                                                <input type="text" class="form-control" name='SAMPLE_DETAILS'
                                                    id="SAMPLE_DETAILS" placeholder="Enter Sample Details">
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="QUANTITY">Mcs</label>
                                                <input required type="text" class="form-control" name="QUANTITY"
                                                    id="QUANTITY" placeholder="Enter Quantity">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="AbStatus"> Testing Status</label>
                                                <select class="form-control select2" name="AbStatus" id="AbStatus">
                                                    <option Value=""> Select </option>
                                                    <?php foreach ($ArrayDropDownOptions as $DropDownOptions) {
                                                        if ($DropDownOptions['Category'] == 'TestStatus') {
                                                            $Options = $DropDownOptions['Name'];
                                                            $Value = $DropDownOptions['OptID']; ?>
                                                            <option value="<?php echo $Value ?>"><?php echo $Options ?></option>
                                                        <?php }
                                                    } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="SHIFT">Shift</label>
                                                <select class="form-control select2" name="SHIFT" id="SHIFT">
                                                    <option>DAY</option>
                                                    <option>NIGHT</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="TESTED_BY">Tested By</label>
                                                <select class="form-control select2" name="TESTED_BY" id="TESTED_BY">
                                                    <?php while ($LAB_USERS_details = $stmt_LAB_USERS->fetch_array()) { ?>
                                                        <option value="<?php echo $LAB_USERS_details['LbuName'] ?>">
                                                            <?php echo $LAB_USERS_details['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">External Lab Name</label>
                                                <select class="form-control select2" name="ExternalLab"
                                                    id="ExternalLab">
                                                    <option value="">Select </option>
                                                    <?php while ($RowEiaLabs = $StmtLabName->fetch_array()) { ?>

                                                        <option value="<?php echo $RowEiaLabs['LabId']; ?>">
                                                            <?php echo $RowEiaLabs['LabName'] . ' - ' . $RowEiaLabs['Address']; ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="ExternalSampleSendingDate">External Sample Sending
                                                    Date</label>
                                                <input type="date" class="form-control" name='ExternalSampleSendingDate'
                                                    id="ExternalSampleSendingDate">

                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="ExternalIssuedResultDate">External Issued Result
                                                    Date</label>
                                                <input type="date" class="form-control" name='ExternalIssuedResultDate'
                                                    id="ExternalIssuedResultDate">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="ExternalResult">External Result</label>
                                                <input type="text" class="form-control" name='ExternalResult'
                                                    id="ExternalResult" placeholder="Enter External Result">
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="REMARKS">Remarks</label>
                                                <input type="text" class="form-control" name='REMARKS' id="REMARKS"
                                                    placeholder="Enter Remarks">
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="LotStatus">Lot Status</label>
                                                <select class="form-control select2" name="LotStatus" id="LotStatus">
                                                    <option value="0">Pending</option>
                                                    <option value="1">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">

                                            <div class="form-group col-sm-3">

                                                <label for="exampleInputEmail1">Parameter</label>

                                                <select class="form-control select2" name="Parameter" id="Parameter"
                                                    required>
                                                    <option>Select</option>
                                                    <option value="AOZ">AOZ</option>
                                                    <option value="AMOZ">AMOZ</option>
                                                    <option value="AHD">AHD</option>
                                                    <option value="SEM">SEM</option>
                                                    <option value="CAP">CAP</option>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="KitBreakupID">Kit Lot No</label>
                                                <select class="form-control select2" name="KitBreakupID[]"
                                                    id="KitBreakupID" required disabled multiple>
                                                    <option value="">Select Lot</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="KitOpeningDate">Kit Opening Date</label>
                                                <input required type="Date" class="form-control" name="KitOpeningDate"
                                                    id="KitOpeningDate">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="OpeningWells">Opening Wells</label>
                                                <input required type="text" class="form-control" name="OpeningWells"
                                                    id="OpeningWells" placeholder="Enter Opening Wells" readonly>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="UsedWells">Used Wells</label>
                                                <input required type="number" class="form-control" name="UsedWells"
                                                    id="UsedWells" placeholder="Enter Used Wells" disabled>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="DamagedWells">Damaged Wells</label>
                                                <input type="number" class="form-control" name="DamagedWells" disabled
                                                    id="DamagedWells" placeholder="Enter Damaged Wells">
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="SPKBLKSTD">SPK/BLK/STD</label>
                                                <select class="form-control select2" name="SpkBlkStd" id="SpkBlkStd"
                                                    disabled>
                                                    <option value="0">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="BalanceWells">Balance Wells</label>
                                                <input required type="number" class="form-control" name="BalanceWells"
                                                    id="BalanceWells" placeholder="Enter Balance Wells" readonly>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="RESULT">Result</label>
                                                <input type="text" class="form-control" name="RESULT" id="RESULT"
                                                    placeholder="Enter Result">
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="ReceivedBy">Received By</label>
                                                <select class="form-control select2" name="ReceivedBy" id="ReceivedBy">
                                                    <?php while ($RowReceivedBy = $stmtReceivedBy->fetch_array()) { ?>
                                                        <option value="<?php echo $RowReceivedBy['LUID'] ?>">
                                                            <?php echo $RowReceivedBy['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="AnalyzedBy">Analyzed By</label>
                                                <select class="form-control select2" name="AnalyzedBy" id="AnalyzedBy">
                                                    <?php while ($RowAnalyzeddBy = $stmtAnalyzedBy->fetch_array()) { ?>
                                                        <option value="<?php echo $RowAnalyzeddBy['LUID'] ?>">
                                                            <?php echo $RowAnalyzeddBy['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="VerifiedBy">Verified By</label>
                                                <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
                                                    <?php while ($RowVerifiedBy = $stmtVerifiedBy->fetch_array()) { ?>
                                                        <option value="<?php echo $RowVerifiedBy['LUID'] ?>">
                                                            <?php echo $RowVerifiedBy['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>


                                        </div>


                                        <div class="col-xs-12 box-footer" align="right">

                                            <a target="" href="#" onclick="window.close();" class="btn btn-danger  ">
                                                Close</a>
                                            <button type="submit" name='submit' class="btn btn-success">Submit</button>
                                        </div>
                                    </div>
                                </form>

                                <!-- end col -->
                            </div> <!-- end row -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="ShowDataTable">
                                            <table id="key-datatable" data-paging="false"
                                                class="table dt-responsive nowrap w-100">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Sample </th>
                                                        <th>Po No</th>
                                                        <th>Kit Lot No</th>
                                                        <th>Used Wells</th>
                                                        <th>Damaged Wells</th>
                                                        <th>Parameter</th>
                                                        <th>Result</th>
                                                        <th>Testing Status</th>
                                                        <th>Shift</th>
                                                        <th>Standard </th>
                                                        <th>Tested By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    while ($row = $StmtAbReportData->fetch_array()) {

                                                        $ID = $row['euid'];
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $row['SampleDetails']; ?> </td>
                                                            <td><?php echo $row['Pono']; ?></td>
                                                            <td><?php echo $row['KitBreakupLotNo']; ?></td>
                                                            <td><?php echo $row['UsedWells']; ?></td>
                                                            <td><?php echo $row['DamagedWells']; ?></td>
                                                            <td><?php echo $row['Parameter']; ?></td>
                                                            <td><?php echo $row['Result']; ?></td>
                                                            <td><?php echo $row['Name']; ?></td>
                                                            <td><?php echo $row['Shift']; ?></td>
                                                            <td><?php echo $row['StandardUsedWells']; ?></td>
                                                            <td><?php echo $row['TestedBy']; ?></td>

                                                            <td><a href="euReportEdit.php?id=<?php echo $ID ?>&FromDate=<?php echo $FromDate ?>&ToDate=<?php echo $ToDate ?>&DOA=<?php echo $DOA ?>&DOR=<?php echo $DOR ?>"
                                                                    class="mr-3 text-primary" data-placement="top"
                                                                    title="Edit" data-original-title="Edit"><i
                                                                        class="mdi mdi-pencil font-size-16"></i>
                                                                </a>
                                                                <button class="btn btn-link text-danger  btnDelete"
                                                                    style="position: relative;top: -3px"
                                                                    data-id="<?php echo $ID ?>" data-toggle="tooltip"
                                                                    data-placement="top" title="Delete" value=""
                                                                    data-original-title="Delete">
                                                                    <i class="mdi mdi-trash-can font-size-16"></i></button>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <?php include($IncludesPath . "footer.php"); ?>
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->
        <?php include($IncludesPath . "vendor-scripts.php"); ?>
        <?php include($IncludesPath . "NewScripts.php"); ?>
        <?php include($IncludesPath . "ShowDataTable.php"); ?>
</body>

<Script>
    $('document').ready(function () {
        showData();

        $('#Pono').select2({
            placeholder: "Select Po No",

        });

        $("#KitBreakupID").select2({
            placeholder: "Select Kits Lot No",
        })

        $(document).on("click", ".btnDelete", function () {
            var selectedID = $(this).attr("data-id");
            var r = confirm("Are You Sure Do You Want To Delete?");
            if (r == true) {
                $.ajax({
                    url: 'EuReportCreate.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'euid': selectedID, // Pass LotIds as an array
                        'Type': 'Delete'
                    },
                    success: function (response) {
                        alert("Deleted successfully");
                        location.reload();
                    }

                });
            }
            else {

            }
        });

        $(document).on('change', '#Parameter', function (e) {
            e.preventDefault();
            $('#KitBreakupID').val('').trigger('change');
            var Parameter = $(this).val();
            var Type = 'GetKitbreakupLotNumbers';
            const KitBreakupLotDropdown = document.getElementById("KitBreakupID");

            // Disable the dropdown initially
            KitBreakupLotDropdown.disabled = true;
            $("#UsedWells, #DamagedWells, #SpkBlkStd").val("").prop("disabled", true);
            if (Parameter) {
                $.ajax({
                    url: 'Ajax/AjaxDependantDropdownValues.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'Parameter': Parameter,
                        'Type': Type
                    },
                    success: function (response) {
                        console.log("AJAX Response:", response);

                        if (response && response.length > 0) {
                            // Enable the dropdown
                            KitBreakupLotDropdown.disabled = false;

                            // Clear existing options
                            KitBreakupLotDropdown.innerHTML = '<option value="">Select</option>';

                            // Append new options dynamically
                            response.forEach(function (item) {
                                const option = document.createElement("option");
                                option.value = item.KitBreakupID; // Assuming 'id' is a property in the response
                                option.textContent = item.KitBreakupLotNo; // Assuming 'name' is a property in the response
                                KitBreakupLotDropdown.appendChild(option);
                            });
                        } else {
                            console.error("Response is empty or invalid.");
                            KitBreakupLotDropdown.innerHTML = '<option value="">No data available</option>';
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        KitBreakupLotDropdown.innerHTML = '<option value="">Error loading data</option>';
                    }
                });
            } else {
                // Clear and disable dropdown if no parameter is selected
                KitBreakupLotDropdown.innerHTML = '<option value="">--Select--</option>';
                KitBreakupLotDropdown.disabled = true;
            }
        });

        $(document).on('change', '#SHIFT', function (e) {
            e.preventDefault();

            $('#SpkBlkStd').val('0').trigger('change');

        });
        $(document).on('change', '#AnalysisDate', function (e) {
            e.preventDefault();

            $('#SpkBlkStd').val('0').trigger('change');

        });

        $(document).on('change', '#KitBreakupID', function (e) {
            e.preventDefault();

            // Reset values & disable fields
            $("#UsedWells, #DamagedWells, #SpkBlkStd").val("").prop("disabled", true);
            $('#SpkBlkStd').val('0').trigger('change');
            $("#OpeningWells, #BalanceWells, #KitOpeningDate").val("0");

            var KitBreakupIDs = $(this).val(); // Get selected KitBreakupID(s)
            var Type = 'GetKitDetails';

            if (KitBreakupIDs && KitBreakupIDs.length > 0) { // If selected, enable inputs
                $("#UsedWells, #DamagedWells, #SpkBlkStd").prop("disabled", false);

                $.ajax({
                    url: 'Ajax/AjaxDependantDropdownValues.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'KitBreakupIDs': KitBreakupIDs,
                        'Type': Type
                    },
                    success: function (response) {
                        console.log("AJAX Response:", response);

                        if (response && response.length > 0) {
                            $("#OpeningWells").val("0");
                            $("#BalanceWells").val("0");
                            $("#KitOpeningDate").val("");

                            response.forEach(function (item) {
                                console.log("Balance Wells:", item.BalanceWells);
                                console.log("Kit Opening Date:", item.KitOpeningDate);

                                $("#OpeningWells").val(function (i, oldValue) {
                                    return parseInt(oldValue) + parseInt(item.BalanceWells || 0);
                                });
                                $("#BalanceWells").val(function (i, oldValue) {
                                    return parseInt(oldValue) + parseInt(item.BalanceWells || 0);
                                });

                                if (!$("#KitOpeningDate").val()) {
                                    $("#KitOpeningDate").val(item.KitOpeningDate || "");
                                }
                            });
                        } else {
                            console.error("No data found for the selected KitBreakupIDs.");
                            $("#OpeningWells, #BalanceWells, #KitOpeningDate").val("0");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            } else {
                console.warn("No KitBreakupIDs selected.");
                $("#OpeningWells, #BalanceWells, #KitOpeningDate").val("0");
            }
        });



        $('#continuebtn').on('click', function () {
            $('form').submit();
        });
    });

    document.getElementById('UsedWells').addEventListener('input', function () {
        const openingWells = parseFloat(document.getElementById('OpeningWells').value) || 0;
        const usedWells = parseFloat(this.value) || 0;
        const damagedWells = parseInt(this.value, 10) || 0;

        if (usedWells > openingWells) {
            alert('Used wells shouldnot exceed opening wells');
            this.value = '';
        }
    });

    document.getElementById('DamagedWells').addEventListener('input', function () {
        const openingWells = parseFloat(document.getElementById('OpeningWells').value) || 0;
        const damagedWells = parseFloat(this.value) || 0;

        if (damagedWells > openingWells) {
            alert('Damaged wells should not exceed opening wells');
            this.value = '';
        }
    });


    $(document).on('change', '#SpkBlkStd', function (e) {
        calculateBalanceWells();
    });

    document.getElementById('UsedWells').addEventListener('input', calculateBalanceWells);
    document.getElementById('DamagedWells').addEventListener('input', calculateBalanceWells);
    $(document).on('change', '#SpkBlkStd', calculateBalanceWells);

    document.getElementById('UsedWells').addEventListener('input', calculateBalanceWells);
    document.getElementById('DamagedWells').addEventListener('input', calculateBalanceWells);
    $(document).on('change', '#SpkBlkStd', fetchStandardWells);

    let standardWells = 0; // Store fetched standardWells globally to prevent multiple additions

    function fetchStandardWells() {
        const SpkBlkStd = document.getElementById('SpkBlkStd').value;
        const AnalysisDate = document.getElementById('AnalysisDate').value;
        const Shift = document.getElementById('SHIFT').value;
        const Parameter = document.getElementById('Parameter').value;
        const Type = 'GetStandardWells';

        $.ajax({
            url: 'Ajax/AjaxDependantDropdownValues.php',
            type: 'POST',
            dataType: 'json',
            data: {
                'SpkBlkStd': SpkBlkStd,
                'AnalysisDate': AnalysisDate,
                'Shift': Shift,
                'Parameter': Parameter,
                'Type': Type
            },
            success: function (response) {
                console.log("AJAX Response:", response);
                standardWells = response && response.standardWells !== undefined ? parseFloat(response.standardWells) : 0;
                calculateBalanceWells(); // Recalculate balance after fetching standardWells
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    }

    function calculateBalanceWells() {
        const openingWells = parseFloat(document.getElementById('OpeningWells').value) || 0;
        const usedWells = parseFloat(document.getElementById('UsedWells').value) || 0;
        const damagedWells = parseFloat(document.getElementById('DamagedWells').value) || 0;

        const remainingBalance = openingWells - (usedWells + damagedWells + standardWells);

        if (remainingBalance < 0) {
            alert("Make sure entered wells do not exceed balance wells.");
            document.getElementById("DamagedWells").value = "";
            document.getElementById("UsedWells").value = "";
            document.getElementById("BalanceWells").value = openingWells;
            $('#SpkBlkStd').val('0').trigger('change'); // Reset dropdown to "No" and refresh it
            return;
        }

        document.getElementById('BalanceWells').value = remainingBalance;
    }

    // Initialize BalanceWells to OpeningWells value
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('BalanceWells').value = document.getElementById('OpeningWells').value;
    });


    function disableValidationFields() {
        var lotIds = $('#LotId').val();
        if (lotIds.indexOf('VALIDATION') != -1) {
            jQuery('#SAMPLE_TYPE').val('').attr('disabled', true);
            jQuery('#SAMPLE_DETAILS').val('').attr('disabled', true);
            jQuery('#COUNT').val('').attr('disabled', true);
            jQuery('#QUANTITY').val('').attr('disabled', true);
            jQuery('#AbStatus').val('').attr('disabled', true);
            jQuery('#ExternalLab').val('').trigger('change').attr('disabled', true);
            jQuery('#ExternalSampleSendingDate').val('').attr('disabled', true);
            jQuery('#ExternalIssuedResultDate').val('').attr('disabled', true);
            jQuery('#ExternalResult').val('').attr('disabled', true);
            jQuery('#REMARKS').val('').attr('disabled', true);
            jQuery('#LotStatus').val('').attr('disabled', true);
            jQuery('#FarmerName').val('').attr('disabled', true);
            jQuery('#FarmerAddress').val('').attr('disabled', true);
            jQuery('#GatePassNo').val('').attr('disabled', true);
            jQuery('#SamplerName').val('').attr('disabled', true);

        } else {
            jQuery('#SAMPLE_TYPE').val('').removeAttr('disabled', true);
            jQuery('#SAMPLE_DETAILS').val('').removeAttr('disabled', true);
            jQuery('#COUNT').val('').removeAttr('disabled', true);
            jQuery('#QUANTITY').val('').removeAttr('disabled', true);
            jQuery('#AbStatus').val('').removeAttr('disabled', true);
            jQuery('#ExternalLab').val('').trigger('change').removeAttr('disabled', true);
            jQuery('#ExternalSampleSendingDate').val('').removeAttr('disabled', true);
            jQuery('#ExternalIssuedResultDate').val('').removeAttr('disabled', true);
            jQuery('#ExternalResult').val('').removeAttr('disabled', true);
            jQuery('#REMARKS').val('').removeAttr('disabled', true);
            jQuery('#LotStatus').val('').removeAttr('disabled', true);
            jQuery('#GatePassNo').val('').removeAttr('disabled', true);
            jQuery('#SamplerName').val('').removeAttr('disabled', true);
        }
    }
</script>

</html>
