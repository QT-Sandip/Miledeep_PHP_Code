<?php


session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

extract($_POST);
extract($_GET);

$PageTitle = "DAILY ETP OUTLET WATER ANALYSIS REPORT";


$PageTitleFolder = "Quality Control";


$ID = $_GET['ID'];

include "Constants.php";


$sqlOutletWater = "SELECT * FROM `QcOutletWaterAnalysis` where ID='$ID'";
$stmtOutletWater = $iconn->query($sqlOutletWater);
$RowOutletWater = $stmtOutletWater->fetch_array();

$sql_LAB_USERS = "SELECT * FROM `LAB_USERS` where stats='1' ";
$stmt_LAB_USERS = $iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1 = $iconn->query($sql_LAB_USERS);


if (isset($_POST['submit'])) {
    $ID = $_GET['id'];

    $RID = $RowOutletWater['ID'];
    if ($RID == 0) {
        $sql_org_insert = "INSERT INTO `QcOutletWaterAnalysis`(`SampleDate`, 
	`SampleTime`, `AnalysedBy`, `VerifiedBy`, `PHValue`, `Temperature`, 
	`Colour`, `Salinity`, `TDS`, `Hardness`, `TSS`, `Chlorine`, `COD`,
	`BOD_Inlet`, `BOD_Outlet`, `Remarks`, `CorrectiveAction`)
	VALUES ('$SampleDate','$SampleTime','$AnalysedBy',
	'$VerifiedBy','$PHValue',
	'$Temperature','$COLOUR','$Salinity','$TDS',
	'$Hardness','$TSS','$Chlorine','$COD','$BOD_Inlet','$BOD_Outlet',
	'$Remarks','$CorrectiveAction')";
    } else {
        $oldRow = legacyAuditFetchRowById('QcOutletWaterAnalysis', 'ID', $RID, 'QualityControl');
        $sql_org_insert = "
 UPDATE `QcOutletWaterAnalysis` SET
 `SampleDate`='$SampleDate',
 `SampleTime`='$SampleTime',
 `AnalysedBy`='$AnalysedBy',`VerifiedBy`='$VerifiedBy',
 `PHValue`='$PHValue',`Temperature`='$Temperature',
 `Colour`='$COLOUR',`Salinity`='$Salinity',
 `TDS`='$TDS',`Hardness`='$Hardness',`TSS`='$TSS',
 `Chlorine`='$Chlorine',`COD`='$COD',
 `BOD_Inlet`='$BOD_Inlet',`BOD_Outlet`='$BOD_Outlet',
 `Remarks`='$Remarks',`CorrectiveAction`='$CorrectiveAction' WHERE ID='$RID'";
    }
    $sql_org_insert;

    $stmt1 = $iconn->query($sql_org_insert);
    $iid = $iconn->insert_id;
    //echo $iid;
    if ($stmt1) {
        if ($RID == 0) {
            legacyAuditLogInsertById('QcOutletWaterAnalysis', 'ID', $iid, $_POST, ['Remarks' => 'Outlet Water Analysis Added'], 'QualityControl');
        } else {
            legacyAuditLogUpdateById('QcOutletWaterAnalysis', 'ID', $RID, $oldRow, $_POST, ['Remarks' => 'Outlet Water Analysis Updated'], 'QualityControl');
        }

        header('Location: CommonSearchList.php?id=2');
    }
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

                    <?php
                    if ($Status != '') {
                        //Show Alert 
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php echo $Status;
                            $_SESSION['Status'] = '';      ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>




                    <!-- Main content -->



                    <!--<div class="row">
                            <div class="col-12">-->
                    <form role="form" method="post" enctype="multipart/form-data">

                        <div class="card">

                            <div class="card-body">

                                <div class="row">


                                    <div class="form-group col-sm-3">
                                        <label for="exampleInputPassword1">Sample collection Time</label>
                                        <input required type="time" class="form-control" name="SampleTime" id="SampleTime" placeholder="Enter Sample collection Time" value="<?php echo $RowOutletWater['SampleTime'] ?>">
                                    </div>



                                    <div class="form-group col-sm-3">
                                        <label for="exampleInputEmail1">Date of Sampling</label>
                                        <input required type="date" value="<?php echo $RowOutletWater['SampleDate'] ?>" class="form-control" name='SampleDate' id="SampleDate" placeholder="Enter Date of Sampling ">

                                    </div>


                                    <div class="form-group col-sm-3">
                                        <label for="exampleInputEmail1">Analysed By</label>
                                        <select required type="text" class="form-control select2" name='AnalysedBy' id="AnalysedBy">

                                            <?php while ($LAB_USERS_details = $stmt_LAB_USERS->fetch_array()) { ?>
                                                <option <?php if ($RowOutletWater['AnalysedBy'] == $LAB_USERS_details['USER_NAME']) { ?> selected="selected" <?php } ?> value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
                                                    <?php echo $LAB_USERS_details['USER_NAME'] ?></option>

                                            <?php  } ?>
                                        </select>

                                    </div>



                                    <div class="form-group col-sm-3">
                                        <label for="exampleInputEmail1">Verified By </label>
                                        <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
                                            <?php while ($LAB_USERS_details = $stmt_LAB_USERS1->fetch_array()) { ?>
                                                <option <?php if ($RowOutletWater['VerifiedBy'] == $LAB_USERS_details['USER_NAME']) { ?> selected="selected" <?php } ?> value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
                                                    <?php echo $LAB_USERS_details['USER_NAME'] ?></option>

                                            <?php  } ?>
                                        </select>


                                    </div>

                                    <div class="form-group col-sm-3">
                                        <label for="exampleInputPassword1">Remarks</label>
                                        <input required type="text" class="form-control" name="Remarks" id="Remarks" placeholder="Enter Remarks" value="<?php echo $RowOutletWater['Remarks'] ?>">
                                    </div>

                                    <div class="form-group col-sm-3">
                                        <label for="exampleInputPassword1">Corrective action</label>
                                        <input required type="text" class="form-control" name="CorrectiveAction" id="CorrectiveAction" placeholder="Enter Corrective action" value="<?php echo $RowOutletWater['CorrectiveAction'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 style="color:green">Parameter Values</h3>

                        <div class="card">
                            <div class="card-body">
                                <div class="row">


                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputEmail1">PH Value</label>
                                        <input required type="text" class="form-control" name='PHValue' id="PHValue" placeholder="Enter PH VALUE" value="<?php echo $RowOutletWater['PHValue'] ?>">


                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputEmail1">Colour</label>
                                        <input required type="text" class="form-control" name='COLOUR' id="COLOUR" placeholder="Enter Colour" value="<?php echo $RowOutletWater['Colour'] ?>">


                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">Temperature</label>
                                        <input required type="text" class="form-control" name='Temperature' id="Temperature" placeholder="Enter Temperature" value="<?php echo $RowOutletWater['Temperature'] ?>">
                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">Salinity</label>
                                        <input required type="text" class="form-control" name='Salinity' id="Salinity" placeholder="Enter Salinity" value="<?php echo $RowOutletWater['Salinity'] ?>">
                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">TDS</label>
                                        <input required type="text" class="form-control" name='TDS' id="TDS" placeholder="Enter Total Dissolved Solids" value="<?php echo $RowOutletWater['TDS'] ?>">
                                    </div>



                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">Hardness</label>
                                        <input required type="text" class="form-control" name='Hardness' id="Hardness" placeholder="Enter HARDNESS" value="<?php echo $RowOutletWater['Hardness'] ?>">
                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">TSS</label>
                                        <input required type="text" class="form-control" name='TSS' id="TSS" placeholder="Enter TOTAL SUSPENDED SOLIDS" value="<?php echo $RowOutletWater['TSS'] ?>">
                                    </div>


                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">Chlorine</label>
                                        <input required type="text" class="form-control" name='Chlorine' id="Chlorine" placeholder="Enter CHLORINE" value="<?php echo $RowOutletWater['Chlorine'] ?>">
                                    </div>
                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">COD</label>
                                        <input required type="text" class="form-control" name='COD' id="COD" placeholder="Enter CHEMICAL OXYGEN DEMAND" value="<?php echo $RowOutletWater['COD'] ?>">
                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">5 Days-BOD(inlet)</label>
                                        <input type="text" class="form-control" name='BOD_Inlet' id="BOD_Inlet" placeholder="Enter BIOLOGICAL OXYGEN DEMAND" value="<?php echo $RowOutletWater['BOD_Inlet'] ?>">
                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="exampleInputPassword1">5 Days-BOD(Outlet)</label>
                                        <input type="text" class="form-control" name='BOD_Outlet' id="BOD_Outlet" placeholder="Enter BIOLOGICAL OXYGEN DEMAND" value="<?php echo $RowOutletWater['BOD_Outlet'] ?>">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 card-footer" align="right">
                            <a href="" class="btn btn-danger">Close</a>
                            <button type="submit" name='submit' class="btn btn-success">Submit</button>
                        </div>
                    </form>

                </div> <!-- end row -->

            </div> <!-- end card body-->
        </div> <!-- end card -->


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

</html>
