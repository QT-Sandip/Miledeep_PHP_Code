<?php
session_start();
include "../includes/config.php";

//$UNIT=$_GET['U']; 


if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}


//$SQL_COSTING="'JME','MAPL'";
//$RM_PROCESSING_STATIONS=$iconn->query($SQL_COSTING);

 
 
$START_DATE = date("Y-m-d",strtotime("yesterday"));

$PUR_DATE = date("Y-m-d",strtotime("yesterday"));
$PURCHASE_DATE=date('d-m-Y', strtotime($PUR_DATE)); 



?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>

</head>
<body class="skin-blue sidebar-mini fixed sidebar-mini-expand-feature sidebar-collapse">
<form action="DpPrint.php" method="post">

<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    
	<section class="content-header">
	  <div class="row">
        <div class="col-md-12">
         
      <h3 ALIGN ="CENTER"><B>
        AVAILABLE REPORTS LIST </B>
         <strong> </strong>
      </h3>
	    </div>
				
				 <div class="col-md-6">
          
					
    
				
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
					 <label for="exampleInputEmail1">Unit</label>
                    <select class="form-control" name="UNIT" id="UNIT">
					<option value='1'> JME</option>
					<option value='2'> MAPL</option>
				
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
				  
				 <h3 ALIGN ="CENTER"><br> </h3>
	  
	   <h3 ALIGN ="CENTER"><B><br>
	 
        Avaiable Reports <br> </B>
         <strong> </strong>
      </h3>
				 
				
				<table id="example" class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">
								
								
						
								
								<!--<tr ><td>Lot Report List</td>
								 <td><a  id="LotList" target="blank"  href="" class="btn btn-primary" > 
								 <i class="fa fa-search"> View</a></td>
								</tr>
								
								
								
								<tr ><td>Grading Reports </td>
								 <td>
								 <a  id='GradingList' target="blank" href="" class="btn btn-primary" >
								 <i class="fa fa-search"> View</a></td>
								</tr>-->
								
								
								
							
								<tr id="td_code_list"><td> Costing Reports </td>
								 <td>
								 <a  id='IqfReport' href="" class="btn btn-success" >
								 <i class="fa fa-search"> excel</a></td>
								
								</tr>

								
								</thead>
								
								</table>
				  
				 
				


    				</div>
	  </div>
	  </div>
	  
	          <div class="box-footer">
			  
			   <div  align="center" class="form-group col-md-12 col-sm-12 col-xs-12">
				  <p><br></p>
         <!--        <button id="list"  class='btn btn-success btn-sm btn-flat'><i class= 'fa fa-search'>
				 </i> Get List</button>  -->
                   
				
									 
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
 </form>
</body>






<script type="text/javascript">

$('document').ready(function(){
	
	
	$("#LotList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();

	var dynamic_link='../Purchase/DocLotReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT;
	
	$("#LotList").attr("href",dynamic_link);
		
	});
	
$("#GradingList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
	var dynamic_link='../DocMLotReportPrint1.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT;

	$("#GradingList").attr("href",dynamic_link);
		
	});

	$("#DehList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();

	var dynamic_link='DeheadingReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT;

	$("#DehList").attr("href",dynamic_link);
		
	});	$("#VaList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();

	//var dynamic_link='PpcValueAdditionPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT;
	var dynamic_link='Accounts/ValueAdditionReport.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT;

	$("#VaList").attr("href",dynamic_link);
		
	});
	
	$("#SoakingReport").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();

	var dynamic_link='SokingReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date;

	$("#SoakingReport").attr("href",dynamic_link);
		
	});
	$("#IqfReport").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
	var dynamic_link='DpPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT;

	$("#IqfReport").attr("href",dynamic_link);
		
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
