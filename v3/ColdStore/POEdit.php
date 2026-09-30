 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
  $ID=$_GET['ID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	
$PageTitle="Edit PO";
$PageTitleFolder="ColdStore";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$PostedDate=date('Y-m-d');


$sqlConsigneeDtls="SELECT * FROM `ScrmConsigneeDetails` "; 
$StmtConsignee=$iconn->query($sqlConsigneeDtls);

$SqlPoData="SELECT * FROM `CsPoData` WHERE PoID='$ID'"; 
$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();

$SqlCsOptions="SELECT * FROM `CsGeneralOptions`";
$StmtCsOptions=$iconn->query($SqlCsOptions);
$ARY_OPTIONS=array();
WHILE($RowCsOPtions=$StmtCsOptions->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$RowCsOPtions;
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
                  <label for="exampleInputPassword1">Internal Lot No(Optional)</label>
                  <input required  type="text" class="form-control" name="InternalLotNo" id="InternalLotNo" 
				  placeholder="Enter Internal Lot Number" value="<?php echo $RowPoData['InternalLotNo']?>">
                </div>

				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Number</label>
                  <input required  type="text" class="form-control" name="PoNumber" id="PoNumber" 
				  value="<?php echo $RowPoData['PoNumber']?>"
				  placeholder="Enter Po Number" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Date</label>
                  <input required  type="date" class="form-control" name="PoDate" id="PoDate"
				value="<?php echo $RowPoData['PoDate']?>"  max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>" placeholder="Enter Date" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Invoice Number</label>
                  <input required  type="text" class="form-control" name="InvoiceNumber" id="InvoiceNumber" 
				value="<?php echo $RowPoData['InvoiceNumber']?>"  placeholder="Enter Invoice Number" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Invoice Date</label>
                  <input required  type="date" class="form-control" name="InvoiceDate" id="InvoiceDate"
			value="<?php echo $RowPoData['InvoiceDate']?>"	  max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>" placeholder="Enter Date" >
                </div>
				

				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Consignee/Buyer Name</label>
				  <select class="form-control select2" name="ConsigneeID" id="ConsigneeID">
				  <option value="">Select Buyer</option>
				   <?php while ($RowConsignee=$StmtConsignee->fetch_array()){ ?>
				     <option 
				<?php if($RowPoData['ConsigneeID']==$RowConsignee['ConID']){	?> selected=="selected" <?php } ?>
					 value="<?php echo $RowConsignee['ConID'] ?>">
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
			     <option <?php if($RowPoData['DestinationType']==$VALUE){ ?> selected="selected" <?php }?>
			    value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
				<select>
				</div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Destination Port</label>
                  <input required  type="text" class="form-control" name="DestinationPort" id="DestinationPort" 
				value="<?php echo $RowPoData['DestinationPort']?>"  placeholder="Enter Destination Port" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Agent Name</label>
                  <input required  type="text" class="form-control" name="AgentID" 
				  id="AgentID" placeholder="Enter Agent Name" value="<?php echo $RowPoData['AgentID']?>" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Ship by Date</label>
                  <input required  type="date" class="form-control" name="ShipmentDate" id="ShipmentDate"
				  max="<?php echo $TodayDate; ?>" value="<?php echo $RowPoData['ShipmentDate']?>" placeholder="Enter Ship by Date" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Brand</label>
                  <input required  type="text" class="form-control" name="Brand" id="Brand"  placeholder="Enter Brand" 
				  value="<?php echo $RowPoData['Brand']?>">
                </div>

		
                <div class="form-group col-sm-12"  align="right">
		
				<button type="submit"  name='submit' 
				id="CL"class="btn btn-primary">Update</button>			
			
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

<Script>

$('document').ready(function(){

    	$(document).on('click','#CL',function(e){
		e.preventDefault();
        $('#CL').attr('disabled','disabled');


            var InternalLotNo= $('#InternalLotNo').val();
			var PoNumber= $('#PoNumber').val();
			var PoDate= $('#PoDate').val();
			var InvoiceNumber= $('#InvoiceNumber').val();
			var InvoiceDate= $('#InvoiceDate').val();
			var ConsigneeID= $('#ConsigneeID').val();
			var DestinationType= $('#DestinationType').val();
			var DestinationPort= $('#DestinationPort').val();
			var AgentID= $('#AgentID').val();
			var ShipmentDate= $('#ShipmentDate').val();
			var Brand= $('#Brand').val();
			var POID= '<?PHP ECHO $ID ?>'

             
			var arr = {
				
				'InternalLotNo': InternalLotNo,
				'PoNumber': PoNumber,
				'PoDate': PoDate,
				'InvoiceNumber': InvoiceNumber,
				'InvoiceDate': InvoiceDate,
				'ConsigneeID': ConsigneeID,
				'DestinationType': DestinationType,
				'DestinationPort': DestinationPort,
				'AgentID': AgentID,
				'ShipmentDate': ShipmentDate,
				'Brand': Brand,
				'POID': POID,
				'SOURCE': 'WEB'


				};
			$.ajax({
		  url:'<?php echo $ApiUrl ?>?f=EditPo',
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(arr),
		  success:function(data){
			 console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			 if(obj.error==null){
				$('#CL').removeAttr('disabled');
				alert(obj.msg);

				location.reload();

				}else{
				alert(obj.error);
				}

			}});


    	});
    });

</script>

