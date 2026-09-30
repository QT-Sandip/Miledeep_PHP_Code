<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

include "Constants.php";

$PageTitleFolder="Master";
$PageTitle="Add Buyer";
//echo $_SERVER['REQUEST_URI']; exit;
 $PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME)); 


extract($_POST);
extract($_GET);

$BuyerID=$_GET['ID'];
$PostedUserId = $_SESSION['UserID'];

$SqlBuyerDtls="select * from ScrmBuyerDetails where BuyerID='$BuyerID'";
$StmtBuyerDtls=$iconn->query($SqlBuyerDtls);
$RowBuyerDtls=$StmtBuyerDtls->fetch_array();

  $SqlCountryList="SELECT * FROM ScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList);
if(isset($_POST['submit'])){
	 
	
	IF($BuyerID==''){
	 $sql_org_insert="
	 INSERT INTO `ScrmBuyerDetails`( `BuyerName`, `BuyerCode`,
 `BuyerAddress`, `BuyerCountry`, `BuyerEmailAddress`, `BuyerContactPerson`,
 `BuyerContactNumber`, `BuyerFax`, `BuyerIRSNumber`, `BuyerUSCICODE`,
 `BuyerIFTPCODE`, `BuyerState`, `BuyerZipCode`) VALUES
('$BuyerName','$BuyerCode','$BuyerAddress','$BuyerCountry',
'$BuyerEmailAddress','$BuyerContactPerson','$BuyerContactNumber'
,'$BuyerFax','$BuyerIRSNumber','$BuyerUSCICODE','$BuyerIFTPCODE'
,'$BuyerState','$BuyerZipCode')";
$stmt1=$iconn->query($sql_org_insert);
if($stmt1){
	$insertedBuyerId = $iconn->insert_id;
	legacyAuditLogInsertById('ScrmBuyerDetails', 'BuyerID', $insertedBuyerId, $_POST, array(
		'ReferenceNo' => $insertedBuyerId,
		'Remarks' => 'Buyer created',
	), 'Sales');
	
    header('Location: DocBuyerDetails.php');
}
	}ELSE{
		$existingBuyer = legacyAuditFetchRowById('ScrmBuyerDetails', 'BuyerID', $BuyerID, 'Sales');
		

	$sql_org_insert="
        UPDATE `ScrmBuyerDetails` SET 
		`BuyerName`='$BuyerName',`BuyerCode`='$BuyerCode',
		`BuyerAddress`='$BuyerAddress',`BuyerCountry`='$BuyerCountry',
		`BuyerEmailAddress`='$BuyerEmailAddress',
		`BuyerContactPerson`='$BuyerContactPerson',
		`BuyerContactNumber`='$BuyerContactNumber',`BuyerFax`='$BuyerFax',
		`BuyerIRSNumber`='$BuyerIRSNumber',`BuyerUSCICODE`='$BuyerUSCICODE',
		`BuyerIFTPCODE`='$BuyerIFTPCODE',`BuyerState`='$BuyerState',
		`BuyerZipCode`='$BuyerZipCode'
		WHERE BuyerID='$BuyerID' ";
        $stmt1=$iconn->query($sql_org_insert);
        if($stmt1){
            legacyAuditLogUpdateById('ScrmBuyerDetails', 'BuyerID', $BuyerID, $existingBuyer, $_POST, array(
                'ReferenceNo' => $BuyerID,
                'Remarks' => 'Buyer updated',
            ), 'Sales');

            $ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$BuyerID;
            postUserEditLog($ArgumentsArray);
	
            header('Location: DocBuyerDetails.php');
        }
		
	}

	
	//$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	
	
	
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
		
             <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Buyer Name<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='BuyerName' id="BuyerName" 
				  placeholder="Enter Buyer Name" value="<?php echo $RowBuyerDtls['BuyerName'];?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Buyer Code</label>
              <input   type="text" class="form-control" name='BuyerCode' id="BuyerCode" 
				  placeholder=" Enter Buyer Code(Optional)" value="<?php echo $RowBuyerDtls['BuyerCode'];?>">
                </div> 
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Buyer Country<span style="color:red">*</span></label>
                 
				  
				  <select required class="form-control select2" name="BuyerCountry" id="BuyerCountry">
				  <option value="">Select Country</option>
				   <?php while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
				     <option <?php if($RowCountry['id']==$RowBuyerDtls['BuyerCountry']){ ?> selected=="selected" <?php } ?>
					 
					 value="<?php echo $RowCountry['id'] ?>">
					 <?php echo strtoupper($RowCountry['name'])?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				 	
				<div class="form-group col-lg-6" >
                  <label for="exampleInputEmail1">Buyer Address<span style="color:red">*</span></label>
				  
				  <textarea required name="BuyerAddress" class="form-control" rows="2" cols="50"
                    id="BuyerAddress"   
					placeholder="Enter Buyer Address" ><?php echo $RowBuyerDtls['BuyerAddress'] ?></textarea>
				 		
		
                </div>
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Email ID</label>
            <input   type="text" class="form-control" name='BuyerEmailAddress' id="BuyerEmailAddress"   
				  placeholder=" Enter Email ID(Ex:ABC@gmail.com)" value="<?php echo $RowBuyerDtls['BuyerEmailAddress'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Person</label>
              <input   type="text" class="form-control" name='BuyerContactPerson' id="BuyerContactPerson"   
				  placeholder=" Enter Contact Person Name" value="<?php echo $RowBuyerDtls['BuyerContactPerson'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Number</label>
                <input   type="text" class="form-control" name='BuyerContactNumber' id="BuyerContactNumber"   
				  placeholder=" Enter Contact Number" value="<?php echo $RowBuyerDtls['BuyerContactNumber'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Fax Number</label>
           <input   type="text" class="form-control" name='BuyerFax' id="BuyerFax"   
				  placeholder=" Enter Fax Number" value="<?php echo $RowBuyerDtls['BuyerFax'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">IRS Number</label>
             <input   type="text" class="form-control" name='BuyerIRSNumber' id="BuyerIRSNumber"   
				  placeholder=" Enter IRS Number" value="<?php echo $RowBuyerDtls['BuyerIRSNumber'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">USCI Code</label>
          <input   type="text" class="form-control" name='BuyerUSCICODE' id="BuyerUSCICODE"   
				  placeholder=" Enter USCI Code" value="<?php echo $RowBuyerDtls['BuyerUSCICODE'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">IFTP Code</label>
<input   type="text" class="form-control" name='BuyerIFTPCODE' id="BuyerIFTPCODE"   
				  placeholder=" Enter IFTP Code" value="<?php echo $RowBuyerDtls['BuyerIFTPCODE'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">State(For USA)</label>
<input   type="text" class="form-control" name='BuyerState' id="BuyerState"   
				  placeholder=" Enter State(For USA)" value="<?php echo $RowBuyerDtls['BuyerState'];?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Zipcode(For USA)</label>
<input   type="text" class="form-control" name='BuyerZipCode' id="BuyerZipCode"   
				  placeholder=" Enter Zipcode(For USA)" value="<?php echo $RowBuyerDtls['BuyerZipCode'];?>">
                </div> 
				
				
				
				 
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
			<a target="" href="DocBuyerDetails.php" class="btn btn-danger"> Close</a>
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
