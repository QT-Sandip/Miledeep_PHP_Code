<?php

session_start();
include "../includes/config.php";

$TYPE=$_GET['T'];




$uid=$_SESSION['r_id'];

if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}

$_SESSION['page']="HeadLess Grading";


 ?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed sidebar-collapse">

<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->


    <!-- Main content
    <section class="content">
	 <?php include 'Search_Bar.php'; ?>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  </div>
 <?php include "../includes/footer.php"; ?>
  <?php include "../includes/scripts.php"; ?>
   <?php include "../includes/AjaxLoader.php"; ?>


</body>

<script>
$('document').ready(function(){
		getData();

		$(document).on('click','#list',function(e){
			e.preventDefault();

			getData()
    	});



    	$('#print').on('click',function(){
			var MONTH=$('#MONTH').val();
			var YEAR=$('#YEAR').val();
			var UNIT=$('#UNIT').val();
			window.open('../Accounts/LotReportPrint.php?Y='+YEAR+'&M='+MONTH+'&U='+UNIT);


    		//alert('print');
    	});
    });

$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });


function getData(){
	
	var MONTH=$('#MONTH').val();
			var YEAR=$('#YEAR').val();
			var UNIT=$('#UNIT').val();
			var TYPE='<?PHP ECHO $TYPE ; ?>';

			//alert(YEAR);


			$.ajax({
		  url:'AjaxGradingList.php',
		  type:'POST',
		  data:{'MONTH' : MONTH,'YEAR': YEAR,'TYPE' : TYPE,'UNIT': UNIT},
		  success:function(data){
			  // console.log(data);
			   $("#ATT_DATA").html(data);
             $('#example1').DataTable(
			   {
   "order": [[ 2, 'desc' ]]
}
			   );


					}
			});
	
}

</script>

</html>
