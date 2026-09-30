<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
// include("Includes/Globals.php");


$PageTitleFolder = "Master";
$PageTitle = "Brand Details";


$BrandID = $_GET['id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));



$SqlConsignee = "SELECT ConID,ConsigneeName FROM `ScrmConsigneeDetails` WHERE Status=1";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlLiner = "SELECT * FROM `ScrmBrandsMaster` WHERE BrandID='$BrandID'";
$StmtLiner = $iconn->query($SqlLiner);
$RowLiner = $StmtLiner->fetch_array();
$Count = mysqli_num_rows($StmtLiner);

extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {

    $SqlBrandName = "SELECT * FROM `ScrmBrandsMaster` WHERE BrandName='$BrandName'";
    $StmtBrandName = $iconn->query($SqlBrandName);
    $BrandCount = mysqli_num_rows($StmtBrandName);


    if ($Count == 0) {
        if ($BrandCount == 0) {



            $SqlInsert = "
	 INSERT INTO `ScrmBrandsMaster`(`BuyerID`,`BrandName`, `Remarks`,`BrandType`, `PrintStatus`,`Status`) VALUES 
	 ('$BuyerID','$BrandName','$Remarks','$BrandType','$PrintStatus','1')";


            $StmtInsert = $iconn->query($SqlInsert);

            if ($StmtInsert) {
                $insertedBrandId = $iconn->insert_id;
                legacyAuditLogInsertById('ScrmBrandsMaster', 'BrandID', $insertedBrandId, $_POST, array(
                    'ReferenceNo' => $insertedBrandId,
                    'Remarks' => 'Brand created',
                ), 'Sales');

                echo '<script language="javascript" type="text/javascript"> 
                    alert("Brand Posted Succesfully");
                    window.location = "BrandsList";
		        </script>';
                // header('Location: BrandsList');
            }
        } else {
            echo '<script>alert("Brand name already exists")</script>';
        }
    } else {
        $existingBrand = legacyAuditFetchRowById('ScrmBrandsMaster', 'BrandID', $BrandID, 'Sales');
        $SqlInsert = "UPDATE `ScrmBrandsMaster` SET `BuyerID`='$BuyerID',`BrandName`='$BrandName',`Remarks`='$Remarks',`BrandType`='$BrandType',`PrintStatus`='$PrintStatus' WHERE BrandID='$BrandID'";


        $stmt1 = $iconn->query($SqlInsert);

        // $errorInfo = $stmt1->errorInfo();
        // var_dump($errorInfo);

        if ($stmt1) {
            legacyAuditLogUpdateById('ScrmBrandsMaster', 'BrandID', $BrandID, $existingBrand, $_POST, array(
                'ReferenceNo' => $BrandID,
                'Remarks' => 'Brand updated',
            ), 'Sales');

            $ArgumentsArray[PostedUserId] = $PostedUserId;
            $ArgumentsArray[PageUrl] = $PageUrl;
            $ArgumentsArray[Remarks] = $Remarks;
            // $ArgumentsArray[PrintStatus] = $PrintStatus;
            $ArgumentsArray[ReferenceID] = $BrandID;
            postUserEditLog($ArgumentsArray);

            echo '<script language="javascript" type="text/javascript"> 
                alert("Brand Updated Succesfully");
                window.location = "BrandsList";
		</script>';
        }
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




                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Buyer</label>
                                                <select class="form-control select2" name="BuyerID" id="BuyerID">
                                                    <option value="">Select Buyer</option>
                                                    <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                        <option <?php if ($RowLiner['BuyerID'] == $RowConsignee['ConID']) { ?> selected="selected" <?php  } ?> value="<?php echo $RowConsignee['ConID'] ?>">
                                                            <?php echo $RowConsignee['ConsigneeName'] ?></option>

                                                    <?php  } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Brand Name<span style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='BrandName' id="BrandName" placeholder="Enter Brand Name" value="<?php echo $RowLiner['BrandName'] ?>">

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Remarks</label>
                                                <input type="text" class="form-control" name='Remarks' id="Remarks" placeholder=" Enter Remarks" value="<?php echo $RowLiner['Remarks'] ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Brand Type<span style="color:red">*</span></label>
                                                <select class="form-control select2" name="BrandType" id="BrandType" value="<?php echo $RowLiner['BrandType'] ?>" required>
                                                    <option value="0" <?php echo ($RowLiner['BrandType'] == '0') ? 'selected' : ''; ?>>Both</option>
                                                    <option value="1" <?php echo ($RowLiner['BrandType'] == '1') ? 'selected' : ''; ?>>Sales</option>
                                                    <option value="2" <?php echo ($RowLiner['BrandType'] == '2') ? 'selected' : ''; ?>>Store</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Print Status<span style="color:red">*</span></label>
                                                <select class="form-control select2" name="PrintStatus" id="PrintStatus" value="<?php echo $RowLiner['PrintStatus'] ?>" required>
                                                    <option value="1" <?php echo ($RowLiner['PrintStatus'] == '1') ? 'selected' : ''; ?>>Print</option>
                                                    <option value="0" <?php echo ($RowLiner['PrintStatus'] == '0') ? 'selected' : ''; ?>>Hide</option>
                                                </select>
                                            </div>




                                        </div>



                                        <div class="col-xs-12 box-footer" align="right">
                                            <a target="" href="BrandsList.php" class="btn btn-danger  "> Close</a>
                                            <?php if ($Count == 0) { ?>

                                                <button type="submit" name='submit' class="btn btn-success">ADD</button>
                                            <?php    } else { ?>

                                                <button type="submit" name='submit' class="btn btn-success">Update</button>
                                            <?php    } ?>

                                        </div>
                                    </div>







                                </form>
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

</html>
