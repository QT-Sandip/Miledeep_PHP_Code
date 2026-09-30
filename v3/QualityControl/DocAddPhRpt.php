<?php
session_start();
$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$ClientID = $_SESSION["ClientID"];
$ClientConfigData = $_SESSION['ClientConfigData'];
// $IsShowQcAddOnFields = $ClientConfigData->IsShowQcAddOnFields;  // uncomment this line after auth-login.php converted to API modal
$IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


$PoID = $_GET['PoID'];

$SqlCodeList = "
 SELECT DISTINCT(Tracebility),Grades,McsGrade,TypeCountry,PoNo,CLID FROM `QcCodeList` as cl left JOIN `QcTracebilityList` as tl 
ON cl.TracebilityID=tl.TraceID LEFT JOIN
 ScrmPoData as pd on pd.PoID=cl.InvID where cl.InvID='$PoID'";
$StmtCodeList = $iconn->query($SqlCodeList);
$RowPo = $StmtCodeList->fetch_array();


$StmtCodeList = $iconn->query($SqlCodeList);

$sql_LAB_USERS = "SELECT * FROM `QcLabTechnologist` WHERE LbuStatus=1";
$StmtLabUsers = $iconn->query($sql_LAB_USERS);
$StmtLabUsers1 = $iconn->query($sql_LAB_USERS);


$PageTitle = "IN-HOUSE ELISA PRE-HARVEST TEST REPORT  - " . $RowPo['PoNo'];
$PageTitleFolder = "Quality Control";





extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {

    $ReportNo = '' . $LabSampleID;
    $Source = 'AQUACULTURE';
    //$QUANTITY_IN_KGS = $FinishedWeight/$Yield; 
    $sqlVerifiedBy = "SELECT LbuName FROM `QcLabTechnologist` WHERE LUID='$VerifiedBy'";
    $stmtVerifiedBy = $iconn->query($sqlVerifiedBy);
    $RowVerifiedBy = $stmtVerifiedBy->fetch_array();
    $VerifiedByName = $RowVerifiedBy['LbuName'];

    $sqlTestedBy = "SELECT LbuName FROM `QcLabTechnologist` WHERE LUID='$TestedBy'";
    $stmtTestedBy = $iconn->query($sqlTestedBy);
    $RowTestedBy = $stmtTestedBy->fetch_array();
    $TestedByName = $RowTestedBy['LbuName'];

    $SqlInsert = "INSERT INTO `QcPhAbReports`(`ReportNo`, `ReportDate`, `TracebilityID`, `LabRep`, `SamplingDate`, 
	`SamplingTime`, `MiddleMan`, `SampleID`, `AnalysisDate`, `ReceivedDate`, `DateIssuence`, `TestedByID`,`TestedBy`,`VerifiedByID`, 
	`VerifiedBy`,`InvID`, `FinishedWeight`, `Yield`, `Source`, `HarvestingDate`,`ReceivedQty`,`SamplingQty`,`SamplingFinishedQty`,
	`SamplingPreharvestQty`,`SampleQuantity`,`RecordNo`) VALUES ('$ReportNo','$ReportDate',
	'$TracebilityID','$LabRep',
	'$SamplingDate','$SamplingTime','$MiddleMan',
	'$LabSampleID','$AnalysisDate','$ReceivedDate','$DateIssuence','$TestedBy','$TestedByName','$VerifiedBy','$VerifiedByName','$PoID',
	'$FinishedWeight','$Yield' ,'$Source','$HarvestingDate','$ReceivedQty','$SamplingQty','$SamplingFinishedQty',
    '$SamplingPreharvestQty', '$SampleWeight', '$RecordNo')";



    $stmt1 = $iconn->query($SqlInsert);
    $iid = $iconn->insert_id;
    if ($stmt1) {
        legacyAuditLogInsertById('QcPhAbReports', 'ID', $iid, $_POST, ['Remarks' => 'PH Report Created'], 'QualityControl');
    }

    header('Location: DocAddReports.php?Q=' . $QueryParm);
}

?>



<!DOCTYPE html>

<html lang="en">

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

                    <?php include($IncludesPath . "page-title.php"); ?>



                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Traceability</label>

                                                <select class="form-control select2" name="TracebilityID"
                                                    id="TracebilityID">
                                                    <?php while ($RowCodeList = $StmtCodeList->fetch_array()) {
                                                        $Grades = $RowCodeList['Grades'];
                                                        $McsGrade = $RowCodeList['McsGrade']; ?>
                                                        <option value="<?php echo $RowCodeList['CLID'] ?>">
                                                            <?php echo $RowCodeList['Tracebility'] . '-' . $Grades . '-' . $McsGrade . ' Mcs'; ?>
                                                        </option>
                                                    <?php } ?>
                                                    <select>
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1"> Report Date</label>
                                                <input required type="date" class="form-control"
                                                    value="<?php echo date("Y-m-d"); ?>" name="ReportDate"
                                                    id="ReportDate">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Date of Sample Collection</label>
                                                <input required type="date" value="<?php echo date("Y-m-d"); ?>"
                                                    class="form-control" name="SamplingDate" id="SamplingDate"
                                                    placeholder="dd/mm/yyyy;hh:mm"
                                                    value="<?php echo $RowPo['DO_SAMPLING'] ?>">
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Time of Sample Collection</label>
                                                <input required type="time" class="form-control" name="SamplingTime"
                                                    id="SamplingTime" placeholder="hh:mm"
                                                    value="<?php echo $RowPo['SamplingTime'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Quantity of Raw Material</label>
                                                <input type="text" class="form-control" name="SamplingQty"
                                                    id="SamplingQty" placeholder="Enter Quantity of Raw Material"
                                                    value="<?php echo $RowPo['SamplingQty'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Quantity of Finished Product</label>
                                                <input type="text" class="form-control" name="SamplingFinishedQty"
                                                    id="SamplingFinishedQty"
                                                    placeholder="Enter Quantity of Finished Product"
                                                    value="<?php echo $RowPo['SamplingFinishedQty'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Quantity of Pre-Harvest</label>
                                                <input type="text" class="form-control" name="SamplingPreharvestQty"
                                                    id="SamplingPreharvestQty"
                                                    placeholder="Enter Quantity of Pre-Harvest"
                                                    value="<?php echo $RowPo['SamplingPreharvestQty'] ?>">
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Sample id/code</label>
                                                <input required type="text" class="form-control" name='LabSampleID'
                                                    id="LabSampleID" placeholder="Enter Sample id/code"
                                                    value="<?php echo $RowPo['LabSampleID'] ?>">

                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Sample Quantity</label>
                                                <input type="text" class="form-control" name='SampleWeight'
                                                    id="SampleWeight" placeholder="Enter  Sample Quantity"
                                                    value="<?php echo $ReportDetails['SampleQuantity'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1"> Received Quantity</label>
                                                <input type="text" class="form-control" name="ReceivedQty"
                                                    id="ReceivedQty" placeholder="Enter Received Quantity in Kgs "
                                                    value="">
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1"> Finished Quantity</label>
                                                <input type="text" class="form-control" name="FinishedWeight"
                                                    id="FinishedWeight" placeholder="Enter Finished Quantity in Kgs "
                                                    value="">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Date & Time Of Harvest </label>
                                                <input type="datetime-local" class="form-control"
                                                    value="<?php echo date("Y-m-d"); ?>" name='HarvestingDate'
                                                    id="HarvestingDate" placeholder="Enter Date and time Of Harvest">

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1"> Received Date </label>
                                                <input required type="date" value="<?php echo date("Y-m-d"); ?>"
                                                    class="form-control" name="ReceivedDate" id="ReceivedDate"
                                                    placeholder="Enter Received Date "
                                                    value="<?php echo $RowPo['AnalysisDate'] ?>">
                                            </div>




                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Date Of Analysis</label>
                                                <input required type="date" value="<?php echo date("Y-m-d"); ?>"
                                                    class="form-control" name="AnalysisDate" id="AnalysisDate"
                                                    placeholder="Enter Date Of Analysis "
                                                    value="<?php echo $RowPo['AnalysisDate'] ?>">
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Date Of Issuance </label>
                                                <input required type="date" value="<?php echo date("Y-m-d"); ?>"
                                                    class="form-control" name='DateIssuence' id="DateIssuence"
                                                    placeholder="Enter Date Of Issuance"
                                                    value="<?php echo $RowPo['DateIssuence'] ?>">


                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Name of the Representation/Technologist
                                                </label>
                                                <input required type="text" class="form-control" name="LabRep"
                                                    id="LabRep" placeholder="Tested - Designation"
                                                    value="(LAB REPRESENTATIVE)">

                                            </div>


                                            <!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Yield </label>
                  <input type="float" class="form-control" name='Yield' 
                  id="Yield" placeholder="Enter Yield(%)" value="<?php echo $RowPo['Yield'] ?>"     required>
                </div>-->


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Tested By</label>
                                                <select required type="text" class="form-control select2"
                                                    name='TestedBy' id="TestedBy">
                                                    <?php while ($RowLabUsers = $StmtLabUsers->fetch_array()) { ?>
                                                        <option value="<?php echo $RowLabUsers['LUID'] ?>">
                                                            <?php echo $RowLabUsers['LbuName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Verified By</label>
                                                <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
                                                    <?php while ($RowLabUsers = $StmtLabUsers1->fetch_array()) { ?>
                                                        <option value="<?php echo $RowLabUsers['LUID'] ?>">
                                                            <?php echo $RowLabUsers['LbuName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <?php if ($IsShowQcAddOnFields == 1) { ?>
                                            <hr>
                                            <div class="row">
                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputPassword1">Record No</label>
                                                    <input type="text" class="form-control" name="RecordNo" id="RecordNo"
                                                        placeholder="Enter Record Number">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="col-xs-12 box-footer" align="right">
                                            <a href="DocAddReports.php?Q=<?php echo $QueryParm ?>"
                                                class="btn btn-danger">Close</a>

                                            <button type="submit" name="submit"
                                                class="btn btn-success waves-effect waves-light">

                                                Submit</button>
                                        </div>
                                </form>

                            </div>
                        </div>

                        <!-- end col -->
                    </div> <!-- end row -->

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



</body>
