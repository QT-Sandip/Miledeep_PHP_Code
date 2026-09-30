<?php
session_start();
include "../includes/config.php";
include "../includes/AjaxCostingCreate.php";
$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"]; 


 $IS_GRADED = getCostingBreakUp($iconn,$LOT_ID);
 
 //echo  "Hii"; exit;


if(!$IS_GRADED){
	echo "<script>
	alert('No Costing Breakup Found Pls Contact Admin ');
	closeCurrentWindow();
	</script>";
}



 $SqlRmBd="SELECT count(*) as TOTAL FROM `RM_COSTING_DETAILS` where LOT_ID='$LOT_ID'";
$StmtRmBd=$iconn->query($SqlRmBd);
$RowRmBd=$StmtRmBd -> fetch_array();

if($RowRmBd['TOTAL']!=0){
	
	
	echo "<script>
	alert('Costing Already Created Please Refresh Reports');
	closeCurrentWindow();
	</script>";
	
echo  "<script type='text/javascript'>";
echo "window.close();";
echo "</script>";


	
	//header('Location: CostingPreview.php?id='.$LOT_ID);
}
 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse fixed">

   

<div class="wrapper">

<?php include "../includes/header.php"; ?>

  <!-- Left side column. contains the logo and sidebar -->

 <?php include "../includes/sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

        

<h2 align="center">Costing  Details </h2>

			

			



		<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Costing Preview</li>

      </ol>

    </section>

	
	

	

	



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	  

    <div id="tdetails" class="table-responsive" >  

        <div class="box">
		
		
		<div class="box-body">
               
			   
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Exchange Rate</label>
                  <input required type="text" class="form-control" VALUE="" name="EXCHANGE_RATE" id="EXCHANGE_RATE" 
				  placeholder="Enter Exchange Rate">
                </div>
				
				
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Expanse</label>
                  <input required type="text" class="form-control" VALUE="" name="EXPANSE" id="EXPANSE" 
				  placeholder="Enter expenses">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">DEPB</label>
                  <input required type="text" class="form-control" VALUE="" name="DEPB" id="DEPB" 
				  placeholder="Enter DEPB ">
                </div>
				
				<div class="form-group col-sm-3">
                <br>
                 <button id="details" align="right" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i> Get</button>
                </div>
			
		
		
		  <div id="wait" style="display:none;width:69px;height:89px;
		  border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		  <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>
		
		
		
        
				
				
			
			 

          

       

  
  </div>
  
  <div class="box-body" id="ATT_DATA">
              
            </div>

 <div class="col-xs-12 box-footer"  align="right">
                <button id="submit"  name='submit' class="btn btn-primary">Submit</button>
              </div>
           
 
 


</div>
						
						
						

    		
  						

<div>



</div>

</div>

</div>
</div>



    </section>

    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>

  </div>



</body>


<script>

$('document').ready(function(){
	
var LOT_ID="<?php echo $LOT_ID ?>";
var USER_ID="<?php echo $USER_ID ?>";
$('#submit').click(function() {

	
	var EXCHANGE_RATE=$('#EXCHANGE_RATE').val();
	var EXPANSE=$('#EXPANSE').val();
	var DEPB=$('#DEPB').val();
	
	if(EXCHANGE_RATE==''){
	alert('Enter Exchange Rate');
	return;
	}else if(EXPANSE==''){
	alert('Enter Expanse / Kg');	
	return;
	}else if(DEPB==''){
	alert('Enter Depb');
	return;
	}
	
	
	$.ajax({
			  	url:'AjaxCostingPreview.php',
			  	type:'POST',
			  	data:{'TYPE':'SUBMIT','EXCHANGE_RATE':EXCHANGE_RATE,'USER_ID':USER_ID,'EXPANSE': EXPANSE ,'DEPB': DEPB,'LOT_ID' :LOT_ID},
			  	success:function(data){
					if(data=='Success'){
					alert('Success');
					window.open ('BillReports.php?id='+LOT_ID,'_self',false)
					}
				}
			});		
	
	  
	
});


	
	
	$('#details').click(function() {
	//
	//return;
	var EXCHANGE_RATE=$('#EXCHANGE_RATE').val();
	var EXPANSE=$('#EXPANSE').val();
	var DEPB=$('#DEPB').val();
	
	if(EXCHANGE_RATE==''){
	alert('Enter Exchange Rate');
	return;
	}else if(EXPANSE==''){
	alert('Enter Expanse / Kg');	
	return;
	}else if(DEPB==''){
	alert('Enter Depb');
	return;
	}
	
	
	$.ajax({
			  	url:'AjaxCostingPreview.php',
			  	type:'POST',
			  	data:{'EXCHANGE_RATE':EXCHANGE_RATE,'EXPANSE': EXPANSE ,'DEPB': DEPB,'LOT_ID' :LOT_ID},
			  	success:function(data){
				$("#ATT_DATA").html(data);
				}
			});		
	
	  
	
});


});


$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });

    	

</script>





</html>

