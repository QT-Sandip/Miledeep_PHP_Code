<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Master";
$PageTitle="Admin Users and Privileges";
$SideBarOptionTbl=$_SESSION["SideBarOptionTbl"];
$SideBarHeaderTbl=$_SESSION["SideBarHeaderTbl"];
//$UserID=$_SESSION["UserID"];

 $SqlHeaders="SELECT * FROM `$SideBarHeaderTbl` where Status=1 order by SortOrder asc"; 
$StmtHeaders=$iconn->query($SqlHeaders);

$SqlCount="SELECT * FROM `AdminUsersandPrivileges` where UserID='$UserID'"; 
$StmtCount=$iconn->query($SqlCount);
$Count=mysqli_num_rows($StmtCount);



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	
$OptionIds=implode(',',$OptionId);

 $SqlOptions="SELECT distinct HeaderId FROM `$SideBarOptionTbl` where Status=1 and ID in ($OptionIds)";
$StmtOptions=$iconn->query($SqlOptions);
while($RowOptions=$StmtOptions->fetch_array()){
	$HeaderId[]=$RowOptions['HeaderId'];
	$HeaderIds=implode(',',$HeaderId);
}
if($Count==0){
	 $sql_org_insert="
	 INSERT INTO `AdminUsersandPrivileges`( `UserID`, `HeaderID`, `OptionID`) VALUES ('$UserID','$HeaderIds','$OptionIds')";
	

	$stmt1=$iconn->query($sql_org_insert);
	
	if($stmt1){
		legacyAuditLogInsertById('AdminUsersandPrivileges', 'UserID', $UserID, $_POST, [
			'ReferenceNo' => $UserID,
			'Remarks' => 'Admin user privileges created',
		], 'Sales');
		header('Location: AddAdminUsersandPrivileges.php');
	}
}

else{
	$oldAdminPrivData = legacyAuditFetchRowById('AdminUsersandPrivileges', 'UserID', $UserID, 'Sales');
	$sql_org_insert="UPDATE `AdminUsersandPrivileges` SET `HeaderID`='$HeaderIds',`OptionID`='$OptionIds' WHERE `UserID`='$UserID'";
	
	  
	$stmt1=$iconn->query($sql_org_insert);

	if($stmt1){
		legacyAuditLogUpdateById('AdminUsersandPrivileges', 'UserID', $UserID, $oldAdminPrivData, $_POST, [
			'ReferenceNo' => $UserID,
			'Remarks' => 'Admin user privileges updated',
		], 'Sales');
		header('Location: AddAdminUsersandPrivileges.php');
	}
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
$_SESSION['Status']='';	  ?>
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
									
										
				
				
				
 <?php while($RowHeaders=$StmtHeaders->fetch_array()) {
 $HeaderId	= $RowHeaders['HeaderID'];
 $SqlOptions="SELECT * FROM `$SideBarOptionTbl` where Status=1 and HeaderId='$HeaderId'"; 
$StmtOptions=$iconn->query($SqlOptions);

 ?>
 <div class="row">
 <div class="form-group col-sm-12 checkdiv1" >
<label for="exampleInputEmail1"><H5 style="color: BROWN;"><?php echo $RowHeaders['Header']?></H5></label><br>
				 
				  <?php while($RowOptions=$StmtOptions->fetch_array()){
					  $OptionID=$RowOptions['ID'];
					  $OptionName=$RowOptions['Name'];
					  ?>
				 
				  &nbsp; &nbsp;<input type="checkbox" id="OptionId" name="OptionId[]" 
				 value="<?php echo $OptionID;?>">
<label for="exampleInputEmail1"> <?php echo $OptionName;?></label>

				 
              
				  <?php } ?>
                </div>  </div> <HR></HR>
				  <?php } ?>
              
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			 <!-- <a target="" href="LinersList.php"
       class="btn btn-danger  "> Close</a>-->
			     <?php    if($Count==0){ ?>
  
                <button type="submit"  name='submit' class="btn btn-success">ADD</button>
				 <?php    } else { ?>
 
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
				 <?php    } ?>

              </div>	</div>
				
				
					
				
				
								

            </form>
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>	

<script>
$('document').ready(function(){
$('.checkdiv1 input').change(function() {
//function getOptionIds(){
	var OptionIds = $("input[name='OptionId']:checked").map(function() {
                return this.value;
            }).get().join(', ');
			$('#OptionId').val(OptionIds + '');
}
});
 
</script>
