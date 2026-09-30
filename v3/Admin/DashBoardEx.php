<?php
session_start();
include "../includes/config.php";






  $START_DATE = date("Y-m").'-01';
    $PUR_DATE = date("Y-m-d");
	 $PURCHASE_DATE=date('d-m-Y', strtotime($PUR_DATE));


 $month= date("m", strtotime($PUR_DATE));

//




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
        DashBoard - <?php echo $PURCHASE_DATE ?>
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
				   <option value="1">Date Wise</option>
					 <option value="2">Agent</option>
					 <option value="3">Count</option>
					 <select>
					</div>

					<div class="col-md-3 col-sm-6 col-xs-12">
					 <label for="exampleInputEmail1">Unit</label>
                    <select class="form-control" name="UNIT" id="UNIT">
					  <option value="0">BOTH</option>
				   <option value="1">SME</option>

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






				<!--	<a id="print" class="btn btn-primary btn-sm edit btn-flat" target="blank" href="" >Print Lots(Day) </a>

					<a  id="status" class="btn btn-primary btn-sm edit btn-flat" target="blank" href="" > Grading Status(Day) </a>
				</div> -->

			  </div>




	  


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
 <?php include "../includes/scripts.php"; ?>
 <?php include "../includes/AjaxLoader.php"; ?>
</body>






<script type="text/javascript">




$('document').ready(function(){
	var from_date='<? echo $START_DATE ?>';
	var to_date='<? echo $PUR_DATE ?>';

		var UNIT=$('#UNIT').val();
		var TYPE=$('#TYPE').val();






	$.ajax({
			  	url:'AjaxDashBoard.php',
			  	type:'POST',
			  	data:{'FROM_DATE':from_date,'TO_DATE' : to_date,'UNIT':UNIT,'TYPE': TYPE},
			  	success:function(data){
				   //	console.log(data);
					$("#ATT_DATA").html(data);
					// $('#example1').DataTable()
				}
			});














	$('#lotTracking').click(function() {
		alert('hi');


});

$('#list').click(function() {
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var UNIT=$('#UNIT').val();
var TYPE=$('#TYPE').val();
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
			  	data:{'FROM_DATE':from_date,'TO_DATE' : to_date,'UNIT':UNIT ,'TYPE':TYPE},
			  	success:function(data){
				  // 	console.log(data);
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



 function PrintLot() {

	var from_date=$('#FROM_DATE').val();
	var to_date=$('#TO_DATE').val();
	var UNIT=$('#UNIT').val();
var URL='/Purchase/Excel.php?Type=Count&FromDate='+from_date+'&ToDate='+to_date;

	window.open(URL, '_blank');
/*
	$.ajax({
			url:'/includes/AjaxPrintFunc.php',
			type:'POST',

				data:{'FUNC':'LOTTRACK','from_date':from_date,'to_date' : to_date,'U':UNIT},
			success:function(data){
			//	console.log(data);
			//$("#ATT_DATA").html(data);
			reply_click();
		}



	});

	*/


 }
 function reply_click() {

alert('We Will UpDate Soon');
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
