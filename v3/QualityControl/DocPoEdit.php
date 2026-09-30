<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");


$PageTitleFolder="Quality Control";
$PageTitle="Proforma Invoice Edit";

include "Constants.php";
$PoID=$_GET['PoID'];

$SqlGetPoData="select * from ScrmPoData where PoID='$PoID' ";
$StmtGetPoData=$iconn->query($SqlGetPoData);
$RowPoData=$StmtGetPoData->fetch_array();
$BUYER_ID=$RowPoData['CONSIGNEE_ID'];
$Country=$RowPoData['DestinationCountry'];

//ECHO $SqlGetPoData;
//EXIT;

$SqlBuyer="SELECT * FROM ScrmConsigneeDetails";
$StmtBuyer1=$iconn->query($SqlBuyer);

$SqlExporterDetails="SELECT * FROM AdminExporterDetails";
$StmtExporterDetails=$iconn->query($SqlExporterDetails);

$SqlPorts="SELECT * FROM ScrmPortCities where Country='$Country'"; 
$StmtPorts=$iconn->query($SqlPorts);

    $SqlCountryList="SELECT * FROM ScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 
	
	while($RowPo=$StmtPorts->fetch_array()){
		$AryPorts[]=$RowPo;
	}
	
	
extract($_POST);
extract($_GET);


if(isset($_POST['submit'])){

	$oldRowPo = legacyAuditFetchRowById('ScrmPoData', 'PoID', $PoID, 'QualityControl');

		
	 $SqlPOEdit="
	UPDATE ScrmPoData SET 
	InvNo='$InvNo',
	InvDate='$InvDate',
	ExporterID='$ExporterID',
	ProcessorID='$ProcessorID',
	LotNo='$LotNo',
	SciName='$SciName',	
	PoNo='$PoNo',
	DestinationCountry='$DestinationCountry',
	FinalDestination='$FinalDestination',
	PoDate='$PoDate'
	
	WHERE PoID='$PoID'";
	
	// echo $SqlPOEdit; exit;
	 

	$stmt1=$iconn->query($SqlPOEdit);
	legacyAuditLogUpdateById('ScrmPoData', 'PoID', $PoID, $oldRowPo, $_POST, ['Remarks' => 'Updated PO Data'], 'QualityControl');
	//$iid = $iconn->insert_id;
	
	
	
	if($stmt1){	
				
	header('Location: DocPoList.php');
	}
}

 ?>


<!DOCTYPE html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

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
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">

			 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Name of the Processor</label>
               
				   <select class="form-control select2" name="ProcessorID" id="ProcessorID">
				   
				   
				  <?php while($row_add=$StmtExporterDetails->fetch_array()) { 
				
				$ary_exp[]=$row_add;?>  
				
				<option Value="<?php echo $row_add['ExpID'];?>" <?php if( $row_add['ExpID']==$RowPoData['ProcessorID'])
					  echo 'selected="selected"'; ?>><?php echo $row_add['EiaExpName'].' - '.$row_add['ExpApprovalNo'].' - '.$row_add['ExpDetails'];?></OPTION>
				
				<?php } ?>
				<select>   
                </div>
				
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Name of the Exporter</label>
               
				   <select class="form-control select2" name="ExporterID" id="ExporterID">
				  <?php foreach($ary_exp as $value ) { ?>  
				
				<option Value="<?php echo $value['ExpID'];?>" <?php if( $value['ExpID']==$RowPoData['ExporterID'])
					  echo 'selected="selected"'; ?>> <?php echo $value['EiaExpName'].' - '.$value['ExpApprovalNo'].' - '.$row_add['ExpDetails'];?></OPTION>
				
				<?php } ?>
				<select>   
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Invoice Number</label>
                  <input required type="text" class="form-control" name='InvNo'
				  id="InvNo" placeholder="Enter Invoice No" value="<?php echo  $RowPoData['InvNo'] ?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Invoice Date</label>
                  <input required type="date"  class="form-control"
				  name='InvDate' id="InvDate" placeholder="Enter " value="<?php echo  $RowPoData['InvDate'] ?>">
                </div>
			
			   <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O No</label>
                  <input readonly type="text" class="form-control" name='PoNo' id="PoNo"
				  placeholder="Enter P.O Number"
				  value="<?php echo  $RowPoData['PoNo'] ?>">
                </div> 
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O Date</label>
                  <input required type="date"  class="form-control" name='PoDate' 
				  id="PoDate" placeholder="Enter P.O Date"  value="<?php echo  $RowPoData['PoDate'] ?>">
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Lot Number</label>
                  <input required type="text"  class="form-control" name='LotNo' 
				  id="LotNo" placeholder="Enter Lot Number"  value="<?php echo  $RowPoData['LotNo'] ?>">
                </div>
			  
			  
			 
			  
                <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Scientific Name</label>
				  <select class="form-control select2" name="SciName" id="SciName">
				  <option value="PENAEUS VANNAMEI" <?php if($RowPoData['SciName']=='PENAEUS VANNAMEI')
					  echo 'selected="selected"'; ?>>PENAEUS VANNAMEI</option>
				   <option value="LITOPENAEUS VANNAMEI" <?php if($RowPoData['SciName']=='LITOPENAEUS VANNAMEI')
					  echo 'selected="selected"'; ?>>LITOPENAEUS VANNAMEI</option>
					 
		
				<select>               
                  
                </div> 
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Destination Country</label>
                 
				  
				  <select required class="form-control select2" name="DestinationCountry" id="DestinationCountry">
				  <option value="">Select Country</option>
				   <?php while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
				     <option  value="<?php echo $RowCountry['id'] ?>" <?php if($RowCountry['id']==$RowPoData['DestinationCountry']) echo 'selected="selected"'; ?>>
					 <?php echo strtoupper($RowCountry['name'])?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Final Destination</label>
                  <input required type="text"  class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination"  value="<?php echo  $RowPoData['FinalDestination'] ?>">
                </div>
               
				
		        	</div> 
				
				<div class="col-xs-12 box-footer"  align="right">
				<a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>

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

<script>

$("document").ready(function(){
	  /*$("#ConsigneeID").select2();
	  $("#BuyerID").select2();
	  $("#DestinationCountry").select2();
	  $("#PortOfDischarge").select2();*/

    $("#file").change(function() {
        alert('changed!');
    });
	
	

	
});
  	

</script>

