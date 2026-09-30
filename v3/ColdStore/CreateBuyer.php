 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
  $ID=$_GET['ID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
IF($ID==''){
	
$PageTitle="Create Buyer";
}ELSE{
	
$PageTitle="Edit Buyer";	
}
$PageTitleFolder="ColdStore";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$PostedDate=date('Y-m-d');


$sqlConsigneeDtls="SELECT * FROM `ScrmConsigneeDetails`  WHERE ConID='$ID'"; 
$stmConsigneeDtls=$iconn->query($sqlConsigneeDtls);
$ROWConsigneeDtls=$stmConsigneeDtls->fetch_array();




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
                  <label for="exampleInputPassword1">Buyer Name</label>
                  <input required  type="text" class="form-control" name="BuyerName" id="BuyerName" placeholder="Enter Buyer Name"
                   value="<?php echo $ROWConsigneeDtls['ConsigneeName']?>"				  >
                </div>
				
				<div class="form-group col-sm-6">  
                  <label for="exampleInputPassword1">Buyer Address</label>
                  <input required  type="text" class="form-control" name="BuyerAddress" id="BuyerAddress" placeholder="Enter Buyer Address"
                   value="<?php echo $ROWConsigneeDtls['ConsigneeAddress']?>"				  >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Buyer Country</label>
                  <input required  type="text" class="form-control" name="BuyerCountry" id="BuyerCountry" placeholder="Enter Buyer Country" 
				  value="<?php echo $ROWConsigneeDtls['ConsigneeCountry']?>">
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Destination Port</label>
                  <input required  type="text" class="form-control" name="DestinationPort" id="DestinationPort" placeholder="Enter Lot Number"
                  value="<?php echo $ROWConsigneeDtls['ConsigneePort']?>"				  >
                </div>
						
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Brand Names</label>
                  <input required  type="text" class="form-control" name="BrandNames" id="BrandNames"   placeholder="Enter Brand Names"
                  value="<?php echo $ROWConsigneeDtls['ConsigneeBrand']?>"				  >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Fishing Permit Number</label>
                  <input required  type="text" class="form-control" name="FishingPermitNumber" id="FishingPermitNumber"
                    value="<?php echo $ROWConsigneeDtls['ConsigneeFishingNo']?>"				  
				  placeholder="Enter Fishing Permit Number" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Product Inspector</label>
                  <input   type="text" class="form-control" name="ProductInspector" id="ProductInspector" 
				  placeholder="Enter Product Inspector Name" value="<?php echo $ROWConsigneeDtls['ConsigneeProInsp']?>" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Quality Inspector</label>
                  <input   type="text" class="form-control" name="QualityInspector" id="QualityInspector" 
				  placeholder="Enter Quality Inspector Name" value="<?php echo $ROWConsigneeDtls['ConsigneeQltyInsp']?>" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Document Inspector</label>
                  <input   type="text" class="form-control" name="DocumntInspector" id="DocumntInspector" 
				  placeholder="Enter Documnt Inspector Name" value="<?php echo $ROWConsigneeDtls['ConsigneeDocInsp']?>">
                </div>
				

				
				<div class="form-group col-sm-6">  
                  <label for="exampleInputPassword1">Any Special Specifications</label>
                  <input   type="text" class="form-control" name="SpecialInstructions" id="SpecialInstructions" 
				  placeholder="Enter Special Instructions(if any)" value="<?php echo $ROWConsigneeDtls['ConsigneeSplIns']?>">
                </div>
				
                <div class="form-group col-sm-12"  align="right">
	<?php	if($ID==''){ ?>
				<button type="submit"  name='submit' 
				id="CL"class="btn btn-primary">Add </button>			
	<?php	} else { ?>
	<button type="submit"  name='submit' 
				id="Edit"class="btn btn-primary">Update</button>
	<?php	}  ?>
				
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

		
            var BuyerName= $('#BuyerName').val();
			var BuyerAddress= $('#BuyerAddress').val();
			var BuyerCountry= $('#BuyerCountry').val();
			var DestinationPort= $('#DestinationPort').val();
			var BrandNames= $('#BrandNames').val();
			var FishingPermitNumber= $('#FishingPermitNumber').val();
			var ProductInspector= $('#ProductInspector').val();
			var QualityInspector= $('#QualityInspector').val();
			var DocumntInspector= $('#DocumntInspector').val();
			var SpecialInstructions= $('#SpecialInstructions').val();
            var PostedUserID= '<?PHP ECHO $ID ?>'
            var PostedDate= '<?PHP ECHO $PostedDate ?>'
            
			var validation=false;


			if(BuyerName==""){
			alert("Buyer Name is empty");
			$('#BuyerName').focus();
			}else if(BuyerAddress==""){
			alert("Please Buyer Address");
			$('#BuyerAddress').focus();
			}else if(BuyerCountry==""){
			alert("Please Buyer Country");
			$('#BuyerCountry').focus();
			}else if(DestinationPort==""){
			alert("Please DestinationPort");
			$('#DestinationPort').focus();
			}else if(BrandNames==""){
			alert("BrandName is empty");
			$('#BrandNames').focus();
			}else if(FishingPermitNumber ==""){
			alert("FishingPermitNumber is empty");
			$('#FishingPermitNumber').focus();
			}
			
			else{
				validation=true;
			}

			if(validation==false){
				$('#CL').removeAttr('disabled');
			return;
			}

		


			var arr = {
				
				'BuyerName': BuyerName,
				'BuyerAddress': BuyerAddress,
				'BuyerCountry': BuyerCountry,
				'DestinationPort': DestinationPort,
				'BrandNames': BrandNames,
				'FishingPermitNumber': FishingPermitNumber,
				'ProductInspector': ProductInspector,
				'QualityInspector': QualityInspector,
				'DocumntInspector': DocumntInspector,
				'SpecialInstructions': SpecialInstructions,
				'PostedUserID': PostedUserID,
				'PostedDateTime': PostedDate,
				'SOURCE': 'WEB'


				};
			$.ajax({
		  url:'<?php echo $ApiUrl ?>?f=createBuyer',
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(arr),
		  success:function(data){
			//  console.log("test");
			 console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			  // alert(obj.status);
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

<Script>

$('document').ready(function(){

    	$(document).on('click','#Edit',function(e){
		e.preventDefault();
        $('#Edit').attr('disabled','disabled');

		
            var BuyerName= $('#BuyerName').val();
			var BuyerAddress= $('#BuyerAddress').val();
			var BuyerCountry= $('#BuyerCountry').val();
			var DestinationPort= $('#DestinationPort').val();
			var BrandNames= $('#BrandNames').val();
			var FishingPermitNumber= $('#FishingPermitNumber').val();
			var ProductInspector= $('#ProductInspector').val();
			var QualityInspector= $('#QualityInspector').val();
			var DocumntInspector= $('#DocumntInspector').val();
			var SpecialInstructions= $('#SpecialInstructions').val();
            var ConID= '<?PHP ECHO $ID ?>'
            
			
		


			var arr = {
				
				'BuyerName': BuyerName,
				'BuyerAddress': BuyerAddress,
				'BuyerCountry': BuyerCountry,
				'DestinationPort': DestinationPort,
				'BrandNames': BrandNames,
				'FishingPermitNumber': FishingPermitNumber,
				'ProductInspector': ProductInspector,
				'QualityInspector': QualityInspector,
				'DocumntInspector': DocumntInspector,
				'SpecialInstructions': SpecialInstructions,
				'ConID': ConID,
				'SOURCE': 'WEB'


				};
			$.ajax({
		  url:'<?php echo $ApiUrl ?>?f=EditBuyer',
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(arr),
		  success:function(data){
			//  console.log("test");
			 console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			  // alert(obj.status);
			 if(obj.error==null){
				$('#Edit').removeAttr('disabled');
				alert(obj.msg);

				location.reload();

				}else{
				alert(obj.error);
				}

			}});


    	});
    });

</script>

