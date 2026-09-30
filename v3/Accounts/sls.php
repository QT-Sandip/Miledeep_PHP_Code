<?php
session_start();
include "../includes/config.php";


/*
$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"];

if(isset($_GET['date'])){
    $date_start = $_GET['date'];
 }else{

$sql_inp_data="SELECT PURCHASE_DATE FROM inputmaterial_details ORDER BY id DESC LIMIT 1";
$stmt_inp=$iconn->query($sql_inp_data);
$row=$stmt_inp->fetch_array();
$date=$row['PURCHASE_DATE'];

$date_start=substr($date,0,10); 
 }



$date_end=$date_start.' 23:59:59';





$sql="SELECT * FROM `inputmaterial_details` where  PURCHASE_DATE between '$PD_START'and'$PD_END'";
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();
			
			
$PO_TIME_STAMP = strtotime($string);
$PD_START = date("d-m-Y", $PO_TIME_STAMP);
$PD_END=$PD_START.' 23:59:59';

*/
 ?>



<!-- <!DOCTYPE html>

<html> -->

<head>
	<?php include "../includes/meta.php"; ?>
<!--	<link href="http://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" rel="stylesheet">   -->
      
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

        

<h2 align="center">Costing Report</h2>




	</a>



			</a>

			

			



<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Costing Report</li>

      </ol>

    </section>

	
	
	<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body">
                
				
						
						
						 <table id="example1" class="table table-bordered table-hover">

              

							<thead class="process_dt_dt" id="process_dt_dt">

                                
								
								</thead>
								
								</table>
		
								
				
				
				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	

	



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	  

    <div id="tdetails" class="table-responsive" >  

        <div class="box">

          

        <div class="box-body">

		

		

		

 <div class="row">



    			<div class="col-xs-12">
    			


					
					
					
					  <table id="example1" class="table table-bordered table-hover">
					    <form class="form-inline">

								<div class="col-xs-2">
                    <label>Select To Date: </label>
                    <input type='DATE' class="" name="FROM_DATE" id="FROM_DATE">                    
                    </input>
					</div>
					</form>
					                


					
					<div class="col-xs-2">
					<label>Select From Date: </label>
                    <input type='date' class="form-control" name="TO_DATE" id="TO_DATE">
                      
                    </input>

                  </div>
				  
				  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Exchange Rate</label>
                  <input type="text" class="form-control"  name="EXCHANGE_RATE" id="EXCHANGE_RATE" 
				  placeholder="Enter Exchange Rate">
                </div>
				
				  
				  <div class="form-group col-sm-3">
                 <button id="list" align="right" class='btn btn-success btn-sm btn-flat'><i class= 'fa fa-search'>
				 </i> Get List</button>				  
                 </div>
				
							
								
								</table>

    				</div>
</div>


 <div class="box">
		  <div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		 <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>
		
            <div class="box-header with-border">
             
            </div>
	  
            <div class="box-body" id="ATT_DATA">
              
            </div>
          </div>

		

	
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-primary">Submit</button>
              </div>
            </form>



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




<!--<script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->

<script type="text/javascript">

$('document').ready(function(){
	$('#list').click(function() {		
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var EX_RATE=$('#EXCHANGE_RATE').val();
		
		if(from_date==''){
		alert("Please Enter From Date");
		$('#FROM_DATE').focus();
		return;
		}else if(to_date==''){
		alert("Please Enter To Date");
		$('#TO_DATE').focus();
		return;
		}else if(EX_RATE==''){
		alert("Please Enter EXCHANGE RATE");
		$('#EXCHANGE_RATE').focus();
		return;
		}else{		
			$.ajax({
			  	url:'AjaxCost.php',
			  	type:'POST',
			  	data:{'FROM_DATE':from_date,'TO_DATE' : to_date,'EXCHANGE_RATE' : EX_RATE },
			  	success:function(data){
				   	console.log(data);
					$("#ATT_DATA").html(data);
					// $('#example1').DataTable()
				}
			});		
		}
	});
});
$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
});
$(document).ajaxComplete(function(){
	$("#wait").css("display", "none");
});
</script>

<!--
<script type="text/javascript">
	$(function(){
		$("#FROM_DATE").datepicker();
		$("#TO_DATE").datepicker();
	});
</script> -->
</body>
<!-- 

</html> -->

