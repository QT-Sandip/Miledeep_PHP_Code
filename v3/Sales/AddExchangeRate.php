<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($cp."Includes/IncludePage.php");



$PageTitle="Add Exchange Rates";

$PageTitleFolder="Sales";

 $UserID=$_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$TodayDate=date('Y-m-d');
$ExchangeRateId=$_GET['id'];
  
extract($_POST);
extract($_GET);   

$SqlRate="SELECT * FROM `ScrmExchangeRates` WHERE ExID='$ExchangeRateId'";
$StmtRate=$iconn->query($SqlRate);
$RowRate=$StmtRate->fetch_array();

	$SqlCurrency="SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
	$StmtCurrency=$iconn->query($SqlCurrency);


if(isset($_POST['submit'])){
	
	$SqlCount="SELECT * FROM `ScrmExchangeRates` WHERE FromDate='$FromDate' and CurrencyType='$CurrencyType'";
	$StmtCount=$iconn->query($SqlCount);
	$Count=mysqli_num_rows($StmtCount);
	if($ExchangeRateId==''){
		if($Count==0){
		$SqlInsert="INSERT INTO `ScrmExchangeRates`(`UserID`,`CurrencyType`, 
		`ExchangeRate`, `FromDate`) VALUES('$UserID','$CurrencyType','$ExchangeRate',
		'$FromDate')"; 
		$StmtInsert=$iconn->query($SqlInsert);


if($StmtInsert){ 
	$insertedExchangeRateId = $iconn->insert_id;
	legacyAuditLogInsertById('ScrmExchangeRates', 'ExID', $insertedExchangeRateId, $_POST, array(
		'ReferenceNo' => $insertedExchangeRateId,
		'Remarks' => 'Exchange rate created',
	), 'Sales');
	
echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "ExchangeRatesList.php";
		</script>';
	
 } 
	}
	
	else{
		echo "<script>alert('Exchange already exists to this date');</script>";
	}
	}	
//}
else{
	$existingExchangeRate = legacyAuditFetchRowById('ScrmExchangeRates', 'ExID', $ExchangeRateId, 'Sales');
	$SqlInsert="UPDATE `ScrmExchangeRates` SET `UserID`='$UserID',
	`CurrencyType`='$CurrencyType',
	`ExchangeRate`='$ExchangeRate',
	`FromDate`='$FromDate',`ToDate`='$ToDate'
	WHERE `ExID`='$ExchangeRateId'";	
	$StmtInsert=$iconn->query($SqlInsert);	
	
	if($StmtInsert){ 
		legacyAuditLogUpdateById('ScrmExchangeRates', 'ExID', $ExchangeRateId, $existingExchangeRate, $_POST, array(
			'ReferenceNo' => $ExchangeRateId,
			'Remarks' => 'Exchange rate updated',
		), 'Sales');

		$ArgumentsArray[PostedUserId]=$UserID;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ExchangeRateId;
            postUserEditLog($ArgumentsArray);
	
echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "ExchangeRatesList.php";
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
										
										
										
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Currency<span style="color:red">*</span></label>
				  <select  required class="form-control select2" name="CurrencyType" id="CurrencyType">
                  <option value="">Select Currency</option>
                  
				   <?php while ($RowCurrency=$StmtCurrency->fetch_array()){ ?>
				     <option 
					 <?php if($RowRate['CurrencyType']==$RowCurrency['CurrencyCode']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowCurrency['CurrencyCode'] ?>">
					 <?php echo $RowCurrency['CurrencyName'].' - 
					 '.$RowCurrency['CurrencyCode'] .'('.$RowCurrency['CurrenySymbol'].')'?></option>
				   
				 <?php  }?>	 
				  </select>
                </div>

    
                <div class="form-group col-lg-3"  >
                  <label for="exampleInputEmail1">Exchange Rate<span style="color:red">*</span></label>
                  <input required type="text" 
				  class="form-control" name='ExchangeRate' id="ExchangeRate"
				  placeholder="Enter Exchange Rate" value="<?php echo $RowRate['ExchangeRate'];?>" >
                </div> 
				
				
				
				<div class="form-group col-lg-3"  >
                  <label for="exampleInputEmail1">Applicable Date<span style="color:red">*</span></label>
                  <input required type="date"  max="<?php echo $TodayDate; ?>"
				  class="form-control" name='FromDate' id="FromDate"
				  placeholder="Enter Date" value="<?php echo $RowRate['FromDate'];?>">
                </div> 
				
				
			 
                </div>
				
				
				

				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="ExchangeRatesList.php" class="btn btn-danger  "> Close</a>	
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
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
</html>				

