 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Add Options";

$DeptID=$_SESSION["DeptID"];
$PageTitleFolder="ColdStore";

$SqlDropdown="SELECT * FROM `AdminDropDownCategories` where Status=1";
$StmtDropdown=$iconn->query($SqlDropdown);


extract($_POST);
extract($_GET);




if(isset($_POST['submit'])){ 

	$Sqlcount="SELECT * FROM `AdminGeneralOptions` WHERE DeptID='$DeptID' and OptionType='$OptionType'";
	$StmtCount=$iconn->query($Sqlcount);
	$Count=mysqli_num_rows($StmtCount);
	$SortOrder=$Count+1;
	//$Deptname='CS';
	
$InsertPackingStyle="INSERT INTO `AdminGeneralOptions`( `DeptID`, `OptionType`, `Value`, `Name`, `SortOrder`) 
VALUES('$DeptID','$OptionType','$Value','$Value','$SortOrder')";
	
	
	 	$ExePackingStyle=$iconn->query($InsertPackingStyle);
	    $CsPoId = $iconn->insert_id;		
	//exit;		
	
	header("Location: AddOptions.php");
	
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

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

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


								        <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Type</label>
				  
				<select class="form-control select2" name="OptionType" id="OptionType">
				<option> Select</option>
			    <?php while($RowDropDown=$StmtDropdown->fetch_array()){
					
					$CatName= $RowDropDown['CatName'];
						//$VALUE= $value['Value'];
				?>
			 <option 
			 value="<?php echo $CatName ?>"><?php echo $CatName ?></option>
					
					<?php  }?>
				<select>
				</div>

				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Value</label>
             <input required  type="text" class="form-control" name="Value" id="Value" placeholder="Enter Value" >
                </div>
				

				</div>
				
              <!-- /.box-body -->


			<div class="col-xs-12 box-footer"  align="right">	
	
				<button type="submit" style="" name='submit' class="btn btn-success">Add</button>
			
            </div>





				</div>
              <!-- /.box-body -->


            </form>



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



</html>
