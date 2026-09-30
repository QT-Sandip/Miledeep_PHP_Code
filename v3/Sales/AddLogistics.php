<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

include "Constants.php";


$PageTitleFolder="Master";
$PageTitle=" New CHA Agent";

$ID=$_GET['ID'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$sqlLogistics="SELECT * FROM `$ScrmLogisticsMaster` where ID='$ID'";
$stmtLogistics=$iconn->query($sqlLogistics);
$RowLogistics=$stmtLogistics->fetch_array();

 $COUNT=mysqli_num_rows($stmtLogistics);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
		
	IF($COUNT==0){	
	 $sql_org_insert="INSERT INTO `$ScrmLogisticsMaster`(`Name`, `Address`,`SalesContactName`, `SalesMailId`,
	 `SalesAddress`, `SalesCity`, `OperationContactName`, `OperationMailId`, `OperationAddress`, `OperationCity`, 
	 `BookingContactName`, `BookingMailId`, `BookingAddress`, `BookingCity`, `DocsContactName`, `DocsMailId`, 
	 `DocsAddress`, `DocsCity`) VALUES ('$Name','$Address','$SalesContactName','$SalesMailId','$SalesAddress','$SalesCity'
	 ,'$OperationContactName','$OperationMailId','$OperationAddress','$OperationCity','$BookingContactName',
	 '$BookingMailId','$BookingAddress','$BookingCity','$DocsContactName','$DocsMailId','$DocsAddress','$DocsCity')"; 

$stmt1=$iconn->query($sql_org_insert);
if($stmt1){
	$insertedLogisticsId = $iconn->insert_id;
	legacyAuditLogInsertById($ScrmLogisticsMaster, 'ID', $insertedLogisticsId, $_POST, array(
		'ReferenceNo' => $insertedLogisticsId,
		'Remarks' => 'CHA agent created',
	), 'Sales');

header('Location: LogisticsList.php');	
}
	 
	}
	
	else{
	$existingLogistics = legacyAuditFetchRowById($ScrmLogisticsMaster, 'ID', $ID, 'Sales');
	
	$sql_org_insert="UPDATE `$ScrmLogisticsMaster`
	SET `Name`='$Name',
	`Address`='$Address',
	`SalesContactName`='$SalesContactName',
	`SalesMailId`='$SalesMailId',
	`SalesAddress`='$SalesAddress',
	`SalesCity`='$SalesCity',
	`OperationContactName`='$OperationContactName',
	`OperationMailId`='$OperationMailId',
	`OperationAddress`='$OperationAddress',
	`OperationCity`='$OperationCity',
	`BookingContactName`='$BookingContactName',
	`BookingMailId`='$BookingMailId',
	`BookingAddress`='$BookingAddress',
	`BookingCity`='$BookingCity',
	`DocsContactName`='$DocsContactName',
	`DocsMailId`='$DocsMailId',
	`DocsAddress`='$DocsAddress',
	`DocsCity`='$DocsCity'
	
	WHERE ID='$ID'";	
	
	$stmt1=$iconn->query($sql_org_insert);
	if($stmt1){
		legacyAuditLogUpdateById($ScrmLogisticsMaster, 'ID', $ID, $existingLogistics, $_POST, array(
			'ReferenceNo' => $ID,
			'Remarks' => 'CHA agent updated',
		), 'Sales');

		$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ID;
            postUserEditLog($ArgumentsArray);
	
	header('Location: LogisticsList.php');	
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
                            <div class="col-lg-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

    
                 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Name<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='Name' 
				  id="Name" placeholder="Enter  Name" 
				  value="<?php echo $RowLogistics['Name']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Address<span style="color:red">*</span></label>
                  <input required type="text"  class="form-control" name='Address' 
				  id="Address" placeholder="Enter  Address" 
				  value="<?php echo $RowLogistics['Address']?>">
                </div>
                </div>
				<h2 align="center">Sales</h2>

				        

				              <div class="card-body">
							  <div class="row">
                  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Name</label>
                  <input  type="text" class="form-control" name='SalesContactName' 
				  id="SalesContactName" placeholder="Enter Contact Name" 
				  value="<?php echo $RowLogistics['SalesContactName']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">EMail Id</label>
                  <input  type="text"  class="form-control" name='SalesMailId' 
				  id="SalesMailId" placeholder="Enter Mail Id" 
				  value="<?php echo $RowLogistics['SalesMailId']?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Address</label>
                  <input  type="text" class="form-control" name='SalesAddress' 
				  id="SalesAddress" placeholder="Enter Address" 
				  value="<?php echo $RowLogistics['SalesAddress']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">City</label>
                  <input  type="text"  class="form-control" name='SalesCity' 
				  id="SalesCity" placeholder="Enter City" 
				  value="<?php echo $RowLogistics['SalesCity']?>">
                </div>
                </div>
                </div>
				
				
				<h2 align="center">Operation</h2>

				        

				              <div class="card-body">
							  <div class="row">
                  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Name</label>
                  <input  type="text" class="form-control" name='OperationContactName' 
				  id="OperationContactName" placeholder="Enter Contact Name" 
				  value="<?php echo $RowLogistics['OperationContactName']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">EMail Id</label>
                  <input  type="text"  class="form-control" name='OperationMailId' 
				  id="OperationMailId" placeholder="Enter Mail Id" 
				  value="<?php echo $RowLogistics['OperationMailId']?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Address</label>
                  <input  type="text" class="form-control" name='OperationAddress' 
				  id="OperationAddress" placeholder="Enter Address" 
				  value="<?php echo $RowLogistics['OperationAddress']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">City</label>
                  <input  type="text"  class="form-control" name='OperationCity' 
				  id="OperationCity" placeholder="Enter City" 
				  value="<?php echo $RowLogistics['OperationCity']?>">
                </div>
                </div>
                </div>
				<h2 align="center">Booking</h2>

				        

				              <div class="card-body">
							  <div class="row">
                  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Name</label>
                  <input  type="text" class="form-control" name='BookingContactName' 
				  id="BookingContactName" placeholder="Enter Contact Name" 
				  value="<?php echo $RowLogistics['BookingContactName']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">EMail Id</label>
                  <input  type="text"  class="form-control" name='BookingMailId' 
				  id="BookingMailId" placeholder="Enter Mail Id" 
				  value="<?php echo $RowLogistics['BookingMailId']?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Address</label>
                  <input  type="text" class="form-control" name='BookingAddress' 
				  id="BookingAddress" placeholder="Enter Address" 
				  value="<?php echo $RowLogistics['BookingAddress']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">City</label>
                  <input  type="text"  class="form-control" name='BookingCity' 
				  id="BookingCity" placeholder="Enter City" 
				  value="<?php echo $RowLogistics['BookingCity']?>">
                </div>
                </div>
                </div>
				<h2 align="center">Document</h2>

				        

				              <div class="card-body">
							  <div class="row">
                  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Name</label>
                  <input  type="text" class="form-control" name='DocsContactName' 
				  id="DocsContactName" placeholder="Enter Contact Name" 
				  value="<?php echo $RowLogistics['DocsContactName']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">EMail Id</label>
                  <input  type="text"  class="form-control" name='DocsMailId' 
				  id="DocsMailId" placeholder="Enter Mail Id" 
				  value="<?php echo $RowLogistics['DocsMailId']?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Address</label>
                  <input  type="text" class="form-control" name='DocsAddress' 
				  id="DocsAddress" placeholder="Enter Address" 
				  value="<?php echo $RowLogistics['DocsAddress']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">City</label>
                  <input  type="text"  class="form-control" name='DocsCity' 
				  id="DocsCity" placeholder="Enter City" 
				  value="<?php echo $RowLogistics['DocsCity']?>">
                </div>
				</div>
				</div>
				<div class="col-xs-12 box-footer"  align="right">
				<a target="" href="LogisticsList.php"
       class="btn btn-danger  "> Close</a>
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
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
</html>				

