<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

if(!empty($_POST['method']) && $_POST['method'] == 'delete' && !empty($_POST['id']) ){
    $existingGstRow = legacyAuditFetchRowById('ScrmPreparationMasterGstPerc', 'ID', $_POST['id'], 'Sales');
    $deleteSql = "DELETE FROM ScrmPreparationMasterGstPerc WHERE ID=".$_POST['id'];
    $StmtDelete = $iconn->query($deleteSql);
    if(!empty($StmtDelete)){
        legacyAuditLogDeleteById('ScrmPreparationMasterGstPerc', 'ID', $_POST['id'], $existingGstRow, array(
            'method' => 'delete',
            'id' => $_POST['id'],
        ), array(
            'ReferenceNo' => $_POST['id'],
            'Remarks' => 'Preparation GST slab deleted',
        ), 'Sales');
        echo 1;die;
    }else{
        echo 0;die;
    }
}

$PageTitleFolder = "Master";
$PageTitle = "Add Preparation";


$PreparationID = $_GET['id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

$SqlFreezingType = "SELECT * FROM `ScrmFreezingTypeMaster` ORDER BY SortOrder asc";
$StmtFreezingType = $iconn->query($SqlFreezingType);

$SqlSpecies = "SELECT SpeciesName,ShortCode FROM `ScrmSpeciesMaster` WHERE Status=1;";
$StmtSpecies = $iconn->query($SqlSpecies);


$SqlPreparation = "SELECT * FROM `ScrmPreparationMaster` WHERE ID='$PreparationID'";
$StmtPreparation = $iconn->query($SqlPreparation);
$RowPreparation = $StmtPreparation->fetch_array();
$Count = mysqli_num_rows($StmtPreparation);
if(!empty($PreparationID)){
    $SqlPreparationBreakUp = "SELECT * FROM `ScrmPreparationMasterGstPerc` WHERE ParentId='$PreparationID' ORDER BY ID DESC";
    $StmtPreparationBreakUp = $iconn->query($SqlPreparationBreakUp);
}

extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {

    $SqlShortName = "SELECT * FROM `ScrmPreparationMaster` WHERE PreparationName='$PreparationName'";
    $StmtShortName = $iconn->query($SqlShortName);
    $ShortCount = mysqli_num_rows($StmtShortName);


    if ($Count == 0) {
        if ($ShortCount == 0) {



            $SqlInsert = "INSERT INTO `ScrmPreparationMaster`(`PreparationName`, `PreparationDesc`,`SpeciesShortCode`, `ShortCode`,`ItemType`,
            `FType`,`HSCode`, `QcPreparationShortCode`) VALUES ('$PreparationName','$PreparationDesc','$Species','$ShortCode',
            '$TypeProduct','$FreezingType','$HSCode','$QcPreparationShortCode')";

            $StmtInsert = $iconn->query($SqlInsert);
            if ($StmtInsert) {
                $lastId = $iconn->insert_id;
                legacyAuditLogInsertById('ScrmPreparationMaster', 'ID', $lastId, $_POST, array(
                    'ReferenceNo' => $lastId,
                    'Remarks' => 'Preparation created',
                ), 'Sales');
                if(!empty($lastId) && isset($GstPerc) && !empty($FromDate) && $TypeProduct != 'RAW'){
                    $breakUpId = addGstPerc($iconn, $lastId, $GstPerc, $FromDate, $PostedUserId);
                    if(!empty($breakUpId)){
                        echo '<script>alert("Preparation Added Successfully.")</script>';
                        header('Location: AddPreparation.php?id='.$lastId);
                        die;
                    }else{
                         echo '<script>alert("Failed To Add  entage.")</script>';
                    }
                }else{
                     header('Location: PreparationList');
                }
            }
        } else {
            echo '<script>alert("Preparation name already exists")</script>';
        }
    } else {
        $existingPreparation = legacyAuditFetchRowById('ScrmPreparationMaster', 'ID', $PreparationID, 'Sales');
        $SqlInsert = "UPDATE `ScrmPreparationMaster` SET `PreparationName`='$PreparationName',`PreparationDesc`='$PreparationDesc',
    `SpeciesShortCode`='$Species',`ShortCode`='$ShortCode',`ItemType`='$TypeProduct',`FType`='$FreezingType',
    `HSCode`='$HSCode', `QcPreparationShortCode`='$QcPreparationShortCode' 
    WHERE `ID`='$PreparationID'";

        $StmtInsert = $iconn->query($SqlInsert);

        if ($StmtInsert) {
            legacyAuditLogUpdateById('ScrmPreparationMaster', 'ID', $PreparationID, $existingPreparation, $_POST, array(
                'ReferenceNo' => $PreparationID,
                'Remarks' => 'Preparation updated',
            ), 'Sales');
            $ArgumentsArray[PostedUserId] = $PostedUserId;
            $ArgumentsArray[PageUrl] = $PageUrl;
            $ArgumentsArray[Remarks] = $Remarks;
            $ArgumentsArray[ReferenceID] = $PreparationID;
            postUserEditLog($ArgumentsArray);
            if(!empty($PreparationID) && isset($GstPerc) && !empty($FromDate) && $TypeProduct != 'RAW'){
                $breakUpId = addGstPerc($iconn, $PreparationID,$GstPerc, $FromDate, $PostedUserId);
                if(!empty($breakUpId)){
                    echo '<script>alert("Preparation Updated Successfully.")</script>';
                    header('Location: AddPreparation.php?id='.$PreparationID);
                }else{
                    echo '<script>alert("Failed To Add  entage.")</script>';
                }
            }else{
                header('Location: PreparationList');
            }
        }
    }
    
}
function addGstPerc($iconn, $ParentId, $GstPerc, $FromDate, $PostedUserId = ''){
    if(!empty($ParentId) && isset($GstPerc) && !empty($FromDate)){
        $currentDate = date('Y-m-d H:i:s');
        $SqlInsert = "INSERT INTO `ScrmPreparationMasterGstPerc`
                        (`ParentId`, `GstPerc`,`FromDate`, `CreatedOn`,`CreatedBy`)
                        VALUES 
                        ('$ParentId','$GstPerc','$FromDate','$currentDate','$PostedUserId')";
        $StmtInsert = $iconn->query($SqlInsert);
        $lastId = $iconn->insert_id;
        if(!empty($lastId)){
            legacyAuditLogInsertById('ScrmPreparationMasterGstPerc', 'ID', $lastId, array(
                'ParentId' => $ParentId,
                'GstPerc' => $GstPerc,
                'FromDate' => $FromDate,
            ), array(
                'ReferenceNo' => $ParentId,
                'Remarks' => 'Preparation GST slab created',
            ), 'Sales');
            return $lastId;
        }else{
            return false;
        }
    }
    return false;
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
                            $_SESSION['Status'] = ''; ?>
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
                                                <label for="exampleInputEmail1">Freezing Type<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="FreezingType" id="FreezingType">
                                                    <option value=""> Select </option>
                                                    <?php while ($RowFreezingType = $StmtFreezingType->fetch_array()) { ?>
                                                        <option <?php if ($RowFreezingType['ShortCode'] == $RowPreparation['FType']) { ?> selected=="selected" <?PHP } ?> Value="<?php echo $RowFreezingType['ShortCode']; ?>">
                                                            <?php echo $RowFreezingType['FreezingTypeName']; ?>
                                                        </OPTION>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Type of Product<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="TypeProduct" id="TypeProduct">
                                                    <option value=""> Select</option>
                                                    <option <?php if ($RowPreparation['ItemType'] == 'RAW') { ?> selected="selected" <?php } ?> value="RAW">RAW</option>
                                                    <option <?php if ($RowPreparation['ItemType'] == 'COOKED') { ?> selected="selected" <?php } ?> value="COOKED">COOKED</option>
                                                    <option <?php if ($RowPreparation['ItemType'] == 'BLANCHED') { ?> selected="selected" <?php } ?> value="BLANCHED">BLANCHED
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Species<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="Species" id="Species">
                                                    <option value=""> Select </option>
                                                    <?php while ($RowSpecies = $StmtSpecies->fetch_array()) { ?>
                                                        <option <?php if ($RowSpecies['ShortCode'] == $RowPreparation['SpeciesShortCode']) { ?> selected="selected" <?PHP } ?> Value="<?php echo $RowSpecies['ShortCode']; ?>">
                                                            <?php echo $RowSpecies['SpeciesName']; ?>
                                                        </OPTION>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Preparation Name<span style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='PreparationName' id="PreparationName" placeholder="Enter Preparation Name" value="<?php echo $RowPreparation['PreparationName'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Short Code<span style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='ShortCode' id="ShortCode" placeholder=" Enter Short Code" value="<?php echo $RowPreparation['ShortCode'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">HS Code<span style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='HSCode' id="HSCode" placeholder=" Enter HS Code" value="<?php echo $RowPreparation['HSCode'] ?>">
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Qc Preparation ShortCode</label>
                                                <input type="text" class="form-control" name='QcPreparationShortCode' id="QcPreparationShortCode" placeholder=" Enter Qc Preparation ShortCode" value="<?php echo $RowPreparation['QcPreparationShortCode'] ?>">
                                            </div>

                                            <div class="form-group col-lg-6">
                                                <label for="exampleInputEmail1">Preparation Description<span style="color:red">*</span></label>
                                                <textarea name="PreparationDesc" class="form-control" rows="2" cols="50" required id="PreparationDesc" placeholder="Enter Preparation Description"><?php echo $RowPreparation['PreparationDesc'] ?></textarea>
                                            </div>

                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Gst Perc <span style="color:red"></span></label>
                                                <input type="number" <?php echo !empty($RowPreparation['ItemType']) && $RowPreparation['ItemType'] == 'RAW' ? 'readonly':'';?> class="form-control" name='GstPerc' id="GstPerc" placeholder="Enter GST Percentage" value="<?php //echo $RowPreparation['GstPerc'] ?>">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="FromDate">From Date<span style="color:red"></span></label>
                                                <input type="date" class="form-control" name='FromDate'  <?php echo !empty($RowPreparation['ItemType']) && $RowPreparation['ItemType'] == 'RAW' ? 'readonly':'';?> id="FromDate" placeholder=" Enter FromDate" value="<?php //echo $RowPreparation['EndDate'] ?>">                    
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-xs-12 box-footer" align="right">
                                            <a target="" href="PreparationList.php" class="btn btn-danger  "> Close</a>
                                            <?php if ($Count == 0) { ?>
                                                <button type="submit" name='submit' class="btn btn-success">ADD</button>
                                            <?php } else { ?>
                                                <button type="submit" name='submit' class="btn btn-success">Update</button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>
                                <!-- end col -->
                            </div> <!-- end row -->
                            <?php 
                            if(!empty($PreparationID)){?>
                                <table id="key-datatable" data-paging="false"  data-searching="false"  class="table dt-responsive nowrap w-100">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>SNo</th>
                                        <th>GST %</th>
                                        <th>From Date</th>
                                        <th>Created On</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>                                    
                                    <tbody>
                                        <?php 
                                        $i = 0;
                                        while($dataRow = $StmtPreparationBreakUp->fetch_assoc()){?>
                                            <tr>
                                                <td><?php echo ++$i;?></td>
                                                <td><?php echo $dataRow['GstPerc'];?></td>
                                                <td><?php echo $dataRow['FromDate'];?></td>
                                                <td><?php echo $dataRow['CreatedOn'];?></td>
                                                <td>
                                                    <a href="javascript:void(0);" class="delete mr-3 text-danger" data-id="<?php echo $dataRow['ID'];?>">
                                                        <i class="mdi mdi-delete font-size-18"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php }?>
                                    </tbody>
                            <?php }?>
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
    <script>
         $(document).on('change', '#TypeProduct', function() {
            if($(this).val() == 'RAW'){
                $('#GstPerc,#FromDate').prop('readonly',true)
            }else{
                $('#GstPerc,#FromDate').prop('readonly',false)
            }
         })
        $(document).on('click', '.delete', function() {
            if(confirm('Are you sure to Delete This?')){
                $.ajax({
                    url: 'AddPreparation.php', // 'Ajax/AjaxBuyerDetails.php',
                    type: 'POST',
                    data: {
                        'id': $(this).data('id'),
                        'method': 'delete',
                    },
                    success: function (res) {
                        if(res == 1){
                            alert('Deleted Successfully');
                            window.location.reload();
                        }else{
                            alert('Failed to Delete.');
                        }
                    }
                })
            }
        })
    </script>
</body>

</html>
