<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");



$PageTitleFolder="Master";
$PageTitle="Add Product";


$ProductID=$_GET['id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlVariety="SELECT * FROM `ScrmProductList` WHERE ProductID='$ProductID'";
$StmtVariety=$iconn->query($SqlVariety);
$RowVariety=$StmtVariety->fetch_array();
$Count=mysqli_num_rows($StmtVariety);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){

$SqlShortName="SELECT * FROM `ScrmProductList` WHERE ShortName='$ShortName'";
$StmtShortName=$iconn->query($SqlShortName);
$ShortCount=mysqli_num_rows($StmtShortName);
	
	
	     if($Count==0){
			 if($ShortCount==0){
		
	

	 $SqlInsert="
	 INSERT INTO `ScrmProductList`(`TypeProduct`, `ShortName`, `FullName`) VALUES 
	 ('$TypeProduct','$ShortName','$FullName')";
	

	$StmtInsert=$iconn->query($SqlInsert);
	
	if($StmtInsert){
        $NewProductID = mysqli_insert_id($iconn);
        legacyAuditLogInsertById('ScrmProductList', 'ProductID', $NewProductID, $_POST, array(
            'ReferenceNo' => $ShortName,
            'Remarks' => 'Sales product created',
        ), 'Sales');
	
		header('Location: ProductList');
	}
}
else{
	echo '<script>alert("Product name already exists")</script>';
}
		 }
else{
    $ExistingProduct = legacyAuditFetchRowById('ScrmProductList', 'ProductID', $ProductID, 'Sales');
	$SqlInsert=" UPDATE `ScrmProductList` SET `TypeProduct`='$TypeProduct',
	`ShortName`='$ShortName',`FullName`='$FullName' WHERE ProductID='$ProductID'";
	
	  
	$StmtInsert=$iconn->query($SqlInsert);

	if($StmtInsert){
        legacyAuditLogUpdateById('ScrmProductList', 'ProductID', $ProductID, $ExistingProduct, $_POST, array(
            'ReferenceNo' => $ShortName,
            'Remarks' => 'Sales product updated',
        ), 'Sales');
        $ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ProductID;
            postUserEditLog($ArgumentsArray);
	
		header('Location: ProductList');
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
                  <label for="exampleInputEmail1">Short Name<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='ShortName' id="ShortName" 
				  placeholder="Enter Short Name" value="<?php echo $RowVariety['ShortName'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Full Name<span style="color:red">*</span></label>
<input  required type="text" class="form-control" name='FullName' id="FullName" 
				  placeholder=" Enter Full Name" value="<?php echo $RowVariety['FullName'] ?>">
                </div> 
				
				
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="ProductList.php"
       class="btn btn-danger  "> Close</a>
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

