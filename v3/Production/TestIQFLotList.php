<?php

session_start();
include "../includes/config.php";

$ID=$_GET['id'];

 
$uid=$_SESSION['r_id'];
$ACC_TYPE=$_SESSION['ACC_TYPE'];


$_SESSION['page']="Lots List";

 ?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
<?php 
	
if($ID=='ph'){
	 $sql="select * from inputmaterial_details WHERE  SAMPLE_TYPE='PRE HARVEST'";
}else if($ID==''){
echo	$sql="select * from inputmaterial_details WHERE `UNIT_ID` !='' and `SAMPLE_TYPE` !='PRE HARVEST' order by id desc limit 500";
}else{
//$sql="select * from inputmaterial_details where STATUS='$ID' ";	
$sql="select * from inputmaterial_details WHERE `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and STATUS='$ID' limit=500  ";	
}


$stmt=$iconn->query($sql);

?>

<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) 
    <section class="content-header">
      <a href="CreateLot.php" class="btn btn-primary">Create Lot</a>  
	<ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Lots List</li>
      </ol>
    </section>
	-->

    <!-- Main content -->  
	 <section class="content">
	 <?php include 'Search_Bar.php'; ?>
				<?php include "PopUpTable.php"; ?>

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
	 elisa.href = "AddHLGrdng.php?ID="+LotId; 
	}else{
	 selectedLots.push(LotId);	
	 
	}
	
	var Ids = selectedLots.join();
	var Ids=Ids.replace(",", "_");
	if(selectedLots.length > 1){
	var i=0;
	for(i=0; i<selectedLots.length; i++){
	var elisa = document.getElementById(selectedLots[i]); //or grab it by tagname etc
	elisa.href = "AddHLGrdng.php?ID="+Ids+"&Mix=1";  
	}
	}else{
	for(i=0; i<selectedLots.length; i++){
	var elisa = document.getElementById(selectedLots[i]); //or grab it by tagname etc
	elisa.href = "AddHLGrdng.php?ID="+Ids;  
	}	
	}
	
	
	
	console.log(selectedLots);
	
	});
	
	$(document).on('click','#Lot_id',function(e){	
	console.log(selectedLots);
	selectedLots=[];
	var LID=$(this).val();
	var MONTH=$('#MONTH').val();
			var YEAR=$('#YEAR').val();
	
	$.ajax({
		  url:'AjaxTestIQFLotList.php',
		  type:'POST',
		  data:{'MONTH' : MONTH,'YEAR': YEAR,'TYPE':'RETURN','LOT_ID':LID},
		  success:function(data){
			  // console.log(data);
			   $("#ATT_DATA").html(data);
             $('#example1').DataTable();
					}
			});
	})
	
		$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			//var MONTH=$('#MONTH').val();
			var YEAR=$('#YEAR').val();
			var FROM_DATE=$('#FROM_DATE').val();
			var TO_DATE=$('#TO_DATE').val();
			var COUNT=$('#COUNT').val();
			
			//alert(YEAR);
			
			
			$.ajax({
		  url:'AjaxTestIQFLotList.php',
		  type:'POST',
		  data:{'COUNT':COUNT,'FROM_DATE': FROM_DATE,'TO_DATE': TO_DATE},
		  success:function(data){
			  // console.log(data);
			   $("#ATT_DATA").html(data);
             $('#example1').DataTable();
					}
			});
			//return false;
			
    		  
    
    	});	


/***uma 21/07/2022 ***/
$(document).on('click','#BtnAddIqf',function(e){			
			e.preventDefault();
		//	alert("INT_LOT_NUM Required");

			var LotID=$(this).val();
			//alert(INT_LOT_NUM);
		
			//var UNIT=$('#JME_MOURYA').val();
			if(LotID ==''){
				$("#LotID").focus();
				alert("LotID Number Required");
				return;
			}else{
				
				

		$.ajax({
		   url:'AjPopUpPoStatus.php',
		  type:'POST',
		  data:{'LotID':LotID,'TYPE':'KK'},
		  success:function(data){
			 
			 $('.modal-title').text('Select Dummy or Finished');
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			}
    	});	
		
		
		$(document).on('click','.dummy',function(e){
	e.preventDefault();
	//var InvId=$(this).val();
	var LotID=$(this).attr('id');
		/*var DummyMaster='DummyMaster'+LotID;

		var DummyMaster=$('#'+DummyMaster).val();*/
	window.open("/Production/AdIQFRprt.php?ID=" + LotID, "_blank");
	


	
});

$(document).on('click','.master',function(e){
	e.preventDefault();
	//var InvId=$(this).val();
	var LotID=$(this).attr('id');
		/*var DummyMaster='DummyMaster'+LotID;

		var DummyMaster=$('#'+DummyMaster).val();*/

	window.open("/Production/AddiqfReport.php?ID=" + LotID, "_blank");
	

	
});
		


/***uma 21/07/2022 ***/
		
		
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
		
    
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
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
