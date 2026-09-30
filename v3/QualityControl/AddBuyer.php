<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="Quality Control";
$PageTitle="New Buyer";
$ID=$_GET['ID'];


$sqlConsignee="SELECT * FROM `ScrmConsigneeDetails` where ConID='$ID'";
$stmtConsignee=$iconn->query($sqlConsignee);
$RowConsignee=$stmtConsignee->fetch_array();

 $COUNT=mysqli_num_rows($stmtConsignee);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
	IF($COUNT==0){	
	 $sql_org_insert="
	 INSERT INTO `ScrmConsigneeDetails`(`ConsigneeName`, `ConsigneeAddress`,
	 `ConsigneeCountry`, `ConsigneeSplIns`, `ConsigneeFishingNo`,
	 `ConsigneeEmailAddress`, `ContactPerson`, `ContactNumber`,`PersonDesignation`)
	 VALUES ('$CONSIGNEE_NAME','$CONSIGNEE_ADDRESS',
	 '$BUYER_COUNTRY','$SPECIAL_INST','$FISHING_PMT_NO',
	 '$BUYER_EMAIL','$ContactPerson','$ContactNumber','$PersonDesignation')"; 
	 
	 
	
	$stmt1=$iconn->query($sql_org_insert);
	$BuyerID = $iconn->insert_id;
	$EmailAddress=explode(',',$BUYER_EMAIL);
	


if($stmt1){
	legacyAuditLogInsertById('ScrmConsigneeDetails', 'ConID', $BuyerID, $_POST, ['Remarks' => 'Buyer Created'], 'QualityControl');
foreach($EmailAddress as $i =>$Value) 
{
	$i >0;
	$SqlMailinsert="INSERT INTO `$ScrmConsigneeMailIDs`(`BuyerID`, `EmailAddress`, `Status`) VALUES ('$BuyerID','$Value','1')";
		$StmtMailinsert=$iconn->query($SqlMailinsert);
	}
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocBuyerDetails.php";
        </script>';
	}
	
	}else{
	$oldRow = legacyAuditFetchRowById('ScrmConsigneeDetails', 'ConID', $ID, 'QualityControl');
	$sql_org_insert="UPDATE `ScrmConsigneeDetails`
	SET `ConsigneeName`='$CONSIGNEE_NAME',
	`ConsigneeAddress`='$CONSIGNEE_ADDRESS',
	`ConsigneeCountry`='$BUYER_COUNTRY',
	`ConsigneeFishingNo`='$FISHING_PMT_NO',
	`ConsigneeSplIns`='$SPECIAL_INST',
	`ConsigneeEmailAddress`='$BUYER_EMAIL',
	`ContactPerson`='$ContactPerson',
	`ContactNumber`='$ContactNumber',
	`PersonDesignation`='$PersonDesignation'
	
	WHERE ConID='$ID'";	
	
	$stmt=$iconn->query($sql_org_insert);
	
	
	$EmailAddress=explode(',',$BUYER_EMAIL);
	
if($stmt){
	legacyAuditLogUpdateById('ScrmConsigneeDetails', 'ConID', $ID, $oldRow, $_POST, ['Remarks' => 'Buyer Updated'], 'QualityControl');
	
	$SqlMailDelete="DELETE FROM `$ScrmConsigneeMailIDs` WHERE BuyerID='$ID'";
		$StmtMailDelete=$iconn->query($SqlMailDelete);
	


foreach($EmailAddress as $i =>$Value) 
{
	$i >0;
	$SqlMailinsert="INSERT INTO `$ScrmConsigneeMailIDs`(`BuyerID`, `EmailAddress`, `Status`) VALUES ('$ID','$Value','1')";
		$StmtMailinsert=$iconn->query($SqlMailinsert);
	}
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocBuyerDetails.php";
        </script>';
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
									
										<div class="row">
		
               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Buyer Name</label>
                  <input required type="text" class="form-control" name='CONSIGNEE_NAME' 
				  id="CONSIGNEE_NAME" placeholder="Enter Buyer Name" 
				  value="<?php echo $RowConsignee['ConsigneeName']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Buyer Address</label>
                  <input required type="text"  class="form-control" name='CONSIGNEE_ADDRESS' 
				  id="CONSIGNEE_ADDRESS" placeholder="Enter Buyer Address" 
				  value="<?php echo $RowConsignee['ConsigneeAddress']?>">
                </div>
				
			  
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Buyer Country</label>
                  <input required type="text" class="form-control" name='BUYER_COUNTRY' id="BUYER_COUNTRY"
				  placeholder="Enter Country name" value="<?php echo $RowConsignee['ConsigneeCountry']?>">
                </div> 

				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Buyer Email Address</label>
                  <input required type="text" class="form-control" name='BUYER_EMAIL' id="BUYER_EMAIL"
				  placeholder="Ex: ab@gmail.com,ad@gmail.com" value="<?php echo $RowConsignee['ConsigneeEmailAddress']?>">
                </div> 
				
          
			
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Contact Person</label>
                  <input required   type="text" class="form-control" name="ContactPerson" 
				  id="ContactPerson" placeholder="Enter Contact Person" 
				  value="<?php echo $RowConsignee['ContactPerson']?>" >
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Contact Number</label>
                  <input required   type="text" class="form-control" name="ContactNumber" 
				  id="ContactNumber" placeholder="Enter Contact Number" 
				  value="<?php echo $RowConsignee['ContactNumber']?>" >
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Person Designation</label>
                  <input required   type="text" class="form-control" name="PersonDesignation" 
				  id="PersonDesignation" placeholder="Enter Person Designation" 
				  value="<?php echo $RowConsignee['PersonDesignation']?>" >
                </div>
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
						  <a target="" href="DocBuyerDetails.php"   class="btn btn-danger  "> Close</a>

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
