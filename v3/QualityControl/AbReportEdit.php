<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$DisabledABReportFields = $ClientConfigData['DisabledABReportFields'];
$defaultSelect = '';
if(!empty($DisabledABReportFields)){
    $defaultSelect = "<option value=''>Select</option>";
}

$PageTitleFolder = "Quality Control";
$PageTitle = "Daily Lab AB Report";
extract($_POST);
extract($_GET);

$DOA = $_GET['DOA'];
$DOR = $_GET['DOR'];
$id = $_GET['id'];

$FromDate = $_GET['FromDate'];
$ToDate = $_GET['ToDate'];
$IsDisable = false;

$SqlLabName = "SELECT * FROM `QcEiaLabNames` WHERE Status=1";
$StmtLabName = $iconn->query($SqlLabName);


$sql_kit_lot_numbers = "SELECT KitID, KitBreakupID, KitBreakupLotNo FROM QcKitsBreakupDetails  
 ORDER BY KitBreakupID ASC";
$stmt_kit_lot_numbers = $iconn->query($sql_kit_lot_numbers);


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


$SqlAbReportData = "SELECT *,
QRAB.Remarks as AbRemarks 
  FROM `QcRmAbReport` as QRAB  
  left join RmLotDetatils as RLD on RLD.RmLotID=QRAB.LotID 
   left join QcABOptions as QAO on QAO.OptID=QRAB.AbStatus where  AbRepID='$id'";

$StmtAbReportData = $iconn->query($SqlAbReportData);

$ROW_CODE_LIST = $StmtAbReportData->fetch_array();
$disabledExtFields = $disabledIntFields = '';
if(!empty($DisabledABReportFields)){
    if(!empty($ROW_CODE_LIST['ExternalLab'])){
        $disabledExtFields = '';
        $disabledIntFields = 'disabled';
    }else{
        $disabledIntFields = '';
        $disabledExtFields = 'disabled';
    }
}

$SqlDropDownOptions = "SELECT * FROM `QcABOptions`  ORDER BY SortOrder ASC   ";
$StmtDropDownOptions = $iconn->query($SqlDropDownOptions);
$ArrayDropDownOptions = array();
while ($RowDropDownOptions = $StmtDropDownOptions->FETCH_ARRAY()) {
    $ArrayDropDownOptions[] = $RowDropDownOptions;
}


if (isset($_POST['submit'])) {
    $SelectedParameter = $ROW_CODE_LIST['Parameter'];
    if(empty($ExternalLab)){
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
    }else{
        if ($SelectedParameter == 'AOZ') {
            $AOZ = $ExternalResult;
        } else if ($SelectedParameter == 'SEM') {
            $SEM = $ExternalResult;
        } else if ($SelectedParameter == 'CAP') {
            $CAP = $ExternalResult;
        } else if ($SelectedParameter == 'AHD') {
            $AHD = $ExternalResult;
        } else if ($SelectedParameter == 'AMOZ') {
            $AMOZ = $ExternalResult;
        }
    }

    $SqlUpdateData = "UPDATE `QcRmAbReport` SET 
	`AnalysisDate`='$ANALYSIS_DATE',`ReportDate`='$REPORT_DATE',`SampleDetails`='$SAMPLE_TYPE',
	`Result`='$RESULT',`AOZ`='$AOZ',`SEM`='$SEM',`CAP`='$CAP',`AHD`='$AHD',`AMOZ`='$AMOZ',
	`Sulphite`='$SULPHITE',`Remarks`='$Remarks',`Shift`='$SHIFT',
	 `TestedBy`='$TESTED_BY',`AbStatus`='$AbStatus',`PostedDate`='$PostedDate',`UserID`='$UserID',`GatePassNo`='$GatePassNo',
     `SamplerName`='$SamplerName',
	 `ReceivedBy`='$ReceivedBy',`AnalyzedBy`='$AnalyzedBy',`VerifiedBy`='$VerifiedBy',
	 `ExternalSampleSendingDate`='$ExternalSampleSendingDate',`ExternalIssuedResultDate`='$ExternalIssuedResultDate',
	 `ExternalResult`='$ExternalResult',`ExternalLab`='$ExternalLab',`SpkBlkStd`='$SpkBlkStd',`LotStatus`='$LotStatus' WHERE AbRepID=$id";

    $StmtUpdateData = $iconn->query($SqlUpdateData);

    if ($StmtUpdateData) {

        echo "<script>
       alert('Data Updated successfully );
    </script>";
        header('Location: AbReportCreate.php?DOA=' . $DOA . '&DOR=' . $DOR . '&FromDate=' . $FromDate . '&ToDate=' . $ToDate);

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


    <?php include($IncludesPath . "PreLoader.php"); ?>

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

                    <?php
                    if ($Status != '') {
                        //Show Alert 
                        ?>

                    <?php } ?>

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


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Analysis Date</label>
                                                <input required type="date" class="form-control" name='ANALYSIS_DATE'
                                                    id="ANALYSIS_DATE"
                                                    value="<?php echo date("Y-m-d", strtotime($ROW_CODE_LIST['AnalysisDate'])); ?>">
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Report Date</label>
                                                <input required type="date" class="form-control" name='REPORT_DATE'
                                                    id="REPORT_DATE"
                                                    value="<?php echo date("Y-m-d", strtotime($ROW_CODE_LIST['ReportDate'])); ?>" >
                                            </div>

                                            <div class="form-group col-sm-3">

                                                <label for="exampleInputPassword1">Sample Type</label>
                                                <select class="form-control select2" name="SAMPLE_TYPE"
                                                    id="SAMPLE_TYPE" >
                                                    <option value="Farm Sample" <?php if ($ROW_CODE_LIST['SampleDetails'] == "Farm Sample")
                                                        echo 'selected="selected"'; ?>>Farm Sample</option>
                                                    <option value="Raw Material Sample" <?php if ($ROW_CODE_LIST['SampleDetails'] == "Raw Material Sample")
                                                        echo 'selected="selected"'; ?>>Raw Material Sample</option>
                                                    <option value="Grading Sample" <?php if ($ROW_CODE_LIST['SampleDetails'] == "Grading Sample")
                                                        echo 'selected="selected"'; ?>>Grading Sample</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> Testing Status</label>
                                                <select class="form-control select2" name="AbStatus" id="AbStatus" >
                                                    <option Value=""> Select </option>
                                                    <?php foreach ($ArrayDropDownOptions as $DropDownOptions) {
                                                        if ($DropDownOptions['Category'] == 'TestStatus') {
                                                            $Options = $DropDownOptions['Name'];
                                                            $Value = $DropDownOptions['OptID']; ?>

                                                            <option <?php if ($Value == $ROW_CODE_LIST['AbStatus']) { ?>
                                                                    selected=="selected" <?php } ?> value="<?php echo $Value ?>">
                                                                <?php echo $Options ?>
                                                            </option>
                                                        <?php }
                                                    } ?>
                                                </select>

                                            </div>

                                            <div class="form-group col-sm-3">

                                                <label for="exampleInputPassword1">Shift</label>

                                                <select class="form-control select2" name="SHIFT" id="SHIFT" >
                                                   <option value="DAY" <?php if ($ROW_CODE_LIST['Shift'] == "DAY")
                                                        echo 'selected="selected"'; ?>>DAY</option>
                                                    <option value="NIGHT" <?php if ($ROW_CODE_LIST['Shift'] == "NIGHT")
                                                        echo 'selected="selected"'; ?>>NIGHT</option>


                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Tested By</label>
                                                <select class="form-control select2" name="TESTED_BY" id="TESTED_BY" >
                                                   <?php
                                                    $L_NAME = $ROW_CODE_LIST['TestedBy'];

                                                    while ($LAB_USERS_details = $stmt_LAB_USERS->fetch_array()) { ?>
                                                        <option value="<?php echo $LAB_USERS_details['LbuName']; ?>" <?php if ($LAB_USERS_details['LbuName'] == $L_NAME)
                                                               echo 'selected="selected"'; ?>>
                                                            <?php echo $LAB_USERS_details['LbuName']; ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="ExternalLab">External Lab Name</label>
                                                <select class="form-control select2" name="ExternalLab" id="ExternalLab"
                                                    value="<?php echo $RowEiaLabs['ExternalLab'] ?>">
                                                    <option value="">Select </option>
                                                    <?php
                                                    $L_NAME = $ROW_CODE_LIST['ExternalLab'];

                                                    while ($RowEiaLabs = $StmtLabName->fetch_array()) { ?>
                                                        <option value="<?php echo $RowEiaLabs['LabId']; ?>" <?php if (($RowEiaLabs['LabId'] == $L_NAME))
                                                               echo 'selected="selected"'; ?>>
                                                            <?php echo $RowEiaLabs['LabName'] . ' - ' . $RowEiaLabs['Address']; ?>
                                                        </option>
                                                    <?php } ?>
                                                    <select>
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="ExternalSampleSendingDate">External Sample Sending
                                                    Date</label>
                                                <input type="date" class="form-control" name='ExternalSampleSendingDate'
                                                    id="ExternalSampleSendingDate"
                                                    value="<?php echo $ROW_CODE_LIST['ExternalSampleSendingDate']; ?>" >
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="ExternalIssuedResultDate">External Issued Result
                                                    Date</label>
                                                <input type="date" class="form-control" name='ExternalIssuedResultDate'
                                                    id="ExternalIssuedResultDate"
                                                    value="<?php echo $ROW_CODE_LIST['ExternalIssuedResultDate']; ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="ExternalResult">External Result</label>
                                                <input type="text" class="form-control" name='ExternalResult'
                                                    id="ExternalResult"
                                                    value="<?php echo $ROW_CODE_LIST['ExternalResult'] ?>"
                                                    placeholder="Enter External Result">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="SpkBlkStd">SPK/BLK/STD</label>
                                                <select class="form-control select2" name="SpkBlkStd" id="SpkBlkStd"
                                                    disabled>
                                                    <option <?php if (($ROW_CODE_LIST['SpkBlkStd'] == 0))
                                                        echo 'selected="selected"'; ?> value="0">No</option>
                                                    <option <?php if (($ROW_CODE_LIST['SpkBlkStd'] == 1))
                                                        echo 'selected="selected"'; ?> value="1">Yes</option>
                                                </select>
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="Remarks">Remarks</label>
                                                <input type="text" class="form-control" name='Remarks' id="Remarks"
                                                    placeholder="Enter Remarks"
                                                    value="<?php echo $ROW_CODE_LIST['AbRemarks'] ?>" >
                                            </div>

                                            <div class="form-group col-sm-3"> 
                                                <label for="LotStatus">Lot Status</label>
                                                <select class="form-control select2" name="LotStatus" id="LotStatus">
                                                    <option value="0">Pending</option>
                                                    <option value="1">Completed</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-8">
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">

                                            <div class="form-group col-sm-3">
                                                <label for="GatePassNo">Gate Pass No</label>
                                                <input type="text" class="form-control" name="GatePassNo"
                                                    id="GatePassNo" value="<?php echo $ROW_CODE_LIST['GatePassNo'] ?>"
                                                    placeholder="Enter Gate Pass Number" <?php echo $disabledIntFields   ;?>>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="SamplerName">Sampler Name</label>
                                                <input type="text" class="form-control" name="SamplerName"
                                                    id="SamplerName" value="<?php echo $ROW_CODE_LIST['SamplerName'] ?>"
                                                    placeholder="Enter Sampler Name" <?php echo $disabledIntFields   ;?>>
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Result</label>
                                                <input type="text" class="form-control" name="RESULT" id="RESULT"
                                                    value="<?php echo $ROW_CODE_LIST['Result'] ?>"
                                                    placeholder="Enter Result">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Received By</label>
                                                <select class="form-control select2" name="ReceivedBy" id="ReceivedBy" <?php echo $disabledIntFields   ;?>>
                                                    <?php echo $defaultSelect;?>
                                                    <?php
                                                    $L_NAME = $ROW_CODE_LIST['ReceivedBy']; // Fetch the previously selected value
                                                    while ($RowReceivedBy = $stmtReceivedBy->fetch_array()) {
                                                        // Check if the option matches the previously selected value
                                                        $selected = ($RowReceivedBy['LUID'] == $L_NAME) ? 'selected="selected"' : '';
                                                        ?>
                                                        <option value="<?php echo $RowReceivedBy['LUID']; ?>" <?php echo $selected; ?>>
                                                            <?php echo $RowReceivedBy['LbuName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="AnalyzedBy">Analyzed By</label>
                                                <select class="form-control select2" name="AnalyzedBy" id="AnalyzedBy" <?php echo $disabledIntFields   ;?>>
                                                    <?php echo $defaultSelect;?>
                                                    <?php
                                                    $analyzedByValue = $ROW_CODE_LIST['AnalyzedBy']; // Fetch the previously selected value
                                                    while ($RowAnalyzedBy = $stmtAnalyzedBy->fetch_array()) {
                                                        // Check if the current option matches the previously selected value
                                                        $selected = ($RowAnalyzedBy['LUID'] == $analyzedByValue) ? 'selected="selected"' : '';
                                                        ?>
                                                        <option value="<?php echo $RowAnalyzedBy['LUID']; ?>" <?php echo $selected; ?>>
                                                            <?php echo $RowAnalyzedBy['LbuName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="VerifiedBy">Verified By</label>
                                                <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
                                                    <?php
                                                    $verifiedByValue = $ROW_CODE_LIST['VerifiedBy']; // Fetch the previously selected value
                                                    while ($RowVerifiedBy = $stmtVerifiedBy->fetch_array()) {
                                                        // Check if the current option matches the previously selected value
                                                        $selected = ($RowVerifiedBy['LUID'] == $verifiedByValue) ? 'selected="selected"' : '';
                                                        ?>
                                                        <option value="<?php echo $RowVerifiedBy['LUID']; ?>" <?php echo $selected; ?>>
                                                            <?php echo $RowVerifiedBy['LbuName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-sm-12 box-footer" align="right">

                                                <a target=""
                                                    href="AbReportCreate.php?FromDate=<?php echo $FromDate; ?>&ToDate=<?php echo $ToDate; ?>&DOA=<?php echo $DOA; ?>&DOR=<?php echo $DOR; ?>"
                                                    class="btn btn-danger  "> Close</a>
                                                <button type="submit" name='submit'
                                                    class="btn btn-success">Update</button>
                                            </div>


                                        </div>
                                </form>

                                <!-- end col -->
                            </div> <!-- end row -->
                        </div> <!-- end row -->

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

        $('#LotId').select2({
            placeholder: "Select Lot No",
            // allowClear: true             
        });

        $("#KitBreakupID").select2({
            placeholder: "Select Kits Lot No",
        })

        $(document).on('change', '#SAMPLE_TYPE', function (e) {
            e.preventDefault();
            var SAMPLE_TYPE = $(this).val();
            //alert(SAMPLE_TYPE);

            if (SAMPLE_TYPE == 'Harvest') {

                $.ajax({
                    url: 'Ajax/AjaxLotsList.php',
                    type: 'POST',
                    data: { 'MONTH': 06, 'YEAR': 2020 },
                    success: function (data) {
                        console.log(data);
                        $("#LOT_NUMBER").html(data);
                        $('#myModal').modal('toggle');
                        $('#ATT_DATA').html(data);
                        $('#example1').DataTable();
                    }
                })
            } else {
                $('#SAMPLE_DETAILS').val('');
                $('#COUNT').val('');
                $('#QUANTITY').val('');
                $('#LOT_ID').val('');
            }


        })



        $('#continuebtn').on('click', function () {

            $('form').submit();

        });

    });


<?php  
    if(!empty($DisabledABReportFields)){
        $AnalysisDate = !empty($ROW_CODE_LIST['AnalysisDate']) ? date("Y-m-d", strtotime($ROW_CODE_LIST['AnalysisDate'])) : '';
        $ReportDate = !empty($ROW_CODE_LIST['ReportDate']) ? date("Y-m-d", strtotime($ROW_CODE_LIST['ReportDate'])) : '';
        $SampleDetails = !empty($ROW_CODE_LIST['SampleDetails']) ? $ROW_CODE_LIST['SampleDetails'] : '';
        $AbStatus = !empty($ROW_CODE_LIST['AbStatus']) ? $ROW_CODE_LIST['AbStatus'] : '';
        $Shift = !empty($ROW_CODE_LIST['Shift']) ? $ROW_CODE_LIST['Shift'] : '';
        $TestedBy = !empty($ROW_CODE_LIST['TestedBy']) ? $ROW_CODE_LIST['TestedBy'] : '';
        $ExternalLab = !empty($ROW_CODE_LIST['ExternalLab']) ? $ROW_CODE_LIST['ExternalLab'] : '';
        $ExternalSampleSendingDate = !empty($ROW_CODE_LIST['ExternalSampleSendingDate']) ? date("Y-m-d", strtotime($ROW_CODE_LIST['ExternalSampleSendingDate'])) : '';
        $ExternalIssuedResultDate = !empty($ROW_CODE_LIST['ExternalIssuedResultDate']) ? date("Y-m-d", strtotime($ROW_CODE_LIST['ExternalIssuedResultDate'])) : '';
        $ExternalResult = !empty($ROW_CODE_LIST['ExternalResult']) ? $ROW_CODE_LIST['ExternalResult'] : '';
        $SpkBlkStd = !empty($ROW_CODE_LIST['SpkBlkStd']) ? $ROW_CODE_LIST['SpkBlkStd'] : '';
        $AbRemarks = !empty($ROW_CODE_LIST['AbRemarks']) ? $ROW_CODE_LIST['AbRemarks'] : '';
        $LotStatus = !empty($ROW_CODE_LIST['LotStatus']) ? $ROW_CODE_LIST['LotStatus'] : '';
        $GatePassNo = !empty($ROW_CODE_LIST['GatePassNo']) ? $ROW_CODE_LIST['GatePassNo'] : '';
        $SamplerName = !empty($ROW_CODE_LIST['SamplerName']) ? $ROW_CODE_LIST['SamplerName'] : '';
        $Result = !empty($ROW_CODE_LIST['Result']) ? $ROW_CODE_LIST['Result'] : '';
        $ReceivedBy = !empty($ROW_CODE_LIST['ReceivedBy']) ? $ROW_CODE_LIST['ReceivedBy'] : '';
        $AnalyzedBy = !empty($ROW_CODE_LIST['AnalyzedBy']) ? $ROW_CODE_LIST['AnalyzedBy'] : '';
        $VerifiedBy = !empty($ROW_CODE_LIST['VerifiedBy']) ? $ROW_CODE_LIST['VerifiedBy'] : '';        
        ?>
        
        $(document).on('change', '#ExternalLab', function (e) {
            e.preventDefault();
            if($(this).val()){
                $('#GatePassNo, #SamplerName, #KitBreakupID, #KitOpeningDate, #OpeningWells, #UsedWells, #DamagedWells, #BalanceWells, #ReceivedBy, #AnalyzedBy').prop("disabled", true).val('')
                 /*  $("form")
                    .find("input, select, textarea")
                    .not("#ExternalLab, #LotId, #ExternalSampleSendingDate, #ExternalIssuedResultDate, #RESULT, #REMARKS, #Parameter, #VerifiedBy")
                    .prop("disabled", true).val(''); */
                   // $('#SAMPLE_TYPE, #SHIFT, #TESTED_BY, #ReceivedBy, #AnalyzedBy').val("").trigger('change.select2')
                $('#SpkBlkStd, #ReceivedBy, #AnalyzedBy').each(function () {
                    $(this).prop('selectedIndex', 0).trigger('change.select2');
                });

                $.each({
                     '#VerifiedBy': "<?php echo $VerifiedBy;?>"
                }, function (id, value) {
                    $(id).val(value).trigger('change.select2');
                });

            }else{                
                $('#GatePassNo, #SamplerName, #KitBreakupID, #KitOpeningDate, #OpeningWells, #UsedWells, #DamagedWells, #BalanceWells, #ReceivedBy, #AnalyzedBy').prop("disabled", false).val('')
                 /* $("form")
                    .find("input, select, textarea")
                    .not("#SpkBlkStd,#ExternalLab, #LotId, #REMARKS, #Parameter, #VerifiedBy")
                    .prop("disabled", false).val('');  */
                $('#ExternalSampleSendingDate, #ExternalIssuedResultDate').prop("disabled", true).val(''); 
                 $.each({
                    
                    '#GatePassNo': "<?php echo $GatePassNo;?>",
                    '#SamplerName': "<?php echo $SamplerName;?>",
                    '#RESULT': "<?php echo $Result;?>",
                }, function (id, value) {
                    $(id).val(value);
                });
                $.each({
                    '#ReceivedBy': "<?php echo $ReceivedBy;?>",
                    '#AnalyzedBy': "<?php echo $AnalyzedBy;?>",
                }, function (id, value) {
                    $(id).val(value).trigger('change.select2');
                });
            }
        })
    <?php } ?>
</script>


</html>