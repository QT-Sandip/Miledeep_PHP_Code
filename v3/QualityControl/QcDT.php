<?php
session_start();
include "../includes/config.php";


if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}



 
 
$START_DATE = date("Y-m").'-01';
$PUR_DATE = date("Y-m-d");



?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
</head>
<body class="skin-blue sidebar-mini fixed sidebar-mini-expand-feature sidebar-collapse">


<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    
	<section class="content-header">
	  <div class="row">
        <div class="col-md-6">
         
      <h4>
        DashBoard - <?php echo $PUR_DATE ?>
         <strong> </strong>
      </h4>
	    </div>
				
				 <div class="col-md-6">
          
	 
      <!-- <div class="btn-group" align="right">
                  <button type="button" class="btn btn-info">Action</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Last 24 Hours</a></li>
                    <li><a href="#">Last 7 Days</a></li>
                    <li><a href="#">Last Month</a></li>
                  </ul>
                </div>-->
				
				</div>
				</div>
    </section>

    <section class="content">
      <!-- Small boxes (Stat box) -->
     
	  
	

   
	
	

	
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
		

		
		
		
      <div class="row">
	  <div class="col-xs-12">
    			


					<div class="col-md-3 col-sm-6 col-xs-12">
					 <label for="exampleInputEmail1">Type</label>
                    <select class="form-control" name="TYPE" id="TYPE">
				   <option value="1">Procurement</option>
				  
				  
				  <select>
					</div>
					
					<div class="col-md-3 col-sm-6 col-xs-12">
					 <label for="exampleInputEmail1">Unit</label>
                    <select class="form-control" name="UNIT" id="UNIT">
					  <option value="0">BOTH</option>
				   <option value="1">JME</option>
				   <option value="2">MAPL</option>
				 
				  <select>
					</div>
					
					<div class="col-md-3 col-sm-6 col-xs-12">
                    <label>Select From Date: </label>
                    <input type='date' value='<?php echo $START_DATE;?>'  class="form-control" name="FROM_DATE" id="FROM_DATE"></input>
					</div>
					
					<div class="col-md-3 col-sm-6 col-xs-12">
					<label>Select To Date: </label>
                    <input type='date' value='<?php echo $PUR_DATE;?>' class="form-control" name="TO_DATE" id="TO_DATE"></input>
					</div>
				  
				 
				
				
				  
				 
				


    				</div>
	  </div>
	  </div>
	  
	          <div class="box-footer">
			  
			   <div  align="center" class="form-group col-md-12 col-sm-12 col-xs-12">
				  <p><br></p>
                 <button id="list"  class='btn btn-success btn-sm btn-flat'><i class= 'fa fa-search'>
				 </i> Get List</button>

					

					<a class="btn btn-primary btn-sm edit btn-flat" target="blank" href="../Purchase/DocLotReportPrint.php?date=
					<?php echo $date_start ?>" >Print Lots(Day) </a>
					
					<a  class="btn btn-primary btn-sm edit btn-flat" target="blank" href="../DocMLotReportPrint1.php?date=
					<?php echo $month ?>" > Lots Status(Day) </a>				 
                 </div>
			  
			  </div>
			  
			   
	  
	  
	  <div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		  <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>
		
	
              
	</div>
	
	
	
 <div class="box-body" id="ATT_DATA">
              
	</div>

        <!-- /.col (RIGHT) -->
      </div>
      <!-- /.row -->

	  
	  
        <!-- /.col (RIGHT) -->
	  
	  </div>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  </div>
 <?php include "../includes/footer.php"; ?>
</body>






<script type="text/javascript">

$('document').ready(function(){
	var from_date='<? echo $START_DATE ?>';
	var to_date='<? echo $PUR_DATE ?>';
		
		var UNIT=$('#UNIT').val();
	
	$.ajax({
			  	url:'AjaxDashBoard.php',
			  	type:'POST',
			  	data:{'FROM_DATE':from_date,'TO_DATE' : to_date,'UNIT':UNIT},
			  	success:function(data){
				   	console.log(data);
					$("#ATT_DATA").html(data);
					// $('#example1').DataTable()
				}
			});		
	
	$('#list').click(function() {		
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		
		if(from_date==''){
		alert("Please Enter From Date");
		$('#FROM_DATE').focus();
		return;
		}else if(to_date==''){
		alert("Please Enter To Date");
		$('#TO_DATE').focus();
		return;
		}else{		
			$.ajax({
			  	url:'AjaxDashBoard.php',
			  	type:'POST',
			  	data:{'FROM_DATE':from_date,'TO_DATE' : to_date,'UNIT':UNIT},
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





<script>
if ( $('[type="date"]').prop('type') != 'date' ) {
    $('[type="date"]').datepicker();
}
</script>




</html>
