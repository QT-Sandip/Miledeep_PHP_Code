<?php

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$LotId=$_GET['LotId'];
$UserID=$_SESSION['UserID'];
extract($_POST);
extract($_GET);
 $SqlLots="SELECT * FROM `RmLotDetatils` as RLD LEFT JOIN RmUsersList as RUL on RUL.ID= RLD.AgentID 
where  RmLotID=$LotId"; 
    $StmtLots=$iconn->query($SqlLots);  
	$RowLots=$StmtLots->fetch_array();
	$PurchaseCount=$RowLots['PurchaseCount'];
	$AgentName=$RowLots['UserName'];

$PageTitle="R.M Analysis Preview  - " .$RowLots['LotNumber'] ;

$PageTitleFolder="QC";

$SqlOrgnReportCount="SELECT * FROM `QcRmOrgnReport` where  LotID=$LotId";
$StmtOrgnReportCount=$iconn->query($SqlOrgnReportCount);


if(isset($_POST['submit'])){
	
$Count=mysqli_num_rows($StmtOrgnReportCount);
$TimeNow=mktime(date('h')+5,date('i')+30,date('s'));
$PostedDate = date('Y-m-d H:i:s', $TimeNow);	
	if($Count==0){
		$SqlUpdate="INSERT INTO `QcRmOrgnReport`(`LotID`, `PostedDate`, `Remarks`, `PostedUserID`, `SuggestedDeductions`, `ActualDeductions`) 
		VALUES ('$LotId','$PostedDate','$Remarks','$UserID','$labdeductions','$labdeductions')"; 
	}
	
	else{
		$oldOrgnRow = legacyAuditFetchRowById('QcRmOrgnReport', 'LotID', $LotId, 'QualityControl');
	 $SqlUpdate="UPDATE `QcRmOrgnReport`  SET 
	 `Remarks`='$Remarks',
	 `PostedUserID`='$UserID',
	 `SuggestedDeductions`='$labdeductions',
	 `ActualDeductions`='$labdeductions'
	 WHERE LotID='$LotId'"; 
	}
	$StmtUpdate=$iconn->query($SqlUpdate);
	
	if($StmtUpdate){
		if($Count==0){
			$OrgnInsertId = $iconn->insert_id;
			legacyAuditLogInsertById('QcRmOrgnReport', 'LotID', $OrgnInsertId, $_POST, ['Remarks' => 'Orgn Report Added'], 'QualityControl');
		} else {
			legacyAuditLogUpdateById('QcRmOrgnReport', 'LotID', $LotId, $oldOrgnRow, $_POST, ['Remarks' => 'Orgn Report Updated'], 'QualityControl');
		}
		echo "hii";
		
		header('Location:RmReportCreate.php?LotId='.$LotId);
	}
	
	}
 
/*if($_SESSION["UserID"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}*/


	 $SqlRmAbList="SELECT * FROM `QcRmAbReport` WHERE LotID='$LotId'";
    $StmtRmAbList=$iconn->query($SqlRmAbList);  
	$RowRmAbList=$StmtRmAbList->fetch_array();
	
	 

	$SqlRmOrgnDetails="SELECT * FROM `QcRmOrgnDetails` WHERE LotID=$LotId";
    $StmtRmOrgnDetails=$iconn->query($SqlRmOrgnDetails);  
	
	while($RowRmOrgnDetails=$StmtRmOrgnDetails->fetch_array()){
	$ArrayINSP[]=$RowRmOrgnDetails;
	}
	
	
	$ArrayINSP=$ArrayINSP;
	
	$CheckedCount=$RowRmOrgnDetails['CheckedCount'];







$TotalCheckedCount=$ArrayINSP[0]['CheckedCount']+$ArrayINSP[1]['CheckedCount']+$ArrayINSP[2]['CheckedCount'];
$TotalCheckedCount=round($TotalCheckedCount/3);


  $AvgWeight= '1000'/$TotalCheckedCount;


 $BlackGills=$ArrayINSP[0]['BlackGills']* $AvgWeight +$ArrayINSP[1]['BlackGills']* $AvgWeight+$ArrayINSP[2]['BlackGills']* $AvgWeight;

 $BlackGills=ROUND($BlackGills/30,1);
	
	$Soft=$ArrayINSP[0]['Soft']* $AvgWeight+$ArrayINSP[1]['Soft']* $AvgWeight+
	$ArrayINSP[2]['Soft']* $AvgWeight;
$Soft=round($Soft/30,1);
	
	$DroopingHead=$ArrayINSP[0]['DroopingHead']* $AvgWeight +$ArrayINSP[1]['DroopingHead'] * $AvgWeight+ $ArrayINSP[2]['DroopingHead']*
	$AvgWeight;
$DroopingHead=round($DroopingHead/30,1);
	
	$BlackSpot=$ArrayINSP[0]['BlackSpot']* $AvgWeight+$ArrayINSP[1]['BlackSpot'] * $AvgWeight +$ArrayINSP[2]['BlackSpot']* $AvgWeight;
$BlackSpot=round($BlackSpot/30,1);
	
	$Broken=$ArrayINSP[0]['Broken']* $AvgWeight +$ArrayINSP[1]['Broken']* $AvgWeight +$ArrayINSP[2]['Broken']* $AvgWeight;
$Broken=round($Broken/30,1);
	
	$ImproperLegs=$ArrayINSP[0]['ImproperLegs']* $AvgWeight+$ArrayINSP[1]['ImproperLegs']* $AvgWeight+$ArrayINSP[2]['ImproperLegs']* $AvgWeight;
$ImproperLegs=round($ImproperLegs/30,1);


	$Bends=$ArrayINSP[0]['Bends']* $AvgWeight+$ArrayINSP[1]['Bends']* $AvgWeight+$ArrayINSP[2]['Bends']* $AvgWeight;
$Bends=round($Bends/30,1);

//$FIT_FOR_HEAD_ON=$stmt_INSP ['FIT_FOR_HEAD_ON'];
	

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

	
          
    <!--<section class="content">-->
      <!-- Small boxes (Stat box) -->
	          <div class="card">
        <div class="card-body">

    <div id="tdetails" class="table-responsive" >  
		
		
 <!--  <h2 align="center">LOT NUMBER - </h3>  -->

          
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>
	         
                <th  align="center" > Description </font></th>
                <th  align="center" > Standard </font></th>
                <th  align="center" > 1 </font></th>
                <th  align="center" > 2  </font></th>
                <th  align="center" > 3 </font></th>
                <th  align="center"> Average% </font></th>
  
    </thead>


    <tbody>

 <tr> 
           		
               <th  align="center" > Farmer Agent</font></th>
                <th  align="center" >-</font></th>
                <th  align="center" > <?php echo $AgentName ?><th  align="center" >

               
                
               
                
				</tr>
				
				 <tr> 
           		
               <th  align="center" >  Purchase count/Kg </font></th>
               <th  align="center" >  - </font></th>
                <th  align="center" > <?php echo $PurchaseCount ?> </font></th>
               <th  align="center" > <?php echo $PurchaseCount ?> </font></th>
                <th  align="center" > <?php echo $PurchaseCount ?>  </font></th>
                <th  align="center" > <?php echo $PurchaseCount ?>  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Checked count/Kg </font> </th>
                <th  align="center" > -  </font></th>
                 <th  align="center" ><?php echo $ArrayINSP[0]['CheckedCount']?> </font></th>
                 <th  align="center" ><?php echo $ArrayINSP[1]['CheckedCount']?> </font></th>
                 <th  align="center" ><?php echo $ArrayINSP[2]['CheckedCount']?> </font></th>
               <th  align="center" ><?php echo $TotalCheckedCount?> </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" >  Red/Black gill in% </font></th>
                <th  align="center" > 5% </font></th>
                 <th  align="center" ><?php echo   ROUND($ArrayINSP[0]['BlackGills']*$AvgWeight/10,1) ?> </font></th>
                <th  align="center" ><?php echo  ROUND($ArrayINSP[1]['BlackGills']*$AvgWeight/10,1) ?>   </font></th>
                <th  align="center" ><?php echo   ROUND($ArrayINSP[2]['BlackGills']*$AvgWeight/10,1) ?> </font></th>
                 <th  align="center" ><?php echo  $BlackGills ?>  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Soft/Semi Soft/Paper Soft </font></th>
                <th  align="center" >  10% </font></th>
                <th  align="center" ><?php echo  ROUND($ArrayINSP[0]['Soft']*$AvgWeight/10,1) ?> </font></th>
                <th  align="center" ><?php echo  ROUND($ArrayINSP[1]['Soft']*$AvgWeight/10,1) ?>  </font></th>
                 <th  align="center" ><?php echo   ROUND($ArrayINSP[2]['Soft']*$AvgWeight/10,1) ?>  </font></th>
                <th  align="center" ><?php echo   $Soft ?> </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Dropping Head in %</font></th>
                <th  align="center" >  5% </font></th>
                <th  align="center" ><?php  echo ROUND($ArrayINSP[0]['DroopingHead']*$AvgWeight/10,1)?>  </font></th>
                <th  align="center" ><?php echo  ROUND($ArrayINSP[1]['DroopingHead']*$AvgWeight/10,1)?>  </font></th>
                 <th  align="center" ><?php echo   ROUND($ArrayINSP[2]['DroopingHead']*$AvgWeight/10,1)?>  </font></th>
                <th  align="center" ><?php echo  $DroopingHead ?>  </font></th>
                
               
                
				</tr>
				
				
				 <tr> 
           		
                <th  align="center" >  Black spot/Black Dot in %</font></th>
                <th  align="center" >  Nil </font></th>
                 <th  align="center" ><?php echo  ROUND($ArrayINSP[0]['BlackSpot']*$AvgWeight/10,1)?> </font></th>
                 <th  align="center" ><?php echo  ROUND($ArrayINSP[1]['BlackSpot']*$AvgWeight/10,1)?> </font></th>
                 <th  align="center" ><?php echo  ROUND($ArrayINSP[2]['BlackSpot']*$AvgWeight/10,1)?> </font></th>
                 <th  align="center" ><?php echo  $BlackSpot ?> </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" >  Broken/Damages in %</font></th>
                <th  align="center" >  Nil </font></th>
                <th  align="center" > <?php echo ROUND($ArrayINSP[0]['Broken']*$AvgWeight/10,1)?>  </font></th>
                <th  align="center" >  <?php echo  ROUND($ArrayINSP[1]['Broken']*$AvgWeight/10,1)?>   </font></th>
                <th  align="center" > <?php echo ROUND($ArrayINSP[2]['Broken']*$AvgWeight/10,1)?>  </font></th>
                <th  align="center" >  <?php echo  $Broken ?> </font></th>
          
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Improper Legs/Decomposition/Discoloration in %</font></th>
                <th  align="center" > Nil </font></th>
                 <th  align="center" ><?php echo   ROUND($ArrayINSP[0]['ImproperLegs']*$AvgWeight/10,1)?>   </font></th>
                <th  align="center" > <?php echo  ROUND($ArrayINSP[1]['ImproperLegs']*$AvgWeight/10,1)?>  </font></th>
                 <th  align="center" ><?php echo  ROUND($ArrayINSP[2]['ImproperLegs']*$AvgWeight/10,1)?> </font></th>
                 <th  align="center" > <?php echo   $ImproperLegs ?>  </font></th>
          
				</tr>
				
				 <tr> 
           		
                 <th  align="center" >  Bends in %</font></th>
                 <th  align="center" >  10% </font></th>
                <th  align="center" ><?php echo ROUND($ArrayINSP[0]['Bends']*$AvgWeight/10,1)?></th>
                <th  align="center" ><?php echo ROUND($ArrayINSP[1]['Bends']*$AvgWeight/10,1)?></th>
                 <th  align="center" ><?php echo ROUND($ArrayINSP[2]['Bends']*$AvgWeight/10,1)?></font></th>
                <th  align="center" ><?php  echo $Bends ?> </font></th>
          
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Material is fit for Head On Packing or not</font></th>
                 <th  align="center" > -- </font></th>
                
                 <th  align="center" > <?php echo $FIT_FOR_HEAD_ON ?>  </font></th>
              
          
				</tr>

 </tbody>

  </table>
  
  <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			    
				
				  <div class="form-group col-lg-12" >
				   Remarks
				  </div>
                <div class="form-group col-lg-9" >
               
                  <input required type="text" class="form-control" name='Remarks' id="Remarks" value= "<?php echo $ArrayINSP[0]['Remarks'] ?>"
				  placeholder="Enter Remarks">
                </div>
				
				
				  <div class="form-group col-lg-12" >
				   Deductions
				  </div>
                <div class="form-group col-lg-9" >
               
                  <input required type="number" step="any" class="form-control" 
				  name='labdeductions' id="labdeductions" value= ""
				  placeholder="Enter Deductions(if any)">
                </div>
				
			  <div class="col-lg-12 box-body"  align="center">
                <button type="submit"  name='submit'  
				class="btn btn-primary btn-sm btn-flat">Submit</button>
              </div>
</form>

   </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                          


<!--</section>-->
    <!-- /.content -->
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


<script>
  $(function () {
   $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});
  })
</script>

</html>
