<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$PageTitleFolder="Master";
$PageTitle="Add Descriptions";


$ID=$_GET['id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlSelect="SELECT * FROM `ScrmDescriptionMaster` WHERE DesID='$ID'";
$stmtSelect=$iconn->query($SqlSelect);
$RowSelect=$stmtSelect->fetch_array();

$SqlExporter="SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
$StmtExporter=$iconn->query($SqlExporter);

$CustomNote=$RowSelect['CustomNote'];
//EXIT;
$COUNT=mysqli_num_rows($stmtSelect);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	 			
	
	 IF($COUNT==0){		
	$SqlInsert="INSERT INTO `ScrmDescriptionMaster`(`CustomNote`, 
	`ChapterNote`,`ShipmentNote`, `DrawBackNote`, `TaxNote`,
	`DeclarationNote`,`AdditionalNote`, `CommercialNote`, `ProformaNote`, 
	`Notifications`, `EffectiveFromDate`,`ExporterID`)VALUES
	('$CustomNote','$ChapterNote','$ShipmentNote','$DrawBackNote'
	,'$TaxNote','$DeclarationNote','$AdditionalNote','$CommercialNote'
	,'$ProformaNote','$Notifications','$EffectiveFromDate','$ExporterID')";
	

	$stmt=$iconn->query($SqlInsert);

	if($stmt){
		$insertedDescriptionId = $iconn->insert_id;
		legacyAuditLogInsertById('ScrmDescriptionMaster', 'DesID', $insertedDescriptionId, $_POST, array(
			'ReferenceNo' => $insertedDescriptionId,
			'Remarks' => 'Description created',
		), 'Sales');
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DescriptionList.php";
		</script>';
	}
	
  }else{
	   $existingDescription = legacyAuditFetchRowById('ScrmDescriptionMaster', 'DesID', $ID, 'Sales');
	   $SqlInsert="UPDATE `ScrmDescriptionMaster` SET 
	  `CustomNote`= '$CustomNote',
	  `ChapterNote`= '$ChapterNote',
	  `ShipmentNote`= '$ShipmentNote',
	  `DrawBackNote`= '$DrawBackNote',
	  `TaxNote`= '$TaxNote',
	  `DeclarationNote`= '$DeclarationNote',
	  `AdditionalNote`= '$AdditionalNote',
	  `CommercialNote`= '$CommercialNote',
	  `ProformaNote`= '$ProformaNote',
	  `Notifications`= '$Notifications',
	  `EffectiveFromDate`= '$EffectiveFromDate',
	  `ExporterID`= '$ExporterID'
	   WHERE DesID='$ID'"; 
	  
	$stmt=$iconn->query($SqlInsert);

	if($stmt){
		legacyAuditLogUpdateById('ScrmDescriptionMaster', 'DesID', $ID, $existingDescription, $_POST, array(
			'ReferenceNo' => $ID,
			'Remarks' => 'Description updated',
		), 'Sales');

		$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ID;
            postUserEditLog($ArgumentsArray);
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DescriptionList.php";
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
					
						

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
										
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Effective From Date</label>
                  <input required type="DATE" class="form-control" name='EffectiveFromDate' id="EffectiveFromDate"
				  placeholder="Enter Description"  value="<?php echo $RowSelect['EffectiveFromDate']?>">
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Exporter<span style="color:red">*</span></label>              			   
				  <select class="form-control select2" name="ExporterID" id="ExporterID">
				 
				<?php while($RowExporter=$StmtExporter->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowExporter['ExpID'];?>">
				<?php echo $RowExporter['ExpName'];?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div>	
				
				
	            </div>
				<HR></HR>
				<H5 style="color: BROWN;">Custom Note </H5>
                <div class="row">
				
              				
				 <div class="form-group col-lg-9" >
                
                  <textarea required type="text" class="form-control" name='CustomNote' id="CustomNote"
				  placeholder="Enter Description" ><?php ECHO $CustomNote;?></textarea>
			   </div>
				
				
				 </div>
				<HR></HR>
				<H5 style="color: BROWN;">Chapter Note </H5>
                <div class="row">
				
				<div class="form-group col-lg-9" >
                  
                  <textarea required type="text" class="form-control" name='ChapterNote' id="ChapterNote"
				  placeholder="Enter Description" > <?php echo $RowSelect['ChapterNote'];?></textarea>

			   </div>
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Shipment Note </H5>
                <div class="row">
				
				<div class="form-group col-lg-9" >
                 
                  <textarea required type="text" class="form-control" name='ShipmentNote' id="ShipmentNote"
				  placeholder="Enter Description" ><?php echo $RowSelect['ShipmentNote']?></textarea>
				
				</div>
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">DrawBack Scheme Note </H5>
                <div class="row">
				
		       <div class="form-group col-lg-9" >
                 
                  <textarea required type="text" class="form-control" name='DrawBackNote' id="DrawBackNote"
				  placeholder="Enter Description"  ><?php echo $RowSelect['DrawBackNote']?></textarea>
				</div>
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Tax Note </H5>
                <div class="row">
				
				<div class="form-group col-lg-9" >
                  
                  <textarea required type="text" class="form-control" name='TaxNote' id="TaxNote"
				  placeholder="Enter Description" ><?php echo $RowSelect['TaxNote']?></textarea>
				
				</div>
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Declaration Note </H5>
                <div class="row">
				
				<DIV class="form-group col-lg-9" >
                  
                  <textarea required type="text" class="form-control" name='DeclarationNote' id="DeclarationNote"
				  placeholder="Enter Description"  ><?php echo $RowSelect['DeclarationNote']?></textarea>
                </div>
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Additional Note </H5>
                <div class="row">
				
				 
				<div class="form-group col-lg-9" >
                 
                  <textarea required type="text" class="form-control" name='AdditionalNote' id="AdditionalNote"
				  placeholder="Enter Description"  ><?php echo $RowSelect['AdditionalNote']?></textarea>
                </div>
				
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Commercial Invoice Note </H5>
                <div class="row">
				
				 
				<div class="form-group col-lg-9" >
                 
                  <textarea required type="text" class="form-control" name='CommercialNote' id="CommercialNote"
				  placeholder="Enter Description" ><?php echo $RowSelect['CommercialNote']?></textarea>
                </div>
				
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Proforma Note </H5>
                <div class="row">
				
				 
				<div class="form-group col-lg-9" >
                  
                  <textarea required type="text" class="form-control" name='ProformaNote' id="ProformaNote"
				  placeholder="Enter Description"  ><?php echo $RowSelect['ProformaNote']?></textarea>
                </div>
				
				
				</div>
				<HR></HR>
				<H5 style="color: BROWN;">Notifications</H5>
                <div class="row">
				
				 
				<div class="form-group col-lg-9" >
                 
                  <textarea required type="text" class="form-control" name='Notifications' id="Notifications"
				  placeholder="Enter Description"  ><?php echo $RowSelect['Notifications']?></textarea>
                </div>
				
				
		
			            
						
						
						
						</div>
				
				
				
			  <div class="col-lg-12 box-footer"  align="right">
			     <?php    if($COUNT==0){ ?>
			  <a target="" href="DescriptionList.php"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-success">Add</button>
				 <?php    } else { ?>
			  <a target="" href="DescriptionList.php"   class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
				 <?php    } ?>

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
		


