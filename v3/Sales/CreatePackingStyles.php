<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="Master";
$PageTitle="Add Packing Style";

/***uma(17/10/2023)*** */
 $PackingStyleID=$_GET['PackingStyleID']; 

  $SqlPackingStyles="SELECT * FROM `AdminPackingStyles`  WHERE PCKID='$PackingStyleID'"; 
$StmtPackingStyles=$iconn->query($SqlPackingStyles);
$RowPackingStyles=$StmtPackingStyles->fetch_array();

$SqlPoNo="SELECT DISTINCT PoNo FROM `ScrmPoGradeData` as SPGD left join ScrmPoData as SPD on SPD.PoID=SPGD.InvId
  WHERE PackingStyleID='$PackingStyleID'";

$StmtPoNo=$iconn->query($SqlPoNo);
$PoNoCount=mysqli_num_rows($StmtPoNo);
while($RowPoNo=$StmtPoNo->fetch_array())
{
$ArrayPoNo[]=$RowPoNo['PoNo'];
$PoNumber=implode(",",$ArrayPoNo);
}


/***uma(17/10/2023)*** */

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
	if($UOM=='LBS'){
		  $MFactor=0.454;
		  $LbsFactor=1;
	  }
	  elseif($UOM=='KGS'){
		  $MFactor=1;
		  $LbsFactor=2.204;
	  }
	  elseif($UOM=='GRAMS'){
		  $MFactor=0.001;
		  $LbsFactor=0.0022;
	  }	

	  elseif($UOM=='OZ'){
		  $MFactor=0.028;
		  $LbsFactor=0.0625;
	  }	  
	 
	$PACKING_STYLE=$TOTAL_SLABS .' X '. $SLAB_WEIGHT .' '. $UOM;

	$Netweight=$TOTAL_SLABS*$SLAB_WEIGHT;
	
	//$SqlCount="SELECT * FROM `AdminPackingStyles` WHERE PackingStyle='$PACKING_STYLE' and GrossWeight='$GrossWeight'";

	if($GrossWeight<$Netweight){
		echo '<script language="javascript" type="text/javascript"> 
                alert("Grossweight must be netweight");
                window.location = "CreatePackingStyles.php";
        </script>';
	}
	else if($PackingStyleID==''){
	$SqlCount="SELECT * FROM `AdminPackingStyles` WHERE PackingStyle='$PACKING_STYLE'";
	$StmtCount=$iconn->query($SqlCount);
	$Count=mysqli_num_rows($StmtCount);
	if($Count==0){
	 $sql_org_insert="
	 INSERT INTO `AdminPackingStyles`(`TotalSlabs`, `SlabWeight`,`GrossWeight`, `Uom`, 
	 `PackingStyle`, `MFactor`, `LbsFactor`, `Status`) VALUES 
	 ('$TOTAL_SLABS','$SLAB_WEIGHT','$GrossWeight','$UOM','$PACKING_STYLE',
	 '$MFactor','$LbsFactor','1')";

    $stmt1=$iconn->query($sql_org_insert);
	
	if($stmt1){
		$insertedPackingStyleId = $iconn->insert_id;
		legacyAuditLogInsertById('AdminPackingStyles', 'PCKID', $insertedPackingStyleId, $_POST, array(
			'ReferenceNo' => $insertedPackingStyleId,
			'Remarks' => 'Packing style created',
		), 'Sales');
	
		header('Location: PackingStyleList.php');
	}
	}
	else{
		echo '<script language="javascript" type="text/javascript"> 
                alert("Packing Style Already Exists");
                window.location = "CreatePackingStyles.php";
        </script>';
			
	
	}
}

else {

 if($PoNumber==''){
	$existingPackingStyle = legacyAuditFetchRowById('AdminPackingStyles', 'PCKID', $PackingStyleID, 'Sales');
    $sql_org_insert=" UPDATE `AdminPackingStyles` SET `TotalSlabs`='$TOTAL_SLABS',`SlabWeight`='$SLAB_WEIGHT',`GrossWeight`='$GrossWeight',
    `Uom`='$UOM',`PackingStyle`='$PACKING_STYLE',
    `MFactor`='$MFactor',`LbsFactor`='$LbsFactor' WHERE `PCKID`='$PackingStyleID'";

	$stmt1=$iconn->query($sql_org_insert);

	if($stmt1){
		legacyAuditLogUpdateById('AdminPackingStyles', 'PCKID', $PackingStyleID, $existingPackingStyle, $_POST, array(
			'ReferenceNo' => $PackingStyleID,
			'Remarks' => 'Packing style updated',
		), 'Sales');
	
		header('Location: PackingStyleList.php');
	}
	}
	else{
		echo '<script language="javascript" type="text/javascript"> 
                alert("This Packing Style is Already Assigned to Po '.$PoNumber.'");
                window.location = "CreatePackingStyles.php";
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
		
             <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Total Slabs <span style="color:red">*</span></label>
				<input required type="number" class="form-control" name='TOTAL_SLABS' id="TOTAL_SLABS" 
				  placeholder="Enter Total No of Slabs" value="<?php echo $RowPackingStyles['TotalSlabs'] ?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Slab Weight <span style="color:red">*</span></label>
<input  required type="number" class="form-control" name='SLAB_WEIGHT' id="SLAB_WEIGHT" step="any"
				  placeholder=" Enter Slab Weight" value="<?php echo $RowPackingStyles['SlabWeight'] ?>">
                </div> 
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">UOM<span style="color:red">*</span></label>
				    
				  <select  class="form-control select2" name="UOM" id="UOM" required>
				   
				  
				
				<option Value="">Select UOM</OPTION>
				<option <?php if($RowPackingStyles['Uom']=='LBS') { ?> selected="selected"<?php } ?>
				 Value="LBS">LBS</OPTION>
				<option <?php if($RowPackingStyles['Uom']=='KGS') { ?> selected="selected"<?php } ?>
				Value="KGS">KGS</OPTION>
				<option <?php if($RowPackingStyles['Uom']=='GRAMS') { ?> selected="selected"<?php } ?>
				Value="GRAMS">GRAMS</OPTION>
				<option <?php if($RowPackingStyles['Uom']=='OZ') { ?> selected="selected"<?php } ?>
				Value="OZ">OZ</OPTION>
				
				<select> 
                </div> 
				
				
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Gross Weight <span style="color:red">*</span></label>
<input  required type="number" class="form-control" name='GrossWeight' id="GrossWeight" step="any"
				  placeholder=" Enter Gross Weight" value="<?php echo $RowPackingStyles['GrossWeight'] ?>">
                </div> 	
				 
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
						  <a target="" href="PackingStyleList.php"   class="btn btn-danger  "> Close</a>

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
