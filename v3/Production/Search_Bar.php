<?php
include "../includes/config.php";


  $START_DATE = date("Y-m").'-01';
    $PUR_DATE = date("Y-m-d");
	 $PURCHASE_DATE=date('d-m-Y', strtotime($PUR_DATE));

IF(ISSET($_SESSION['YEAR'])==''){
	$_SESSION['YEAR'] = date("Y");
	$_SESSION['MONTH'] = date("m");
}

$YEAR=$_SESSION['YEAR'];
$MONTH=	$_SESSION['MONTH'];
$HEDAER=$_SESSION['page'];
//$HEDAER="test";

if($_GET['MONTH']!=''){
	$month= $_GET['MONTH'];
}else{
	 
	$month= date("m");
$month= trim($month);

}


?> 
 
 <section class="content-header">
      <h1> <?php echo $HEDAER ?>   </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?php echo $HEDAER ?></li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
			
          ";
		  
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
		 <div class="box-body">
		
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit</label>
                  <select class="form-control" name="UNIT" id="UNIT">
				  <!-- <option value="2019">2019</option> -->
				   <option value="1">SME</option>
				  <select>
				  </div>
				 
				  <!--
				   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Year</label>
                  <select class="form-control" name="YEAR" id="YEAR">
				  <!-- <option value="2019">2019</option> 
				   <option value="2020">2020</option>
				   <option value="2021">2021</option>
				  <select>
				  </div> -->
				  
				 <!-- <div class="form-group col-sm-2">
                	  <label>Select Month: </label>
                   <select class="form-control" name="MONTH" id="MONTH">
				   <option value="01" <?php if($month=='01'){ echo 'selected="selected"'; }?>>JAN</option>
				   <option value="02" <?php if($month=='02'){ echo 'selected="selected"'; }?>>FEB</option>
				   <option value="03" <?php if($month=='03'){ echo 'selected="selected"'; } ?>>MAR</option>
				   <option value="04" <?php if($month=='04') { echo 'selected="selected"'; } ?>>APR</option>
				   <option value="05" <?php if($month=='05') { echo 'selected="selected"'; } ?>>MAY</option>
				   <option value="06" <?php if($month=='06'){ echo 'selected="selected"'; } ?>>JUN</option>
				  
				   <option value="07" <?php if($month=='07'){ echo 'selected="selected"'; } ?>>JULY</option>
				   <option value="08" <?php if($month=='08'){ echo 'selected="selected"'; } ?>>AUG</option>
				   
				  <option value="09" <?php if($month=='09'){ echo 'selected="selected"'; } ?>>SEP</option>
				   <option value="10" <?php if($month=='10'){ echo 'selected="selected"'; } ?>>OCT</option>
				   <option value="11" <?php if($month=='11'){ echo 'selected="selected"'; }?>>NOV</option>
				   <option value="12" <?php if($month=='12'){ echo 'selected="selected"';} ?>>DEC</option>
				 	<select>
				  </div>  -->
				  
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Count</label>
                  <select class="form-control" name="COUNT" id="COUNT">
				  <!-- <option value="2019">2019</option> -->
				   <option value="">All</option>
				   <option value="30&34">30 - 33</option>
				   <option value="40&44">40 - 43</option>
				   <option value="50&54">50 - 53</option>
				   <option value="60&64">60 - 63</option>
				   <option value="70&74">70 - 73</option>
				   <option value="80&84">80 - 83</option>
				   <option value="90&94">90 - 93</option>
				   <option value="100&104">100 - 103</option>
				  
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
				  
				  <div class="footer">
				  <div align="center" class="row" >
		 
				<button id="list" align="right" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i> Get List</button>
				
				<!-- <button  id="lotTracking" onclick=PrintLot() class="btn btn-primary" >
								 <i class="fa fa-search"> </i>Excel</button>


			<button id="print" align="right" class='btn btn-warning'><i class='fa fa-print'>
				</i> Print Lots</button>
				
				-->
				</div>
				  
				  </div>
		
				  
		
          <div class="box">
		  
	  
            <div class="box-body" id="ATT_DATA">
              
            </div>
          </div>
        </div>
      
	  
	  
	  </div>
    </section>
				
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