<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);

	$PageTitle="SANITARY STANDARD ASSESSING OF UNIT WATER & ICE" ;


$PageTitleFolder="Quality Control";

$ID=$_GET['ID'];

include "Constants.php";



$sqlIceNWater="SELECT * FROM `QcIHIceNWaterRprt` where ID='$ID'";
$stmtIceNWater=$iconn->query($sqlIceNWater);
$RowIceNWater=$stmtIceNWater->fetch_array();

$COUNT=mysql_num_rows($stmtIceNWater);


$sql_LAB_USERS="SELECT * FROM `LAB_USERS` where stats='1' ";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1=$iconn->query($sql_LAB_USERS);


$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='QC' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}


if(isset($_POST['submit'])){
	$ID=$_GET['ID'];
 $COUNT=mysqli_num_rows($stmtIceNWater);


	//$SampleDate = date("d-m-Y", strtotime($SamplingDate));
	IF($COUNT==0){
	$sql_org_insert="INSERT INTO `QcIHIceNWaterRprt`
	(`UnitName`, `SampleDate`,`AnalysedBy`, `VerifiedBy`)
	VALUES('$UnitName','$SamplingDate','$AnalysedBy','$VerifiedBy')";
	
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	
	
	}else{
		$oldRow = legacyAuditFetchRowById('QcIHIceNWaterRprt', 'ID', $ID, 'QualityControl');
	$sql_org_insert="UPDATE `QcIHIceNWaterRprt`
	SET `UnitName`='$UnitName',
	`SampleDate`='$SamplingDate',
	`AnalysedBy`='$AnalysedBy',
	`VerifiedBy`='$VerifiedBy'
	WHERE ID='$ID'";	
	
	$stmt=$iconn->query($sql_org_insert);
	//$iid = $iconn->insert_id;
	
	}
	  
	
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcIHIceNWaterRprt', 'ID', $iid, $_POST, ['Remarks' => 'Ice & Water Report Added'], 'QualityControl');
		header('Location: IHAddIce_WaterResult.php?ID='.$iid);
	}ELSE{
		if(isset($oldRow)){
			legacyAuditLogUpdateById('QcIHIceNWaterRprt', 'ID', $ID, $oldRow, $_POST, ['Remarks' => 'Ice & Water Report Updated'], 'QualityControl');
		}
	header('Location: CommonSearchList.php?id=4');	
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
		<form role="form" method="post" enctype="multipart/form-data">

                                <div class="card">
								
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
			<?php if($RowIceNWater['UnitName']==$VALUE){
					 ?> selected="selected" <?php }?>
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Sampling</label>
               <input required type="date" value="<?php  echo date("Y-m-d");?>"class="form-control"
			   name='SamplingDate' id="SamplingDate"
			   placeholder="Enter Date of Sampling"
			   value="<?php echo $RowIceNWater['SamplingDate']?>">
   
                </div>
			
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analysed By</label>
                   <select required type="text" class="form-control select2" name='AnalysedBy' id="AnalysedBy">

				    <?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option 
					 
			     <?php if($RowIceNWater['AnalysedBy']==$LAB_USERS_details['USER_NAME']){
					 ?> selected="selected" <?php }?>
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
				 <?php if($RowIceNWater['VerifiedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?>	 
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>

				</div>
				

                
  <div class="col-xs-12 "  align="right">
                
				
				<button type="submit"  name='submit' class="btn btn-success">Submit</button>
</div>
     </form>

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
