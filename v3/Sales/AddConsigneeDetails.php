<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");

include "Constants.php";

$PageTitleFolder="Master";

$PageTitle="Add Consignee";

extract($_POST);

extract($_GET);


$ConID = $_GET['ID'] ? $_GET['ID'] : '';

$PostedUserId = $_SESSION['UserID']?$_SESSION['UserID']:'';

$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

$RowConsigneeDtls = [];

$SqlConsigneeDtls="SELECT * FROM `ScrmConsigneeDetails` WHERE ConID='$ConID'";

$StmtConsigneeDtls=$iconn->query($SqlConsigneeDtls);

$RowConsigneeDtls=$StmtConsigneeDtls->fetch_array();



 $SqlCountryList="SELECT * FROM ScrmCountriesList";

    $StmtCountryList=$iconn->query($SqlCountryList);



if(isset($_POST['submit'])){

    $ConsigneeName         = $_POST['ConsigneeName'] ? trim($_POST['ConsigneeName']) : '';
    $ConsigneeAddress      = $_POST['ConsigneeAddress'] ? trim($_POST['ConsigneeAddress']) : '';
    $ConsigneeCountry      = $_POST['ConsigneeCountry'] ? trim($_POST['ConsigneeCountry']) : '';
    $ConsigneeSplIns       = $_POST['ConsigneeSplIns'] ? trim($_POST['ConsigneeSplIns']) : '';
    $ConsigneeFishingNo    = $_POST['ConsigneeFishingNo'] ? trim($_POST['ConsigneeFishingNo']) : '';
    $ConsigneeEmailAddress = $_POST['ConsigneeEmailAddress'] ? trim($_POST['ConsigneeEmailAddress']) : '';
    $ContactPerson         = $_POST['ContactPerson'] ? trim($_POST['ContactPerson']) : '';
    $ContactNumber         = $_POST['ContactNumber'] ? trim($_POST['ContactNumber']) : '';
    $PersonDesignation     = $_POST['PersonDesignation'] ? trim($_POST['PersonDesignation']) : '';
    $ConsigneeCode         = $_POST['ConsigneeCode'] ? trim($_POST['ConsigneeCode']) : '';
    $ConsigneeFaxNo        = $_POST['ConsigneeFaxNo'] ? trim($_POST['ConsigneeFaxNo']) : '';
    $ConsigneeIRSNumber    = $_POST['ConsigneeIRSNumber'] ? trim($_POST['ConsigneeIRSNumber']) : '';
    $ConsigneeUSCICODE     = $_POST['ConsigneeUSCICODE'] ? trim($_POST['ConsigneeUSCICODE']) : '';
    $ConsigneeIFTPCODE     = $_POST['ConsigneeIFTPCODE'] ? trim($_POST['ConsigneeIFTPCODE']) : '';
    $ConsigneeState        = $_POST['ConsigneeState'] ? trim($_POST['ConsigneeState']) : '';
    $ConsigneeZipCode      = $_POST['ConsigneeZipCode'] ? trim($_POST['ConsigneeZipCode']) : '';
    $Remarks               = $_POST['Remarks'] ? trim($_POST['Remarks']) : '';

    if (empty($ConID)) {

        $stmt = $iconn->prepare("
            INSERT INTO ScrmConsigneeDetails (
                ConsigneeName,
                ConsigneeAddress,
                ConsigneeCountry,
                ConsigneeSplIns,
                ConsigneeFishingNo,
                ConsigneeEmailAddress,
                ContactPerson,
                ContactNumber,
                PersonDesignation,
                ConsigneeCode,
                ConsigneeFaxNo,
                ConsigneeIRSNumber,
                ConsigneeUSCICODE,
                ConsigneeIFTPCODE,
                ConsigneeState,
                ConsigneeZipCode
            ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->bind_param(
            "ssssssssssssssss",
            $ConsigneeName,
            $ConsigneeAddress,
            $ConsigneeCountry,
            $ConsigneeSplIns,
            $ConsigneeFishingNo,
            $ConsigneeEmailAddress,
            $ContactPerson,
            $ContactNumber,
            $PersonDesignation,
            $ConsigneeCode,
            $ConsigneeFaxNo,
            $ConsigneeIRSNumber,
            $ConsigneeUSCICODE,
            $ConsigneeIFTPCODE,
            $ConsigneeState,
            $ConsigneeZipCode
        );

        if ($stmt->execute()) {
            legacyAuditLogInsertById('ScrmConsigneeDetails', 'ConID', $iconn->insert_id, $_POST, ['Remarks' => $Remarks], 'Sales');
            header("Location: DocConsigneeDetails.php");
            exit;
        } else {
            echo "Insert Error: " . $stmt->error;
            error_log("Insert Error: " . $stmt->error);
        }

        $stmt->close();

    } else {

        $oldRow = legacyAuditFetchRowById('ScrmConsigneeDetails', 'ConID', $ConID, 'Sales');

        /* UPDATE */
        $stmt = $iconn->prepare("
            UPDATE ScrmConsigneeDetails SET
                ConsigneeName = ?,
                ConsigneeAddress = ?,
                ConsigneeCountry = ?,
                ConsigneeSplIns = ?,
                ConsigneeFishingNo = ?,
                ConsigneeEmailAddress = ?,
                ContactPerson = ?,
                ContactNumber = ?,
                PersonDesignation = ?,
                ConsigneeCode = ?,
                ConsigneeFaxNo = ?,
                ConsigneeIRSNumber = ?,
                ConsigneeUSCICODE = ?,
                ConsigneeIFTPCODE = ?,
                ConsigneeState = ?,
                ConsigneeZipCode = ?
            WHERE ConID = ?
        ");

        $stmt->bind_param(
            "ssssssssssssssssi",
            $ConsigneeName,
            $ConsigneeAddress,
            $ConsigneeCountry,
            $ConsigneeSplIns,
            $ConsigneeFishingNo,
            $ConsigneeEmailAddress,
            $ContactPerson,
            $ContactNumber,
            $PersonDesignation,
            $ConsigneeCode,
            $ConsigneeFaxNo,
            $ConsigneeIRSNumber,
            $ConsigneeUSCICODE,
            $ConsigneeIFTPCODE,
            $ConsigneeState,
            $ConsigneeZipCode,
            $ConID
        );

        if ($stmt->execute()) {

            legacyAuditLogUpdateById('ScrmConsigneeDetails', 'ConID', $ConID, $oldRow, $_POST, ['Remarks' => $Remarks], 'Sales');

            $ArgumentsArray = [];
            $ArgumentsArray['PostedUserId'] = $PostedUserId;
            $ArgumentsArray['PageUrl'] = $PageUrl;
            $ArgumentsArray['Remarks'] = $Remarks;
            $ArgumentsArray['ReferenceID'] = $ConID;

            if (function_exists('postUserEditLog')) {
                postUserEditLog($ArgumentsArray);
            }

            header("Location: DocConsigneeDetails.php");
            exit;

        } else {
            echo "Update Error: " . $stmt->error;
            error_log("Update Error: " . $stmt->error);
        }

        $stmt->close();
    }
}

 ?>


<!DOCTYPE html>
<html lang="en">

    <head>

            <?php include ($IncludesPath."title-meta.php"); ?>
            <?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">


     <?php
            include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
            include ($IncludesPath."topbar.php");
            include ($IncludesPath."sidebar.php");
            ?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                         <?php
    if($Status !=''){
    //Show Alert
    ?>
     <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';   ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>




<div class="row">
                            <div class="col-12">
                                <div class="card">

                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">

             <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Consignee Name<span style="color:red">*</span></label>
                <input required type="text" class="form-control" name='ConsigneeName' id="ConsigneeName"
                  placeholder="Enter Consignee Name" value="<?php echo $RowConsigneeDtls['ConsigneeName'];?>">

                </div>

                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Consignee Code</label>
              <input   type="text" class="form-control" name='ConsigneeCode' id="ConsigneeCode"
                  placeholder=" Enter Consignee Code(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeCode'];?>">
                </div>



                <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Consignee Country<span style="color:red">*</span></label>


                  <select required class="form-control select2" name="ConsigneeCountry" id="ConsigneeCountry">
                  <option value="">Select Country</option>
                   <?php while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
                     <option <?php if($RowCountry['id']==$RowConsigneeDtls['ConsigneeCountry']){ ?> selected=="selected" <?php } ?>

                     value="<?php echo $RowCountry['id'] ?>">
                     <?php echo strtoupper($RowCountry['name'])?></option>

                 <?php  }?>
                  </select>
                </div>

                <div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Consignee Address<span style="color:red">*</span></label>

                  <textarea required name="ConsigneeAddress" class="form-control" rows="2" cols="50"
                    id="ConsigneeAddress"
                    placeholder="Enter Consignee Address" ><?php echo $RowConsigneeDtls['ConsigneeAddress'] ?></textarea>


                </div>





                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Email ID</label>
            <input   type="text" class="form-control" name='ConsigneeEmailAddress' id="ConsigneeEmailAddress"
                  placeholder=" Enter Email ID(Ex:ABC@gmail.com)" value="<?php echo $RowConsigneeDtls['ConsigneeEmailAddress'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Person</label>
              <input   type="text" class="form-control" name='ContactPerson' id="ContactPerson"
                  placeholder=" Enter Contact Person Name(Optional)" value="<?php echo $RowConsigneeDtls['ContactPerson'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Number</label>
                <input   type="text" class="form-control" name='ContactNumber' id="ContactNumber"
                  placeholder=" Enter Contact Number(Optional)" value="<?php echo $RowConsigneeDtls['ContactNumber'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Fax Number</label>
           <input   type="text" class="form-control" name='ConsigneeFaxNo' id="ConsigneeFaxNo"
                  placeholder=" Enter Fax Number(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeFaxNo'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">IRS Number</label>
             <input   type="text" class="form-control" name='ConsigneeIRSNumber' id="ConsigneeIRSNumber"
                  placeholder=" Enter IRS Number(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeIRSNumber'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">USCI Code</label>
          <input   type="text" class="form-control" name='ConsigneeUSCICODE' id="ConsigneeUSCICODE"
                  placeholder=" Enter USCI Code(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeUSCICODE'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">IFTP Code</label>
<input   type="text" class="form-control" name='ConsigneeIFTPCODE' id="ConsigneeIFTPCODE"
                  placeholder=" Enter IFTP Code(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeIFTPCODE'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">State(For USA)</label>
<input   type="text" class="form-control" name='ConsigneeState' id="ConsigneeState"
                  placeholder=" Enter State(For USA)(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeState'];?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Zipcode(For USA)</label>
<input   type="text" class="form-control" name='ConsigneeZipCode' id="ConsigneeZipCode"
                  placeholder=" Enter Zipcode(For USA)(Optional)" value="<?php echo $RowConsigneeDtls['ConsigneeZipCode'];?>">
                </div>




                </div>


            <div class="col-xs-12 box-footer"  align="right">
            <a target="" href="DocConsigneeDetails.php" class="btn btn-danger"> Close</a>
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
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

                 <?php include ($IncludesPath."footer.php");?>

            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->



        <?php include ($IncludesPath."vendor-scripts.php");?>
        <?php include ($IncludesPath."NewScripts.php");?>

  
       
    </body>
