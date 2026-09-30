<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);
$INVOICE_ID=$_GET['id'];
$ID=$_GET['ID'];

if($ID==''){
	$PageTitle="IN HOUSE MICROBIOLOGY TEST REPORT" ;

}
else{
		$PageTitle="IN HOUSE MICROBIOLOGY TEST REPORT" ;

}
$PageTitleFolder="Quality Control";

if($ID!=''){
	
$SqlMbReport="SELECT * FROM `QcIHMBReport` WHERE ID='$ID'";
$StmtMbReport=$iconn->query($SqlMbReport);
$RowMbReport=$StmtMbReport->fetch_array();


$Count=mysqli_num_rows($StmtMbReport);

}


$SqlLabUsersList="SELECT * FROM `QcLabTechnologist` where LbuStatus='1'";
$stmt_LAB_USERS=$iconn->query($SqlLabUsersList);
$stmt_LAB_USERS1=$iconn->query($SqlLabUsersList);



if(isset($_POST['submit'])){
	$Count=mysqli_num_rows($StmtMbReport);

	$COLOUR='Transperant';
		if($Count==0){
	
	$sql_org_insert="INSERT INTO `QcIHMBReport`(`UnitName`, 
	`AnalysedBy`, `VerifiedBy`)
	VALUES('$UnitName','$AnalysedBy','$VerifiedBy')";
	
	  $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcIHMBReport', 'ID', $iid, $_POST, ['Remarks' => 'MB Report Added'], 'QualityControl');
		header('Location: IHAddMBResult.php?ReportID='.$iid);
	}
	}
	
	else{
		$oldRow = legacyAuditFetchRowById('QcIHMBReport', 'ID', $ID, 'QualityControl');
		$sql_org_insert="UPDATE `QcIHMBReport` SET `UnitName`='$UnitName',
	`AnalysedBy`='$AnalysedBy',`VerifiedBy`='$VerifiedBy' WHERE ID='$ID'
	";
	
	  $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	if($stmt1){
		legacyAuditLogUpdateById('QcIHMBReport', 'ID', $ID, $oldRow, $_POST, ['Remarks' => 'MB Report Updated'], 'QualityControl');
		header('Location: IHMBReportList.php');
	}	
	}

}

 ?>

<!DOCTYPE html>

<html lang="en">

<style>
	.submit-button{
		margin-top: 28px;
	}
</style>
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



          <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

                 
		 	  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit Name</label>
                 
                <select class="form-control" name="UnitName" id="UnitName">
					 <?php 
					 $SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='QC' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

					 
					 foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='UNITS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; 
						?>
					<option 
		<?php if($RowMbReport['UnitName']==$VALUE){ ?> selected='selected'<?php } ?>
			
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
	
						
						
			 
					 <?php  }}?>
				<select>
				
				</div>
				
		
		
		   
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analysed By</label>
                   <select required type="text" class="form-control" name='AnalysedBy' id="AnalysedBy">

				    <?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option 
					 					 <?php if($RowMbReport['AnalysedBy']==$LAB_USERS_details['LbuName']){ ?> selected='selected'<?php } ?>

					 value="<?php echo $LAB_USERS_details['LbuName'] ?>">
					 <?php echo $LAB_USERS_details['LbuName'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>             
				  <select class="form-control" name="VerifiedBy" id="VerifiedBy">
				  <?php while ($LAB_USERS_details=$stmt_LAB_USERS1->fetch_array()){ ?>
				     <option 
					 
					 <?php if($RowMbReport['VerifiedBy']==$LAB_USERS_details['LbuName']){ ?> selected='selected'<?php } ?>
					 value="<?php echo $LAB_USERS_details['LbuName'] ?>">
					 <?php echo $LAB_USERS_details['LbuName'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>
          

   

        <!-- <div class="box-body"> -->

<?php 	if($Count==0){
 ?>
	<div class="form-group col-sm-3 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success submit-button">Submit</button>
              </div>
<?php } else { ?>	  
				
				<div class="form-group col-sm-3 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-primary submit-button">Update</button>
              </div>
<?php }?>	  
			  
             
            </form>					



</div>		  
			


   </div> <!-- end row -->
    
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
