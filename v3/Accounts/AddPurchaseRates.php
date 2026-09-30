<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Accounts";
$PageTitle="Purchase Rates";


$INVOICE_ID=$_GET['id'];





 $sql_acr="SELECT * FROM RmBaseRates   ORDER by COUNT ASC ";
$stmt_acr=$iconn->query($sql_acr);


 while($row=$stmt_acr->fetch_array()){
	 $AryBaseRates[]=$row;
	 $AryBaseCounts[]=$row['COUNT'];
 }
 
 
 $SizeBaseRates=count( $AryBaseRates);

$sql_acr="SELECT * FROM `RmBaseRatesVariations`";
$stmt_acr=$iconn->query($sql_acr);


 while($row=$stmt_acr->fetch_array()){
	 $AryVariation[]=$row;
 }
 
  $SizeBaseRatesVariatons=count($AryVariation);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));
	
	$datas = explode('&',$FULL_NAME);
	 $FULL_NAME = $datas[0];
	 $SHORT_NAME = $datas[1];
	
	
	
	
	 $sql_org_insert="";
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		
			 $SQL_INV_UPDATE="";
	
	
	$STMT_INV_UPDATE=$iconn->query($SQL_INV_UPDATE);
		
		
		header('Location: CodeList.php?id='.$INVOICE_ID);
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

											<?php include "PopUpTable.php"; ?>

          <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                
                <h4 class="modal-title modheading">Select Rates Start Date</h4>
				
            </div>
            <div class="modal-body checkdiv">
			

			 <input  type="date" class="form-control" 
			 name="SubmissionDate" id="SubmissionDate"
			placeholder="Enter Base Price" 
			value=""> 
            
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="proceed" class="btn btn-success" data-dismiss="modal">proceed</button>
            </div>
        </div>
    </div>
</div>

	
<div class="row">
                                <div class="card">
								
                                    <div class="card-body" id ="ClientList">
									

     <table class="table table-striped table-bordered 
	 dt-responsive " >
  <thead>
 
	<?php
	
	$count=0;
	$w1='10%';
	
      foreach($AryBaseRates AS $RowBaseRate){?>
	
	
	<?php $COST_RATE_ID= $count; ?>
	

	<th  id="BaseCount<?php echo $COST_RATE_ID; ?>" ><?php echo $RowBaseRate['COUNT']; ?>
	</th>	
	
	  <?php 
		$count++;
   } ?>

	</thead>
	<tbody>
	<tr>
	<?php
	
	$count=0;
	
      FOREACH($AryBaseRates AS $RowBaseRate){?>
	
	
	<?php 
	  $COST_RATE_ID= $RowBaseRate['COST_RATE_ID'];
 //echo $RowBaseRate['CountStatus'] ?>
	
	
<td >	<input type="hidden" id="BaseRateId<?php echo $count; ?>" 
	value="<?php echo $COST_RATE_ID ?>"> 
<?php	if($RowBaseRate['CountStatus']==1){?>

 <input  type="text" class="form-control"  
	name="BaseRate<?php echo $count; ?>" id="BaseRate<?php echo $count; ?>"
	placeholder="Enter Base Price" value="<?php echo (int)$RowBaseRate['USD']; ?>"> </td>
	
<?php	}
$count++;
   } ?>

				  
	</tr>
	
	
  
  <tr>
	<?php
	
	$count=0;
	
      FOREACH($AryVariation AS $VALUE){?>
	
	
	<?php $COST_RATE_ID= $count; ?>
	

	<th><font size="<?php echo $text;?>"> <B>
<?php echo $VALUE['GRADE']; ?></B>
	</font></th>	
	
	  <?php 
$count++;
   } ?>
   
   
   
	<tr>
	<?php
	
	$count=0;
	
      FOREACH($AryVariation AS $VALUE){?>
	
	
	<?php $COST_RATE_ID= $VALUE['COST_RATE_ID']; ?>
	
	<td> 
	<input type="hidden" id="RatesVariationId<?php echo $count; ?>" value="<?php echo $COST_RATE_ID ?>">
	<input  type="text" class="form-control"  name="Rates" id="RatesVariation<?php echo $count; ?>"
	placeholder="Enter Variation" value="<?php echo (int)$VALUE['USD']; ?>"> </td>
				  

				  

	  <?php 
$count++;
   }

	  ?>

				  
	</tr>
	</tbody>
</table>
	
	<div>      
		 
		  <button onClick="reply_click()"
		  class=" glyphicon glyphicon- btn btn-primary"> Refresh</button>
	 	 
		 <button  id="Btnsubmit"
		  class=" glyphicon glyphicon- btn btn-success">Submit</button>
	 	
		  </div>
		  
		  <div id="PricesTable">

			</div>

   <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
                               
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

$(document).on('click','#Btnsubmit',function(e){			
			e.preventDefault();
			
			$('#myModal').modal('show');
		//	
		/*	var INT_LOT_NUM=$(this).val();
			//alert(INT_LOT_NUM);
		
						//var UNIT=$('#JME_MOURYA').val();
			
				

		$.ajax({
		   url:'Ajax/AjPopUpPoStatus.php',
		  type:'POST',
		  data:{'INT_LOT_NUM':INT_LOT_NUM , 'TYPE':'UMA'},
		  success:function(data){
			 
			 $('.modal-title').text('Do You Want To Submit The Rates '+INT_LOT_NUM+' ?');
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});*/
			
			 var SizeBaseRates='<?php echo  $SizeBaseRates; ?>';		 
  var SizeBaseRatesVariatons='<?php echo  $SizeBaseRatesVariatons; ?>';		 
		// alert(SizeBaseRates);  
			var AryBaseRates = [];
			var AryRatesVariation = [];
		 for (i = 0; i < SizeBaseRates; i++) {
			   var BaseRateId='#BaseRate'+i;
			   var BaseRatePid='#BaseRateId'+i;
			   var BaseRate=$(BaseRateId+"").val();
			   var BaseRatePidValue=$(BaseRatePid+"").val();
			   var Value=BaseRatePidValue+'/'+BaseRate
				AryBaseRates.push(Value);
				
				if(BaseRate !=0 && !Number(BaseRate)) {
				 alert('Only Number Allowed for Price ' +BaseRate );				 
				  document.getElementById("BaseRate"+i).focus();					
					return;					
				}
			} 
			
		for (i = 0; i < SizeBaseRatesVariatons; i++) {
			   var RatesVariationId='#RatesVariation'+i;
			   var RatesVariationPId='#RatesVariationId'+i;
			   var RatesVariation=$(RatesVariationId+"").val();
			   var RatesVariationPId=$(RatesVariationPId+"").val();
			    var VaricValue=RatesVariationPId+'/'+RatesVariation
				AryRatesVariation.push(VaricValue);
				
				if(RatesVariation !=0 && !Number(RatesVariation)) {
				 alert('Only Number Allowed for Price Variation ' +RatesVariation );				 
				  document.getElementById("RatesVariation"+i).focus();					
					return;					
				}
			}

	 
		$.ajax({
		  url:'Ajax/AjaxRmBaseRatesUpdate.php',
		  type:'POST',
		  data:{'BaseRates':AryBaseRates,'RateVarc':AryRatesVariation,'Type':'Update',
		  'SubmissionDate':SubmissionDate},
		  success:function(data){
			  if(SubmissionDate==null){
			 $("#PricesTable").html(data);
			  }else{
				 // alert(data);
				 // console.log(data);
				   var strArray = JSON.parse(data);
				   if(strArray['Error']==null){
					   alert(strArray['Msg']);
				   }else{
					    alert(strArray['Error'])
				   }
  
			  }
			
			}
			});					  

    	});	
		
		
		
			
$(document).on('click','#proceed',function(e){
	e.preventDefault();
	
	var SubmissionDate=$('#SubmissionDate').val();

	reply_click(SubmissionDate);
	
  
});
		
	
		
		
</script>
<!--uma-->

<script>
$('document').ready(function(){	
			
			$.ajax({
		  url:'Ajax/AjaxRmBaseRatesUpdate.php',
		  type:'POST',
		  data:{'Type':'Refresh'},
		  success:function(data){
			 $("#PricesTable").html(data);
			
			}
			});	

});
</script>


<script>
  function reply_click(SubmissionDate) {
	  


  var SizeBaseRates='<?php echo  $SizeBaseRates; ?>';		 
  var SizeBaseRatesVariatons='<?php echo  $SizeBaseRatesVariatons; ?>';		 
		// alert(SizeBaseRates);  
			var AryBaseRates = [];
			var AryRatesVariation = [];
		 for (i = 0; i < SizeBaseRates; i++) {
			   var BaseRateId='#BaseRate'+i;
			   var BaseRatePid='#BaseRateId'+i;
			   var BaseRate=$(BaseRateId+"").val();
			   var BaseRatePidValue=$(BaseRatePid+"").val();
			   var Value=BaseRatePidValue+'/'+BaseRate
				AryBaseRates.push(Value);
				
				if(BaseRate !=0 && !Number(BaseRate)) {
				 alert('Only Number Allowed for Price ' +BaseRate );				 
				  document.getElementById("BaseRate"+i).focus();					
					return;					
				}
			} 
			
		for (i = 0; i < SizeBaseRatesVariatons; i++) {
			   var RatesVariationId='#RatesVariation'+i;
			   var RatesVariationPId='#RatesVariationId'+i;
			   var RatesVariation=$(RatesVariationId+"").val();
			   var RatesVariationPId=$(RatesVariationPId+"").val();
			    var VaricValue=RatesVariationPId+'/'+RatesVariation
				AryRatesVariation.push(VaricValue);
				
				if(RatesVariation !=0 && !Number(RatesVariation)) {
				 alert('Only Number Allowed for Price Variation ' +RatesVariation );				 
				  document.getElementById("RatesVariation"+i).focus();					
					return;					
				}
			}

	 
		$.ajax({
		  url:'Ajax/AjaxRmBaseRatesUpdate.php',
		  type:'POST',
		  data:{'BaseRates':AryBaseRates,'RateVarc':AryRatesVariation,'Type':'Update',
		  'SubmissionDate':SubmissionDate},
		  success:function(data){
			  if(SubmissionDate==null){
			 $("#PricesTable").html(data);
			  }else{
				 // alert(data);
				 // console.log(data);
				   var strArray = JSON.parse(data);
				   if(strArray['Error']==null){
					   alert(strArray['Msg']);
				   }else{
					    alert(strArray['Error'])
				   }
  
			  }
			
			}
			});					  
  }
</script>
<script>
$(document).ready(function(){
	  showData();
	  });


function showData(data){
			
					$("#ClientList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}


		


</script>






</html>


