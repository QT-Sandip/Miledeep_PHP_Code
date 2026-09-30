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
					 <label for="exampleInputEmail1">Station</label>
                    <select class="form-control" name="PURCHASE_UNIT" id="PURCHASE_UNIT">
					<option value='1'>Bhimavaram</option>
					<option value='2'>Narasapuram</option>

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

        Available Reports <br> </B>
         <strong> </strong>
      </h3>


				<table id="example" class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">




								<tr ><td>Lot Report List</td>
								 <td><a  id="LotList" target="_blank"  href="" class="btn btn-primary" >
								 <i class="fa fa-search"> View</a></td>
								</tr>



								<tr ><td>Grading Reports </td>
								 <td>
								 <a  id='GradingList' target="_blank" href="" class="btn btn-primary" >
								 <i class="fa fa-search"> View</a></td>
								</tr>

									<tr ><td>Hon Waste Reports </td>
								 <td>
								 <a  id='DehList' target="_blank" href="" class="btn btn-primary" >
								 <i class="fa fa-search">View</i></a>
								  <a  id='Dehdownload' target="_blank" href="" class="btn btn-primary" >
								 <i class="fa fa-download"> Download</i></a>
								</td> 
								 
								</tr>


								<tr><td> Costing Reports </td>
								 <td>
								 <a  id='IqfReport' target="_blank" href="" class="btn btn-primary" >
								 <i class="fa fa-search"> View</a></td>
								</tr>


								<tr><td> Lot Tracking </td>
								 <td>
								 <button  id='lotTracking' target="_blank" href="" class="btn btn-primary" >
								 <i class="fa fa-search"> View</button></td>
								</tr>
								
								<tr><td> Tds Report </td>
								 <td>
								 <button  id='TdsTracking' target="_blank" href="" class="btn btn-primary" >
								 <i class="fa fa-search"> View</button></td>
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







	</div>



 <div class="box-body hidden" id="ATT_DATA">



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
 <?php include "../includes/scripts.php"; ?>
 
</body>






<script type="text/javascript">

$('document').ready(function(){


	$("#LotList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		var PU=$('#PURCHASE_UNIT').val();

	  var dynamic_link='../Purchase/DocLotReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT+'&PU='+PU;

	$("#LotList").attr("href",dynamic_link);

	});



$("#GradingList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		var PU=$('#PURCHASE_UNIT').val();
	var dynamic_link='../DocMLotReportPrint1.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT+'&PU='+PU;;

	$("#GradingList").attr("href",dynamic_link);

	});



	$("#DehList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		var PU=$('#PURCHASE_UNIT').val();

	var dynamic_link='DpPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT+'&PU='+PU;

	$("#DehList").attr("href",dynamic_link);

	});
	
	$("#Dehdownload").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		var PU=$('#PURCHASE_UNIT').val();

	var dynamic_link='HonPackingReportDwnld.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT+'&PU='+PU;

	$("#Dehdownload").attr("href",dynamic_link);

	});

	$("#VaList").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
var PU=$('#PURCHASE_UNIT').val();
	//var dynamic_link='PpcValueAdditionPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT;
	var dynamic_link='Accounts/ValueAdditionReport.php?from_date='+from_date+'&'+'to_date='+to_date+'&U='+UNIT+'&PU='+PU;;

	$("#VaList").attr("href",dynamic_link);

	});




	$("#SoakingReport").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
var PU=$('#PURCHASE_UNIT').val();
	var dynamic_link='SokingReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&PU='+PU;;

	$("#SoakingReport").attr("href",dynamic_link);

	});



	$("#IqfReport").click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		var PU=$('#PURCHASE_UNIT').val();
	var dynamic_link='LotReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT+'&PU='+PU;;

	$("#IqfReport").attr("href",dynamic_link);

	//window.open(dynamic_link);

	});
	
	$("#TdsTracking").click(function() {
		
		//alert('hii');
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
		var PU=$('#PURCHASE_UNIT').val();
	var dynamic_link='TdsReportPrint.php?from_date='+from_date+'&'+'to_date='+to_date+'&'+'U='+UNIT+'&PU='+PU;;

	$("#TdsTracking").attr("href",dynamic_link);

	window.open(dynamic_link);

	});


	$("#lotTracking").click(function() {
	//alert('hi');
	var from_date=$('#FROM_DATE').val();
	var to_date=$('#TO_DATE').val();
	var UNIT=$('#UNIT').val();
			var PU=$('#PURCHASE_UNIT').val();
			



	$.ajax({
			url:'../includes/AjaxPrintFunc.php',
			type:'POST',

				data:{'FUNC':'LOTTRACK','from_date':from_date,'to_date' : to_date,'U':UNIT},
			success:function(data){
				console.log(data);
			$("#ATT_DATA").html(data);
			reply_click();
			}



	});
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



<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	 <script type="text/javascript">

	 function reply_click() {

		 divToPrint=document.getElementById("ATT_DATA");
 		 // date=document.getElementById("date");

 			var originalTitle = document.title;
 			document.title = "Print page title";
 		var printWindow = window.open('', '', 'height=400,width=800');

 		//	 printWindow.document.write(date.outerHTML);
 		printWindow.document.write(divToPrint.outerHTML);
 		printWindow.document.close();
 		printWindow.print();
	   }



	 </script>




</html>
