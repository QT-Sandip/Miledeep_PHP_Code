<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);

	$PageTitle="DAILY WATER ANALYSIS REPORT" ;


$PageTitleFolder="Quality Control";




$INVOICE_ID=$_GET['ID'];

$sqlTPCAnalysis="SELECT * FROM `QcIHTPCMicroAnalysisRprt` where ID='$INVOICE_ID'";
$stmtTPCAnalysis=$iconn->query($sqlTPCAnalysis);
$invoice_details=$stmtTPCAnalysis->fetch_array();


$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='QC' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}



$sql_LAB_USERS="SELECT * FROM `LAB_USERS` where stats='1'";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1=$iconn->query($sql_LAB_USERS);


if(isset($_POST['submit'])){
		$ID=$_GET['ID'];
	//$SampleDate = date("d-m-Y", strtotime($SampleDate));
	
$COUNT=mysqli_num_rows($stmtTPCAnalysis);

	IF($COUNT==0){
	 $sql_org_insert="INSERT INTO `QcIHTPCMicroAnalysisRprt`(`SampleDate`,
	`SampleTime`, `AnalysedBy`, `VerifiedBy`,`Unit`)VALUES 
	('$SampleDate','$SampleTime','$AnalysedBy','$VerifiedBy','$Unit')"; 
	
	$stmt1=$iconn->query($sql_org_insert);
	$M_REPORT_ID = $iconn->insert_id;
	
	}ELSE{
		$oldRow = legacyAuditFetchRowById('QcIHTPCMicroAnalysisRprt', 'ID', $ID, 'QualityControl');
		$sql_org_insert="UPDATE `QcIHTPCMicroAnalysisRprt` SET
		`SampleDate`='$SampleDate',
		`SampleTime`='$SampleTime',
		`AnalysedBy`='$AnalysedBy',
		`VerifiedBy`= '$VerifiedBy',
		`Unit`= '$Unit'
		WHERE ID='$ID'";
		$stmt=$iconn->query($sql_org_insert);
	}

	
	//$M_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcIHTPCMicroAnalysisRprt', 'ID', $M_REPORT_ID, $_POST, ['Remarks' => 'TPC Micro Analysis Report Added'], 'QualityControl');
		
		header('Location: IHAddDlyWaterTPDMicroReport.php?ReportID='.$M_REPORT_ID);
	}ELSE{
		if(isset($oldRow)){
			legacyAuditLogUpdateById('QcIHTPCMicroAnalysisRprt', 'ID', $ID, $oldRow, $_POST, ['Remarks' => 'TPC Micro Analysis Report Updated'], 'QualityControl');
		}
     header('Location: CommonSearchList.php?id=5');	
		
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
                  <label for="exampleInputEmail1">UNIT </label>
                 
                <select class="form-control select2" name="Unit" id="Unit">
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
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Sample Date</label>
                  <input required type="date" class="form-control"  name="SampleDate" id="SampleDate"
				  value="<?php echo $invoice_details['SampleDate']; ?>" placeholder="Enter Sample Date">
                </div>
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Sample Time</label>
                  <input required type="time" class="form-control"  name="SampleTime" id="SampleTime" 
				  value="<?php echo $invoice_details['SampleTime']; ?>" placeholder="Enter Sample Time">
                </div>
				
				
				

                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analysed By</label>
                   
					<select required type="text" class="form-control select2" name='AnalysedBy' id="AnalysedBy">
					<?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option 
					  <?php if($invoice_details['AnalysedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?> 
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>"><?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>
				</select>
               
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label> 
			      <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				 <?php while ($LAB_USERS_details=$stmt_LAB_USERS1->fetch_array()){ ?>
				     <option 
				 <?php if($invoice_details['VerifiedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?>  
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>"><?php echo $LAB_USERS_details['USER_NAME'] ?></option>
					 
				 <?php  }?>				  
				  </select>
   
               
                </div>
                </div>
               

<div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-primary">Submit</button>
              </div>
         
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
