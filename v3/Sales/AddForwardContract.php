<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");



$PageTitleFolder="Sales";		
$PageTitle="Forward Contract Details";


$FCID=$_GET['FCID'];
$ExporterID=$_GET['ExporterID'];




$SqlExporter="SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
$StmtExporter=$iconn->query($SqlExporter);
	
	
$SqlFCDtls="SELECT * FROM ScrmForwardContractsDetails WHERE FCID='$FCID'";
$StmtFCDtls=$iconn->query($SqlFCDtls);
$RowFCDtls = $StmtFCDtls->fetch_assoc();	

 $Count=mysqli_num_rows($StmtFCDtls);
if($FCID!=''){
  $FinYear=$RowFCDtls['FinYear'];
 $Month=$RowFCDtls['Month'];
}
 else{
	$CurrentMonth=date('m');	

if($CurrentMonth >3){	
$FinYear=$Currentyear+1;	
}else{
$FinYear=$Currentyear;
}
 }


extract($_POST);
extract($_GET);




if(isset($_POST['submit'])){	


		

//echo  $Count;exit;
//exit;		
		if($TotalRate==''){
			echo '<script language="javascript" type="text/javascript"> 
                	 alert("Total Rate is Invalid");
					 </script>';
					
		}else{
			  if($Count==0){
   	/* $SqlInsert="INSERT INTO `ScrmForwardContractsDetails`(`ExpID`,
		`FCNumber`, `BookingDt`, `NetValue`, `UtilizedValue`, 
		`BalancedValue`, `FinYear`, `Month`, `StartDt`, `EndDt`,
		`SpotRte`, `PremiumRate`, `BranchMargin`, `TotalRate`,`Status`)		   
		VALUES ('$ExpID','$FCNumber','$BookingDt','$NetValue','$UtilizedValue','$BalancedValue','$FinYear','$Month','$StartDt','$EndDt','$SpotRte','$PremiumRate','$BranchMargin',
		'$TotalRate','1')"; */
		
		$SqlInsert="INSERT INTO `ScrmForwardContractsDetails`(`ExpID`,
		`FCNumber`, `BookingDt`, `NetValue`, `UtilizedValue`, 
		`BalancedValue`, `FinYear`, `Month`, `StartDt`, `EndDt`,
		`SpotRte`, `PremiumRate`, `BranchMargin`, `TotalRate`,`Status`)		   
		VALUES ('$ExpID','$FCNumber','$BookingDt','$NetValue','$UtilizedValue','$NetValue','$FinYear','$Month','$StartDt','$EndDt','$SpotRte','$PremiumRate','$BranchMargin',
		'$TotalRate','1')";
			  }
			  
			  else{
				 $existingForwardContract = legacyAuditFetchRowById('ScrmForwardContractsDetails', 'FCID', $FCID, 'Sales');
				 $SqlInsert="UPDATE ScrmForwardContractsDetails 
				SET
				FCNumber='$FCNumber',
				ExpID='$ExpID',
				BookingDt='$BookingDt',
				NetValue='$NetValue',
				UtilizedValue='$UtilizedValue',
				BalancedValue='$BalancedValue',
				FinYear='$FinYear',
				Month='$Month',
				StartDt='$StartDt',
				EndDt='$EndDt',
				SpotRte='$SpotRte',
				PremiumRate='$PremiumRate',
				BranchMargin='$BranchMargin',
				TotalRate='$TotalRate' WHERE FCID='$FCID'";  
				  
			  }
			
			$StmtInsert=$iconn->query($SqlInsert);
		
		
		if($StmtInsert){
			if($Count==0){
				$insertedForwardContractId = $iconn->insert_id;
				legacyAuditLogInsertById('ScrmForwardContractsDetails', 'FCID', $insertedForwardContractId, $_POST, array(
					'ReferenceNo' => $insertedForwardContractId,
					'Remarks' => 'Forward contract created',
				), 'Sales');
			}else{
				legacyAuditLogUpdateById('ScrmForwardContractsDetails', 'FCID', $FCID, $existingForwardContract, $_POST, array(
					'ReferenceNo' => $FCID,
					'Remarks' => 'Forward contract updated',
				), 'Sales');
			}
					
		echo '<script language="javascript" type="text/javascript"> 
                	 alert("Successfully Updated");

                window.location = "ForwrdContrctList";
        </script>';
					
		}else{
			
			
			echo '<script language="javascript" type="text/javascript"> 
                	 alert("Unable To  Save Data");
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
								
								<form id="form1" role="form" 
								onsubmit="return validateForm()"method="post">
                                    <div class="card-body">
									
										<div class="row">
										
										
				
				
				

				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Company<span style="color:red">*</span></label>              			   
				  <select class="form-control select2" name="ExpID" id="ExpID">
				 
				<?php while($RowExporter=$StmtExporter->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowExporter['ExpID'];?>">
				<?php echo $RowExporter['ExpName'];?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div>			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Booking Date<span style="color:red">*</span></label>
			 <input required type="date" onkeydown="return false" class="form-control" name='BookingDt' id="BookingDt" 
				  placeholder="Enter Booking Date" value="<?php echo $RowFCDtls['BookingDt']?>">	
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Forward Contract Number<span style="color:red">*</span></label>
			 <input required type="text" class="form-control" name='FCNumber' id="FCNumber" 
				  placeholder="Enter Forward Contract Number" 
				  value="<?php echo $RowFCDtls['FCNumber']?>">	
                </div>
					
							
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Value<span style="color:red">*</span></label>
				<input required type="number" class="form-control" name='NetValue' id="NetValue"  placeholder="Enter Value" 
				value="<?php echo $RowFCDtls['NetValue']?>">	
                </div>
				
				<div class="form-group col-lg-3">
                	  <label>Period </label>
                   <select class="form-control select2" name="FinYear" id="FinYear">
                    <?php	 						
             for($Startyear; $Startyear <= $endYear+3; $Startyear++) { 
						$year=($Startyear-1).'-'.($Startyear); ?>
                                                            
           <option  <?php  if($Startyear == $FinYear){ ?> selected="selected" 
						<?php   }   	?>			
			value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>                                             
							<?php }   ?>
                   <select>
				  </div>
				  
				 
				 <div class="col-lg-3">                                  
	               <div class="form-group">			
                 <label for="exampleInputEmail1">Month </label>
            <select class="form-control select2" name="Month" id="Month">
				   <?php
				   $startMonth = 3; // March
                    for ($i = 0; $i < 12; $i++) {
                    //   $time = strtotime(sprintf('%d months', $i), $start);   
		            //    $label = date('F', $time);   
                    //    $value = date('n', $time);

					$value = ($startMonth + $i - 1) % 12 + 1;
    				// Create DateTime object for monthNum to get month name
        			$dateObj = DateTime::createFromFormat('!m', $value);
    				$label = $dateObj->format('F'); // Full month name
					   
					   ?>
                   <option <?php if($RowFCDtls['Month']==$value){ ?> selected="selected" <?php } ?>
				   value="<?php echo $value; ?>"><?php echo $label; ?></option>
                 <?php  }  ?>                                    
				  <select>
				 
				  </div>
				  </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Date<span style="color:red">*</span></label>
				<input required type="Date" onkeydown="return false" class="form-control" name='StartDt' id="StartDt" 
				  placeholder="Enter Collection Rate" 
				  value="<?php echo $RowFCDtls['StartDt']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">End Date<span style="color:red">*</span></label>
				<input required  type="Date" onkeydown="return false" class="form-control" name='EndDt' id="EndDt"   placeholder="Enter End Dt" 
				value="<?php echo $RowFCDtls['EndDt']?>">
                </div>

					
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Spot Rate<span style="color:red">*</span></label>
				<input required  type="decimal" class="form-control"  name='SpotRte' id="SpotRte"   placeholder="Rate" 
				value="<?php echo $RowFCDtls['SpotRte']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Premium Rate</label>
				<input  type="decimal" class="form-control" name='PremiumRate' id="PremiumRate"  placeholder="PremiumRate"
				value="<?php echo $RowFCDtls['PremiumRate']?>">
                </div>
				

					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Branch Margin<span style="color:red">*</span></label>
				<input required type="decimal" class="form-control" 
				oninput="BranchMargin" name='BranchMargin' id="BranchMargin" placeholder="Enter Branch Margin" 
				value="<?php echo $RowFCDtls['BranchMargin']?>">
                </div>

				
					

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Total Rate<span style="color:red">*</span></label>
				<input required readonly type="text" class="form-control" name='TotalRate' id="TotalRate"  placeholder="Total Rate" value="<?php echo number_format($RowFCDtls['TotalRate'],4)?>">			
                </div>

						
				<div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Balance Value</label>
				<input readonly type="decimal" class="form-control" style="background-color:lightgrey;" name='BalancedValue' id="BalancedValue" placeholder="Balanced Value" 
				value="<?php echo $RowFCDtls['BalancedValue']?>">
                </div>
				
				
				<div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Utilized Value </label>
				<input readonly type="decimal" class="form-control" style="background-color:lightgrey;" name='UtilizedValue' id="UtilizedValue"  placeholder="Utilized Value"
				value="<?php echo $RowFCDtls['UtilizedValue']?>">
                </div>
				
				</div>
				
				
				
				
					<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
	<a target="" href="ForwrdContrctList.php" class="btn btn-danger  "> Close</a>
    <button  type="submit" name="submit"  id='BtnSub' class="btn btn-primary">Add</button>
			 
              </div>
					<?php } 
					
					else {	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="ForwrdContrctList.php"
               class="btn btn-danger"> Close</a>
  		
               <button type="submit" name="submit"  class="btn btn-primary">Update</button>
							 


              </div>
					<?php } ?>

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



<script>


function validateForm(){
var TotalRate =$("#TotalRate").val();
	if(TotalRate==''){
	alert('Total Rate Required ');
	return false;
	}else{
	return true;
	}		
}

		$("#FCNumber").change(function(){
			
			checkFcNumber();
		});

	  $("#SpotRte").change(function(){
			calculateSum();
		});
		$("#PremiumRate").change(function(){
			calculateSum();
		});
		$("#BranchMargin").change(function(){
			calculateSum();
		});
	

	function calculateSum() {
		
		var SpotRate =$("#SpotRte").val();
		var PremiumRate =$("#PremiumRate").val();
		var BranchMargin =$("#BranchMargin").val();
		
	//	alert(SpotRate);
		
		
		if(SpotRate!='' && PremiumRate!='' && BranchMargin!=''){
		
		var Values=parseFloat(SpotRate)+parseFloat(PremiumRate)-parseFloat(BranchMargin);
		//alert(Values);
		}
		  
		if(!isNaN(Values)){
			$("#TotalRate").val(Values.toFixed(4));
		}else{
			$("#TotalRate").val("");
		}
		}
		
		


function checkFcNumber(){
	  
	  var FCNumber=$('#FCNumber').val(); 
	
	if(FCNumber < 15){
		return;
	}
	  
	  
	  
	  
	  $.ajax({
		  url:'Ajax/AjaxFcNumberCheck.php',
		  type:'POST',
		  data:{'FCNumber':FCNumber},
		  success:function(data){

			  if(data!=''){
			  alert(data);
			  $("#FCNumber").val("");
			  }
			  
			}
			});			  
  }

</script>
		
