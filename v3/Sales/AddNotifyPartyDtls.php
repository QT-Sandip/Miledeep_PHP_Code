<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "Constants.php";

$PageTitleFolder="Master";
$PageTitle="Add Notify Party";
$NotifyPartyID=$_GET['ID'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlNotifyParty="SELECT * FROM `ScrmNotifyparty` WHERE NotifyPartyID='$NotifyPartyID'";
$StmtNotifyParty=$iconn->query($SqlNotifyParty);
$RowNotifyParty=$StmtNotifyParty->fetch_array();

$SqlCountryList="SELECT * FROM ScrmCountriesList";
$StmtCountryList=$iconn->query($SqlCountryList);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
	
	if($NotifyPartyID==''){
	 $sql_org_insert="
	 INSERT INTO `ScrmNotifyparty`(`NotifyPartyName`,
	 `NPartyAddress`, `NPartyCountry`, `NPartyEmailID`, 
	 `NPartyContactPerson`, `NPartyContactNumber`, `NPartyCode`,
	 `NPartyFaxNo`)VALUES('$NotifyPartyName','$NPartyAddress','$NPartyCountry','$NPartyEmailID',
      '$NPartyContactPerson','$NPartyContactNumber','$NPartyCode',
	  '$NPartyFaxNo')";
      $stmt1=$iconn->query($sql_org_insert);
	
      if($stmt1){
          $insertedNotifyPartyId = $iconn->insert_id;
          legacyAuditLogInsertById('ScrmNotifyparty', 'NotifyPartyID', $insertedNotifyPartyId, $_POST, array(
              'ReferenceNo' => $insertedNotifyPartyId,
              'Remarks' => 'Notify party created',
          ), 'Sales');
      
          header('Location: DocNotifyPartyDetails.php');
      }

		}
		
		else{
		$existingNotifyParty = legacyAuditFetchRowById('ScrmNotifyparty', 'NotifyPartyID', $NotifyPartyID, 'Sales');
		$sql_org_insert="UPDATE `ScrmNotifyparty` SET `NotifyPartyName`='$NotifyPartyName',
		`NPartyAddress`='$NPartyAddress',`NPartyCountry`='$NPartyCountry',`NPartyEmailID`='$NPartyEmailID',`NPartyContactPerson`='$NPartyContactPerson',`NPartyContactNumber`='$NPartyContactNumber',`NPartyCode`='$NPartyCode',`NPartyFaxNo`='$NPartyFaxNo' 
		WHERE NotifyPartyID='$NotifyPartyID'";	
	  $stmt1=$iconn->query($sql_org_insert);
	
      if($stmt1){
        legacyAuditLogUpdateById('ScrmNotifyparty', 'NotifyPartyID', $NotifyPartyID, $existingNotifyParty, $_POST, array(
            'ReferenceNo' => $NotifyPartyID,
            'Remarks' => 'Notify party updated',
        ), 'Sales');

        $ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$NotifyPartyID;
            postUserEditLog($ArgumentsArray);
      
          header('Location: DocNotifyPartyDetails.php');
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
		
             <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Notify Party Name<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='NotifyPartyName' id="NotifyPartyName" 
				  placeholder="Enter Notify Party Name" value="<?php echo $RowNotifyParty['NotifyPartyName']; ?>">		
		
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Notify Party Country<span style="color:red">*</span></label>
                 
				  
				  <select required class="form-control select2" name="NPartyCountry" id="NPartyCountry">
				  <option value="">Select Country</option>
				   <?php while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
				     <option <?php if($RowCountry['id']==$RowNotifyParty['NPartyCountry']){ ?> selected=="selected" <?php } ?>
					 
					 value="<?php echo $RowCountry['id'] ?>">
					 <?php echo strtoupper($RowCountry['name'])?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				 
				<div class="form-group col-lg-6" >
                  <label for="exampleInputEmail1">Notify Party Address<span style="color:red">*</span></label>
				  
				  <textarea required name="NPartyAddress" class="form-control" rows="2" cols="50"
                    id="NPartyAddress"   
					placeholder="Enter Notify Party Address" ><?php echo $RowNotifyParty['NPartyAddress'] ?></textarea>
				 		
		
                </div>
				
				
				
								
				
				
				

				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Email ID</label>
            <input   type="text" class="form-control" name='NPartyEmailID' id="NPartyEmailID"   
				  placeholder=" Enter Email ID(Ex:ABC@gmail.com)" value="<?php echo $RowNotifyParty['NPartyEmailID']; ?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Person</label>
              <input   type="text" class="form-control" name='NPartyContactPerson' id="NPartyContactPerson"   
				  placeholder=" Enter Contact Person Name" value="<?php echo $RowNotifyParty['NPartyContactPerson']; ?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Contact Number</label>
                <input   type="text" class="form-control" name='NPartyContactNumber' id="NPartyContactNumber"   
				  placeholder=" Enter Contact Number" value="<?php echo $RowNotifyParty['NPartyContactNumber']; ?>">
                </div> 
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Fax Number</label>
           <input   type="text" class="form-control" name='NPartyFaxNo' id="NPartyFaxNo"   
				  placeholder=" Enter Fax Number" value="<?php echo $RowNotifyParty['NPartyFaxNo']; ?>">
                </div> 
				
	            </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
			<a target="" href="DocNotifyPartyDetails.php" class="btn btn-danger"> Close</a>
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
