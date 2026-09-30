<?php

session_start();
include "../includes/config.php";

 $TYPE=$_GET['ID']; 

if($TYPE==1){
	$Header="Soaking Data";
}else if($TYPE==2){
	$Header="IQF Out Feed Data";
}
 $_SESSION['page']=$Header;



$uid=$_SESSION['r_id'];





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

    
    <!-- Main content -->
    <section class="content">
	 <?php include 'HlSearch_Bar.php'; ?>

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


	var selectedLots=new Array();

	$(document).on('click','#chbk',function(e){

	var LotId=$(this).val();
	var index = selectedLots.indexOf(LotId);

	if(index > -1){
	selectedLots.splice(index, 1);

	var elisa = document.getElementById(LotId);
	 elisa.href = "../Purchase/AddHLGrdng.php?ID="+LotId;
	}else{
	 selectedLots.push(LotId);

	}

	var MixedIds = selectedLots.join();
	//alert(MixedIds);
	var Ids=MixedIds.replace(/,/g, "_");
	//alert(Ids);
	if(selectedLots.length > 1){
	var i=0;
	for(i=0; i<selectedLots.length; i++){
	var elisa = document.getElementById(selectedLots[i]); //or grab it by tagname etc
	elisa.href = "../Purchase/AddHLGrdng.php?ID="+Ids+"&Mix=1";
	}
	}else{
	for(i=0; i<selectedLots.length; i++){
	var elisa = document.getElementById(selectedLots[i]); //or grab it by tagname etc
	elisa.href = "../Purchase/AddHLGrdng.php?ID="+Ids;
	}
	}



	console.log(selectedLots);

	});



		$(document).on('click','#list',function(e){
			e.preventDefault();

			var MONTH=$('#MONTH').val();
			var YEAR=$('#YEAR').val();
			var UNIT=$('#UNIT').val();
			var TYPE='<?PHP ECHO $TYPE ; ?>';
			
			if(TYPE==1){
				$URL='AjaxSckngTable.php';
			}else{
				$URL='AjaxIQFTable.php';
			}

			//alert(YEAR);


			
//priya
$.ajax({
		  url:$URL,
		  type:'POST',
		  data:{'MONTH' : MONTH,'YEAR': YEAR,'TYPE' : TYPE,'UNIT': UNIT},
		  success:function(data){
			  // console.log(data);
			   $("#ATT_DATA").html(data);
             $('#example1').DataTable(
			   {
   "order": [[ 1, 'desc' ]]
}
			   );


					}
			});

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


</script>



</html>
