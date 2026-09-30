 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Create PackingStyle";


$PageTitleFolder="ColdStore";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$today_date=date('Y-m-d');


//ORIGINAL

$SqlCsOptions="SELECT * FROM `CsGeneralOptions`";
$StmtCsOptions=$iconn->query($SqlCsOptions);
 

$ARY_OPTIONS=array();
WHILE($RowCsOPtions=$StmtCsOptions->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$RowCsOPtions;
}


if(isset($_POST['submit'])){ 

	if($UOM=='LBS'){
		  $MFactor=0.454;
	  }
	  elseif($UOM=='KGS'){
		  $MFactor=1;
	  }
	  elseif($UOM=='GRAMS'){
		  $MFactor=0.001;;
	  }	  
	 
	$PackingStyle=$TotalSlabs .' X '. $SlabWeight .' '. $UOM;
	

$InsertPackingStyle="
INSERT INTO `CsPackingStyles`
(`TotalSlabs`, `SlabWeight`, `Uom`, `PackingStyle`, `MFactor`) 
VALUES('$TotalSlabs','$SlabWeight','$UOM','$PackingStyle','$MFactor')";
	
	//echo  $InsertPoData;
	
	 	$ExePackingStyle=$iconn->query($InsertPackingStyle);
	    $CsPoId = $iconn->insert_id;		
	//exit;		
	
	header("Location: CreatePackingStyle.php");
	
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


				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Total Slabs</label>
              <input required  type="text" class="form-control" name="TotalSlabs" id="TotalSlabs" placeholder="Enter Total Slabs" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Slab Weight</label>
             <input required  type="text" class="form-control" name="SlabWeight" id="SlabWeight" placeholder="Enter Slab Weight" >
                </div>
				
				        <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">UOM</label>
				  
				<select class="form-control select2" name="UOM" id="UOM">
			    <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='UOM'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Value'];
				?>
			 <option <?php if($UNIT==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
				<select>
				</div>

				</div>
				
              <!-- /.box-body -->


			<div class="col-xs-12 box-footer"  align="right">	
	
				<button type="submit" style="background-color: #008000" name='submit' class="btn btn-primary">Add</button>
			
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
