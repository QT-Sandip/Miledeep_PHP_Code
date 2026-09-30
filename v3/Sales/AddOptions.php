 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Add Options";


$PageTitleFolder="Master";

$SqlDropdown="SELECT * FROM `AdminDropDownCategories` where Status=1";
$StmtDropdown=$iconn->query($SqlDropdown);

$SqlGradeGroup="SELECT DISTINCT GradeGroup FROM `ProductionGrades` WHERE GradeGroup!='' order by abs(GradeGroup)";
$StmtGradeGroup=$iconn->query($SqlGradeGroup);
/*$RowDropdown=$StmtDropdown1->fetch_array();
$DeptID=$RowDropdown['DeptID'];*/

extract($_POST);
extract($_GET);




if(isset($_POST['submit'])){ 

	//$Sqlcount="SELECT * FROM `AdminGeneralOptions` WHERE DeptID='$DeptID' and OptionType='$OptionType'";
	$Sqlcount="SELECT * FROM `OPTIONS` as OPT
	left join AdminDropDownCategories as ADC on  ADC.DeptID=OPT.DEPT
    WHERE DEPT='$DeptID' and TYPE='$OptionType'";
	$StmtCount=$iconn->query($Sqlcount);
	$Count=mysqli_num_rows($StmtCount);
	$SortOrder=$Count+1;
	$DeptID='CS';
	
	$SqlOption="SELECT * FROM `ProductionGrades` WHERE value='$Value' ";
	$StmtGrade=$iconn->query($SqlOption);
	$OptionCount=mysqli_num_rows($StmtGrade);
	if($OptionCount==0){
 $InsertOption="INSERT INTO 
`ProductionGrades`( `DeptId`, `Type`, `value`, `Options`,`GradeGroup`, `SortOrder`) 
					VALUES('$DeptID','$OptionType','$Value','$Value','$GradeGroup','$SortOrder')";
$StmtOption=$iconn->query($InsertOption);
	
	if($StmtOption){		
		$insertedGradeId = $iconn->insert_id;
		if (!empty($insertedGradeId)) {
			legacyAuditLogInsertById('ProductionGrades', 'ID', $insertedGradeId, $_POST, array(
				'ReferenceNo' => $Value,
				'Remarks' => 'Production grade created',
			), 'Sales');
		}
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "GradesList.php";
		</script>';		
	}
	}
	else{
		
		echo "<script>alert('Already Exists');</script>";
	}
	 	
	   
	//header("Location: AddOptions.php");
	
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
                  <label for="exampleInputEmail1">Type<span style="color:red">*</span></label>
				  
				<select required class="form-control select2" name="OptionType" id="OptionType">
				
			    <?php while($RowDropDown=$StmtDropdown->fetch_array()){
					
					$CatName= $RowDropDown['CatName'];
						//$VALUE= $value['Value'];
				?>
			 <option 
			 value="<?php echo $CatName ?>"><?php echo $CatName ?></option>
					
					<?php  }?>
				</select>
				</div>

				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Group<span style="color:red">*</span></label>
				  
				<select required class="form-control select2" name="GradeGroup" id="GradeGroup">
				<option value="">Select</option>
			    <?php while($RowGradeGroup=$StmtGradeGroup->fetch_array()){
					
					$GradeGroup= $RowGradeGroup['GradeGroup'];
						//$VALUE= $value['Value'];
				?>
			 <option 
			 value="<?php echo $GradeGroup ?>"><?php echo $GradeGroup ?></option>
					
					<?php  }?>
				</select>
				</div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Value<span style="color:red">*</span></label>
             <input required  type="text" class="form-control" name="Value" id="Value" placeholder="Enter Value" >
                </div>
				
				

				</div>
				
              <!-- /.box-body -->


			<div class="col-xs-12 box-footer"  align="right">
 <a target="" href="GradesList.php"
       class="btn btn-danger  "> Close</a>			
	
				<button type="submit" style="" name='submit' class="btn btn-success">Submit</button>
			
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
	
	
	
