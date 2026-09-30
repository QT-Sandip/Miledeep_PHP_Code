<?php

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Create Inner Page";
$PageTitleFolder="Admin";
$AccessType=$_SESSION['AccessType'];
$PageID=$_GET['PageID'];

$SqlMenuList="SELECT * FROM `ClientSideBarHeaders` ORDER BY Header";  
$StmtMenuList=$iconn->query($SqlMenuList);

$RowPageDetails = array();
if(isset($PageID)){
$SqlPageList="SELECT CSIP.*, CSBO.HeaderId FROM `InnerPagesForRoleManagement` CSIP
    LEFT JOIN ClientSideBarOptions CSBO ON CSIP.SubMenuID = CSBO.ID
    WHERE CSIP.ID='$PageID' ";  
$StmtPageList=$iconn->query($SqlPageList);
$RowPageDetails=$StmtPageList->fetch_assoc();
}
 
extract($_POST);
extract($_GET);

 
if(isset($_POST['submit'])){

$isPageInsert = !isset($PageID);
if($isPageInsert){
     $SqlClientInsert="INSERT INTO `InnerPagesForRoleManagement`(`SubMenuID`, `PageTitle`,
     `Directory`, `FileName`)
     VALUES ('$SubMenuID','$PageTitle','$Directory','$FileName')";
}else{
    $oldPageData = legacyAuditFetchRowById('InnerPagesForRoleManagement', 'ID', $PageID, 'SAdmin');
    $SqlClientInsert="UPDATE `InnerPagesForRoleManagement`
    SET
    SubMenuID='$SubMenuID',
    PageTitle='$PageTitle',
    Directory='$Directory',
    FileName='$FileName'
    WHERE ID='$PageID'";
}


    $StmtClientInsert=$iconn->query($SqlClientInsert);
    $JobID= $iconn->insert_id;


    if($StmtClientInsert){
        if($isPageInsert){
            legacyAuditLogInsertById('InnerPagesForRoleManagement', 'ID', $JobID, $_POST, [
                'ReferenceNo' => $JobID,
                'Remarks' => 'Inner page created',
            ], 'SAdmin');
        }else{
            legacyAuditLogUpdateById('InnerPagesForRoleManagement', 'ID', $PageID, $oldPageData, $_POST, [
                'ReferenceNo' => $PageID,
                'Remarks' => 'Inner page updated',
            ], 'SAdmin');
        }
        header('Location:InnerPageList');


    }
else{
        echo "<script type='text/javascript'>alert('Failed to save')</script>";

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


     <?php          include ($IncludesPath."PreLoader.php"); ?>

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

                    <div class="form-group col-sm-3" >
                  <label for="MenuID">Menu</label>
                  <select class="form-control select2" name="MenuID" id="MenuID">
                    <option value="">-- Select Menu --</option>
        <?php while($RowMenuList=$StmtMenuList->fetch_array()){?>
        <option <?php if($RowPageDetails['HeaderId']==$RowMenuList['Sid']){ ?> selected
                                                        <?php } ?> value="<?php echo $RowMenuList['Sid'] ;?>" >
        <?php  echo $RowMenuList['Header']; ?></option>
                                    <?php    } ?>
                                   </select>
                </div>

                <div class="form-group col-sm-3" >
                  <label for="SubMenuID">Sub Menu</label>
                  <select class="form-control select2" name="SubMenuID" id="SubMenuID" required>
                    <option value="">-- Select Sub Menu --</option>
        <?php if(isset($PageID) && isset($RowPageDetails['SubMenuID'])){
            $SelHeaderId = $RowPageDetails['HeaderId'];
            $SqlSubs="SELECT ID, Name FROM `ClientSideBarOptions` WHERE HeaderId='$SelHeaderId' ORDER BY Name";
            $StmtSubs=$iconn->query($SqlSubs);
            while($RowSub=$StmtSubs->fetch_array()){
        ?>
        <option <?php if($RowPageDetails['SubMenuID']==$RowSub['ID']){ ?> selected
        <?php } ?> value="<?php echo $RowSub['ID']; ?>"><?php echo $RowSub['Name']; ?></option>
        <?php } } ?>
                                   </select>
                </div>

                <div class="form-group col-sm-3" >
                  <label for="PageTitle">Page Title</label>
                  <input required type="text" class="form-control" name='PageTitle' id="PageTitle"
                  placeholder="Page Title" value="<?php echo $RowPageDetails['PageTitle']?>"  >
                </div>

                <div class="form-group col-sm-3" >
                  <label for="Directory">Directory</label>
                  <input required type="text" class="form-control" name='Directory' id="Directory"
                  placeholder="Directory" value="<?php echo $RowPageDetails['Directory']?>"  >
                </div>

                <div class="form-group col-sm-3" >
                  <label for="FileName">File Name</label>
                  <input required type="text" class="form-control" name='FileName' id="FileName"
                  placeholder="File Name" value="<?php echo $RowPageDetails['FileName']?>"  >
                </div>

                </div>



              <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Save</button>
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

    <script>
    $(document).ready(function(){
        $(".select2").select2();

        var selectedSubMenuID = "<?php echo $RowPageDetails['SubMenuID']; ?>";

        $('#MenuID').on('change', function(){
            var HeaderID = $(this).val();
            $('#SubMenuID').html('<option value="">-- Select Sub Menu --</option>');
            if(HeaderID){
                $('#SubMenuID').next('.select2-container').remove();
                $.ajax({
                    url: 'Ajax/AjaxGetSubMenus.php',
                    type: 'POST',
                    data: {HeaderID: HeaderID},
                    success: function(data){
                        $('#SubMenuID').append(data);
                        if(selectedSubMenuID){
                            $('#SubMenuID').val(selectedSubMenuID);
                        }
                        $('#SubMenuID').select2();
                    }
                });
            }
        });

        <?php if(isset($PageID) && isset($RowPageDetails['HeaderId'])){ ?>
        $('#MenuID').val('<?php echo $RowPageDetails['HeaderId']; ?>').trigger('change');
        <?php } ?>
    });
    </script>

    </body>
</html>

