<?php



session_start();

$PageTitle="Edit Department";
$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$Id=$_GET['ID'];
$SUB_ID=$_GET['SUB_ID'];

$SqlDept="select DM.DeptID,
 DM.DepartmentName,
 DM.OtAval,
 SDM.SubDeptID,SDM.SubDeptName from HrmDepartments as DM 
 LEFT JOIN HrmSubDepartments AS SDM ON DM.DeptID = SDM.DeptID WHERE DM.DeptID='$Id'";
$StmtDept=$iconn->query($SqlDept);
$RowDept=$StmtDept->fetch_array();

 
extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	

	
	
 $sql_org_update=" UPDATE `HrmDepartments` SET `DepartmentName`='$DeptName',`OtAval`='$OtAval'
			 WHERE DeptID='$ID'		  ";
			
			$oldDeptData = legacyAuditFetchRowById('HrmDepartments', 'DeptID', $ID, 'Hrm');
			$stmt1=$iconn->query($sql_org_update);
	//$MB_REPORT_ID = $iconn->insert_id;
			if($stmt1){
			legacyAuditLogUpdateById('HrmDepartments', 'DeptID', $ID, $oldDeptData, $_POST, array(), 'Hrm');
$sql_org_update="UPDATE `HrmSubDepartments` SET `SubDeptName`='$SubDeptName' WHERE SubDeptID='$SUB_ID'";

			$oldSubDeptData = legacyAuditFetchRowById('HrmSubDepartments', 'SubDeptID', $SUB_ID, 'Hrm');
			$stmt2=$iconn->query($sql_org_update);
			if($stmt2){
			legacyAuditLogUpdateById('HrmSubDepartments', 'SubDeptID', $SUB_ID, $oldSubDeptData, $_POST, array(), 'Hrm');
			}
	
	}
			header('Location: HrmDepartments.php');

}



 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
                 <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

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

                        <?php include ($IncludesPath."page-title.php");?>
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								 <form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			

                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Department Name</label>
                  <input required type="text" class="form-control" name='DeptName' id="DeptName"
				  placeholder="Enter Department Name" value="<?php echo $RowDept['DepartmentName']?>">
                </div>
			
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">OT Availability</label>
                   <select class="form-control" name='OtAval' id="OtAval" >
				   <option <?php if($RowDept['OtAval']=='Yes'){?>selected="selected"<?php }?> value="Yes"> Yes</option>
				   <option <?php if($RowDept['OtAval']=='No'){?>selected="selected"<?php }?> value="No">No</option>
				  </select>
                </div>
				
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Sub Department Name</label>
                  <input required type="text" class="form-control" name='SubDeptName' id="SubDeptName"
				  placeholder="Enter Sub Department Name" value="<?php echo $RowDept['SubDeptName']?>">
                </div>
				 
                </div>
				
				<div class="col-xs-12 box-footer"  align="right">
				 <a target="" href="HrmDepartments.php"   class="btn btn-danger  "> Close</a>
                <button type="submit"  name='submit' class="btn btn-success waves-effect waves-light">
				
				Update</button>
				
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

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ("Includes/NewScripts.php");?>
      

       
    </body>
</html>				
