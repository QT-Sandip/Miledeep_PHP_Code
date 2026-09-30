 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
$LotID=$_GET['LotID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
IF($LotID==''){
	
$PageTitle="Create PO";
}ELSE{
	
$PageTitle="Edit PO";	
}
$PageTitleFolder="ColdStore";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$today_date=date('Y-m-d');


//ORIGINAL

$SqlCsOptions="SELECT * FROM `CsGeneralOptions`";
$StmtCsOptions=$iconn->query($SqlCsOptions);
 
$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);
 
$ARY_OPTIONS=array();
WHILE($RowCsOPtions=$StmtCsOptions->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$RowCsOPtions;
}

/*echo $SqlRmUsersList="SELECT * FROM `RmUsersList`"; exit;
$StmtRmUsersList=$iconn->query($SqlRmUsersList);


$AryUserList=array();
while($RowRmUsersList=$StmtRmUsersList->fetch_array()) {
$AryUserList[]=$RowRmUsersList;
}*/

$TimeFormat = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
$TimeStamp=$TimeFormat->format("Y-m-d  H:i:s"); 

extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){ 

	$InternalLotNo=$PoNumber;
	

$InsertPoData="
INSERT INTO `CsPoData`(`InvoiceNumber`, `InvoiceDate`, `InternalLotNo`, `InvoiceValue`, `PoNumber`, `PoDate`, `DestinationType`, `Brand`, `DestinationCountry`, `DestinationPort`, `ProcessorID`, `ConsigneeID`,  `AgentID`, `ShipmentDate`, `ShippedOutDate`, `GLAZE_TYPE`, `PoStatus`,`CreatedDateTime`, `PostedUserID`) 
VALUES('$InvoiceNumber','$InvoiceDate','$InternalLotNo','$InvoiceValue','$PoNumber','$PoDate','$DestinationType','$Brand','$DestinationCountry','$DestinationPort','$ProcessorID','$ConsigneeID','$AgentID','$ShipmentDate','$ShippedOutDate','$GLAZE_TYPE','$PoStatus','$TimeStamp','$UserID')";
	
	//echo  $InsertPoData;
	
	 	$ExePoData=$iconn->query($InsertPoData);
	    $CsPoId = $iconn->insert_id;		
	//exit;		
	
	header("Location: AddPOGradeData.php?id=".$CsPoId);
	
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

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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
                  <label for="exampleInputEmail1">Station</label>
				  
				    <select class="form-control select2" name="UnitID" id="UnitID">
			          <?php foreach($ARY_OPTIONS as $value){
					  if($value['OptionType']=='CS_STATIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['CsOptID'];
				         ?>
			         <option <?php if($UNIT==$VALUE){ ?> selected="selected" <?php }?>
			          value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
				<select>
				</div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Internal Lot No(Optional)</label>
                  <input required  type="text" class="form-control" name="InternalLotNo_1" id="InternalLotNo_1" 
				  placeholder="Enter Internal Lot Number" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Number</label>
                  <input required  type="text" class="form-control" name="PoNumber" id="PoNumber" 
				  placeholder="Enter Po Number" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Date</label>
                  <input required  type="date" class="form-control" name="PoDate" id="PoDate"
				  max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>" placeholder="Enter Date" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Invoice Number</label>
                  <input required  type="text" class="form-control" name="InvoiceNumber" id="InvoiceNumber" 
				  placeholder="Enter Invoice Number" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Invoice Date</label>
                  <input required  type="date" class="form-control" name="InvoiceDate" id="InvoiceDate"
				  max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>" placeholder="Enter Date" >
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Consignee/Buyer Name</label>
				  <select class="form-control select2" name="ConsigneeID" id="ConsigneeID">
				  <option value="">Select Buyer</option>
				   <?php while ($RowConsignee=$StmtConsignee->fetch_array()){ ?>
				     <option value="<?php echo $RowConsignee['ConID'] ?>">
					 <?php echo $RowConsignee['ConsigneeName'].' - 
					 '.$RowConsignee['ConsigneeBrand'] ?></option>
				   
				 <?php  }?>	  
				  </select>
				
				</div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Type of Destination</label>
				  
				<select class="form-control select2" name="DestinationType" id="DestinationType">
			    <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='DestinationType'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['CsOptID'];
				?>
			     <option <?php if($UNIT==$VALUE){ ?> selected="selected" <?php }?>
			    value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
				<select>
				</div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Destination Port</label>
                  <input required  type="text" class="form-control" name="DestinationPort" id="DestinationPort" 
				  placeholder="Enter Destination Port" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Agent Name</label>
                  <input required  type="text" class="form-control" name="AgentID" 
				  id="AgentID" placeholder="Enter Agent Name" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Ship by Date</label>
                  <input required  type="date" class="form-control" name="ShipmentDate" id="ShipmentDate"
				  max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>" placeholder="Enter Ship by Date" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Brand</label>
                  <input required  type="text" class="form-control" name="Brand" id="Brand"  placeholder="Enter Brand" >
                </div>

				</div>
				
              <!-- /.box-body -->


			<div class="col-xs-12 box-footer"  align="right">
		
				<button type="submit" style="background-color: #008000" name='submit' class="btn btn-primary">Create PO</button>			
			
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
