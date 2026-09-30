<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);

	$PageTitle="SANITARY STANDARD ASSESSING OF UNIT SWAB" ;


$PageTitleFolder="Quality Control";

$ID=$_GET['ID'];

include "Constants.php";

$sqlSWAB="SELECT * FROM `QcIHSWABBReprt` WHERE ID='$ID' ";
$stmtSWAB=$iconn->query($sqlSWAB);
$ROWSWAB=$stmtSWAB->FETCH_ARRAY();
$Count=mysqli_num_rows($stmtSWAB);

$sql_LAB_USERS="SELECT * FROM `LAB_USERS` where stats='1'";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1=$iconn->query($sql_LAB_USERS);


$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='QC' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}


if(isset($_POST['submit'])){
	$RID=$ROWSWAB['ID'];
	$Count=mysqli_num_rows($stmtSWAB);

	IF($Count==0){
	$sql_org_insert="INSERT INTO `QcIHSWABBReprt`(`UNIT`, 
	`SampleDate`, `AnalysedBy`, `VerifiedBy`, `Remarks`, 
	`CorrectiveAction`) VALUES ('$UnitName','$SampleDate','$AnalysedBy',
	'$VerifiedBy','$Remarks','$CorrectiveAction')";
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	
	if($stmt1){
		legacyAuditLogInsertById('QcIHSWABBReprt', 'ID', $iid, $_POST, ['Remarks' => 'SWAB Report Added'], 'QualityControl');
		header('Location: IHAddSWABResult.php?ID='.$iid);
	}
	
	
	}
	
	ELSE{
		$oldRow = legacyAuditFetchRowById('QcIHSWABBReprt', 'ID', $RID, 'QualityControl');
	$sql_org_insert=" 
	UPDATE `QcIHSWABBReprt` SET 
	`UNIT`='$UnitName',
	`SampleDate`='$SampleDate',
	`AnalysedBy`='$AnalysedBy',
	`VerifiedBy`='$VerifiedBy',`Remarks`='$Remarks',
	`CorrectiveAction`='$CorrectiveAction' WHERE ID='$RID'";	
	
	$stmt1=$iconn->query($sql_org_insert);
	//$iid = $iconn->insert_id;
//echo $iid;
if($stmt1){
		legacyAuditLogUpdateById('QcIHSWABBReprt', 'ID', $RID, $oldRow, $_POST, ['Remarks' => 'SWAB Report Updated'], 'QualityControl');
		header('Location: CommonSearchList.php?id=3');
	}
		
	
	}
	  //$sql_org_insert;

	
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

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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




    <!-- Main content -->



          <!--<div class="row">
                            <div class="col-12">-->
							                                <div class="card">

		<form role="form" method="post" enctype="multipart/form-data">

								
                                    <div class="card-body">
									
										<div class="row">

                 
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">UNIT Name</label>
                 
                <select class="form-control select2" name="UnitName" id="UnitName">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='UNITS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option 
					<?php if($ROWSWAB['UNIT']==$VALUE){?>selected='selected'<?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Sampling</label>
               <input required type="date" value="<?php  echo $ROWSWAB['SampleDate']?>"class="form-control"
			   name='SampleDate' id="SampleDate"
			   placeholder="Enter Date of Sampling">
   
                </div>
			
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analysed By</label>
                   <select required type="text" class="form-control select2" name='AnalysedBy' id="AnalysedBy">

				    <?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option 
					  <?php if($ROWSWAB['AnalysedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?>  
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>             
				  <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				  <?php while ($LAB_USERS_details=$stmt_LAB_USERS1->fetch_array()){ ?>
				     <option 
					 
					  <?php if($ROWSWAB['VerifiedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?>  
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>


               <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Remarks</label>
                  <input required type="text" class="form-control" name="Remarks" id="Remarks"
				  placeholder="Enter Remarks" value="<?php  echo $ROWSWAB['Remarks'] ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Corrective action</label>
                  <input required type="text" class="form-control" 
				  name="CorrectiveAction" id="CorrectiveAction"
				  placeholder="Enter Corrective action" value="<?php echo $ROWSWAB['CorrectiveAction']?>">
                </div>



				</div>
				

	

<?php if($Count==0) { ?>
	<div class="col-xs-12 "  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
<?php } else {  ?>

	<div class="col-xs-12 "  align="right">
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
              </div>
    <?php } ?>
         
     </form>

    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                          

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
</html>				
