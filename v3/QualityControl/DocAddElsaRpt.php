<?php
session_start();
$QueryParms=$_GET['Q']; 
 $QueryParm=$_GET['Q']; 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$ClientID=$_SESSION["ClientID"];



$PoID=$_GET['PoID'];
$Sqlpo="SELECT * FROM $TblScrmPoData where PoID='$PoID'";
$StmtPo=$iconn->query($Sqlpo);
$RowPo=$StmtPo->fetch_array();
$DESTINATION_COUNTRY=$RowPo['DESTINATION_COUNTRY'];


 $SqlCodeList="SELECT * FROM $TblQcCodeList  as CL
	left join $TblAdminPackingStyles as PL on PL.PCKID=CL.PackingStyleID 
	left join $TblQcTracebilityList  as TL on TL.TraceID=CL.TracebilityID 
	WHERE InvID='$PoID' ORDER BY `DayCode` ASC"; 
$StmtCodeList=$iconn->query($SqlCodeList);


$SqlLabUsers="SELECT * FROM $TblQcLabTechnologist where LbuStatus=1 ";
$StmtLabUsers=$iconn->query($SqlLabUsers);
$StmtLabUsers1=$iconn->query($SqlLabUsers);



$PageTitle="IN-HOUSE  ELISA  TEST REPORT  - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";





extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
	
	$SAMPLE_NAME=$SAMPLE_NAME.'('.$TREATE_MENT .')';
	  $sql_org_insert="INSERT INTO $TblQcInHouseAbReport(
	`ReportNo`, `ReportDate`, `TracebilityID`, `SamplingDate`,
	`Representation`, `SampleID`, `AnalysisDate`, `IssuanceDate`,
	`TestedBy`, `VerifiedBy`, `InvID`,`SampleQty`) VALUES (
	
	'$ReportNo','$ReportDate','$TracebilityID','$SamplingDate',
	'$Representation','$SampleID','$AnalysisDate','$IssuanceDate',
	'$TestedBy','$VerifiedBy','$PoID','$SampleQty')"; 
	
	  //echo $sql_org_insert; exit;

	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	if($stmt1){
		legacyAuditLogInsertById($TblQcInHouseAbReport, 'ID', $iid, $_POST, ['Remarks' => 'ELISA Report Created'], 'QualityControl');
				header('Location: DocAddReports.php?Q='.$QueryParm);

}
}
 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
                <?php   include ($IncludesPath."PreLoader.php"); ?>

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
                  <label for="exampleInputEmail1">Traceability</label>
                 				  
				 <select class="form-control select2" name="TracebilityID" id="TracebilityID">				
				<?php while($row=$StmtCodeList->fetch_array()){ ?>				
				<option value="<?php echo $row['TracebilityID'];?>"><?php echo $row['Tracebility'];?></option> 
				<?php } ?>								
				</select>
				
                </div>
						
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Report No</label>
                  <input required type="text" class="form-control" name='ReportNo' id="ReportNo"
				  placeholder="Enter Test Report No" value="<?php echo $RowPo['TEST_REPORT_NUMBER']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Report Date</label>
                  <input required type="date" value="<?php  echo date("Y-m-d");?>"class="form-control" 
				  name='ReportDate' id="ReportDate" 
				  placeholder="Enter Test Report Date" value="<?php echo $RowPo['TEST_REPORT_DATE']?>">
                </div>
				
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Lab Sample ID</label>
                  <input required type="text" class="form-control" name='SampleID' id="SampleID"
				  placeholder="Enter Lab Sample ID" value="<?php echo $RowPo['SampleID']?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Sample Quantity</label>
                  <input required type="text" class="form-control" name='SampleQty' id="SampleQty"
				  placeholder="Enter Sample Quantity" value="<?php echo $RowPo['SampleQty']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Sampling</label>
               <input required type="datetime-local" value="<?php  echo date("Y-m-d");?>"class="form-control" 
			   name='SamplingDate' id="SamplingDate" placeholder="Enter Date of Sampling"
			   value="<?php echo $RowPo['DO_SAMPLING']?>">
   
                </div>		
				
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date of Analysis</label>
                  <input required type="date"value="<?php  echo date("Y-m-d");?>" class="form-control"  
				  name="AnalysisDate" id="AnalysisDate" 
				  placeholder="Enter Date Of Analysis" value="<?php echo $RowPo['DATE_OF_ANALYSIS']?>">
                </div>
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Issuance </label>
                   <input required type="date" value="<?php  echo date("Y-m-d");?>"class="form-control" 
				   name='IssuanceDate' id="IssuanceDate"
				   placeholder="Enter Date of Issuance(dd/mm/yyyy)" value="<?php echo $RowPo['DATE_OF_ISSUANCE']?>">
   
               
                </div>
			   
                
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Name of the Representation/Technologist </label>
                   <input required type="text" class="form-control" name='Representation' id="Representation" 
				   placeholder="Enter name of Technologist - Designation" value="<?php echo $RowPo['NAME_OF_REP']?>">
                </div>
				
				 
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tested By</label>
                   <select required type="text" class="form-control select2" name='TestedBy' id="TestedBy">
				   
				   
				    <?php while ($RowLabUsers=$StmtLabUsers->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"><?php echo $RowLabUsers['LbuName'] ?></option>
				   
				 <?php  }?>
   </select>
               
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>             
				  <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				  <?php while ($RowLabUsers=$StmtLabUsers1->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"><?php echo $RowLabUsers['LbuName'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>     	</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
			  			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				
				Submit</button>
              </div>
			  </form>

</div>
</div>
        
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

