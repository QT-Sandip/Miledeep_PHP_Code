<style>
.select2 {
    width:150px;
}
</style>

<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Invoice Details";

$PoID=$_GET['PIID'];


  $SqlPoData="SELECT * FROM `$TblScrmPIData` where PoID='$PoID'"; 
 $StmtPoData=$iconn->query($SqlPoData);
 $RowPo=$StmtPoData->fetch_array();
 $PoRefId=$RowPo['PoRefId'];
 $Country=$RowPo['DestinationCountry'];
 
 	$SqlPorts="SELECT * FROM $TblScrmPortCities where Country=$Country";
	$StmtPorts=$iconn->query($SqlPorts);
	
	while($RowPo=$StmtPorts->fetch_array()){
		$AryPorts[]=$RowPo;
	}
 
 
  $SqlPoData="SELECT * FROM `$TblScrmPIData` where PoRefId='$PoRefId'";  
 $StmtPoData=$iconn->query($SqlPoData);
 
 /*
extract($_POST);
extract($_GET);

*/



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
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
			<table id="" class="table table-bordered table-hover">
              

    <thead>

      <tr>	  
	  <th>PI Number</th>
	  <th>Ship By Date</th>
	  <th>Port of Discharge</th>
      </tr>

    </thead>

	

	

	 

			

			

			

    <tbody id="CODE_LIST">



	<?php

$Index=0;

	

   while($RowPo=$StmtPoData->fetch_array()){

	?>
	<tr>
	
	
	

	
	<td  ><input type='hidden' value="<?php echo $RowPo['PoID']; ?>" id="SelPoID<?php echo $Index ?>"><?php echo $RowPo['InvNo']; ?></td>
				  
				  
				  
	
	<td><input required type="date" class="form-control" name="ShipByDate<?php echo $Index ?>"
				  id="ShipByDate<?php echo $Index ?>" placeholder="Enter ShipBy Date" Value="<?php echo $RowPo['ShipByDate'];?>"
				 ></input>			  
				  
	</td>
	
	
	
	<!--<td style="width: 150px;">
		 <select  required class="form-control select2" name="Port<?php echo $Index ?>" id="Port<?php echo $Index ?>">
				  <option value="">Select Port</option>
				   <?php /*foreach ($AryPorts as $RowPort ){ ?>
				   
				     <option 
					 
					 value="<?php  echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }*/?>	  
				  </select>				  
				  
	</td>-->	
	<td style="width: 150px;">
		 <select  required class="form-control select2" name="Port<?php echo $Index ?>" id="Port<?php echo $Index ?>">
				  <option value="">Select Port</option>
				   <?php foreach ($AryPorts as $RowPort ){ ?>
				   			<?php if($RowPo['PortOfDischarge']==$RowPort['PortId']){ ?>

				     <option selected="selected" 
	 
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				  <?php }else{ ?>
					<option value="<?php echo $RowPort['PortId'] ?>"><?php echo $RowPort['Name'];?> </option> 
					 
			<?php  }}?>	  
			
			
				  </select>				  
				  
	</td>	
	
	


        

        </tr>

	  <?php 
	  
	  $Index++;

   }

	  ?>

	  

	 

     </tbody>

  </table>
  
  <div class="col-xs-12 box-footer"  align="right">
    <a href="PIEdit.php?id=<?php echo  $PoID ?>" class="btn btn-danger">Back</a>
	<a target="" href="DocAddReports.php?PoID=<?php echo $PoID ?>"
       class="btn btn-warning"> Close</a>
  		
                <!--<button  type='submit' name='submit' id='submit' value="<?php echo $Index ?>"
				class="btn btn-success">Next</button> -->
                <button  type='button' name='Next' id='Next' value="<?php echo $Index ?>" class="btn btn-success">Next</button>
              </div>
	
			
</div>





    </section>
	
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
<Script>

$('document').ready(function(){
    var arr=[];


//$('#FULL_NAME').change(function() {
$('#Next').click(function() {
	var Size=this.value;
	var InvID='<?php echo $PoID ?>'
	
	//alert(InvID);
	//return;
	//var Obj={};
	var AryData=[];
	
			 for (i = 0; i < Size; i++) {
			 var Obj={};
			
			   var PoIDId='#SelPoID'+i;
			   var ShipByDateId='#ShipByDate'+i;
			   var PortId='#Port'+i;
			    var PoID=$(PoIDId+"").val();
			    var Port=$(PortId+"").val();
			    var ShipByDate=$(ShipByDateId+"").val();
				
				//alert(Port);
				
				if(Port==''){
				alert("Select Port");
				document.getElementById("Port"+i).focus();
				return;
				}
				
				//alert(PoID);
				Obj['ShipByDate']=ShipByDate;
				Obj['PoID']=PoID;
				Obj['Port']=Port;
				AryData.push(Obj);
				
			 }
			 
			// alert(AryData); 
			 
			$.ajax({
		  url:'Ajax/AjPiDtls.php',
		  type:'POST',
		  data:{'AryData' : AryData},
		  success:function(data){
			  if(data=='Success'){
				   window.location = "PoBreakUp.php?id="+InvID;
				   				  alert(data);

			  }else{
				  window.location = "PoBreakUp.php?id="+InvID;
				  // $('#myModal').modal('toggle');
				  alert(data);
			  }
			 
			 
			}
			});	
});


});

    	

</script>







<script>

    function update_click(clicked_id){
	  var ref_id='<?php echo $PoID?>';
	  
	  var DAY_CODE_ID='#DAY_CODE'+clicked_id;
	  var PACKING_ID='#PACKING'+clicked_id;
	  var GLAZE_PERC_ID='#GLAZE_PERC'+clicked_id;
	  var VARIETY='#VARIETY'+clicked_id;
	  var BRAND_ID='#BRAND'+clicked_id;
	  var GRADES_ID='#GRADES'+clicked_id;
	  var MCS_ID='#MCS'+clicked_id;
	  var T_O_MCS_ID='#T_O_MCS'+clicked_id;
	  
	  var DAY_CODE=$(DAY_CODE_ID+"").val();
	  var PACKING=$(PACKING_ID+"").val();
	  var GLAZE_PERC=$(GLAZE_PERC_ID+"").val();
	  var VARIETY=$(VARIETY+"").val();
	  var BRAND=$(BRAND_ID+"").val();
	  var GRADES=$(GRADES_ID+"").val();
	  var MCS=$(MCS_ID+"").val();
	  var T_O_MCS=$(T_O_MCS_ID+"").val();
		 
		//alert(FARM_REG_ID);
		  
		$.ajax({
		  url:'AjaxBDdelete.php',
		  type:'POST',
		  data:{
		  
		  'TYPE':'CODE_LIST_UPDATE',
		  'D_ID':clicked_id,
		  'ref_id':ref_id,
		  'DAY_CODE':DAY_CODE,
		  'PACKING':PACKING,
		  'GLAZE_PERC':GLAZE_PERC,
		  'VARIETY':VARIETY,
		  'BRAND':BRAND,
		  'GRADES':GRADES,
		  'MCS':MCS,
		  'T_O_MCS':T_O_MCS
		  
		  },
		  success:function(data){
			  console.log(data);
			  
			 $("#PACK_SIZE").html(data);
			 alert("Code List Updated");
			}
			});	
			
			
					  
  }
</script>





</html>


			

