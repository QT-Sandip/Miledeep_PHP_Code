<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


  $StartDate = date("Y-m").'-01';
    $PurchaseDate = date("Y-m-d");
	 //$PURCHASE_DATE=date('d-m-Y', strtotime($PUR_DATE));

IF(ISSET($_SESSION['YEAR'])==''){
	$_SESSION['YEAR'] = date("Y");
	$_SESSION['MONTH'] = date("m");
}

$Year=$_SESSION['YEAR'];
$Month=	$_SESSION['MONTH'];
$HEDAER=$_SESSION['page'];
//$HEDAER="test";

/*if($_GET['MONTH']!=''){
	$Month= $_GET['MONTH'];
}else{
	 
	$Month= date("m");
$Month= trim($Month);

}*/


?> 
 
 
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

	  <div class="col-12">
                                <div class="card">
								
							
      <div class="card-body">
									
										<div class="row">
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit</label>
                  <select class="form-control select2" name="Unit" id="Unit">
				   <option value="1">SME</option>
				  <select>
				  </div>
				 
				   
				   <div class="form-group col-sm-3">
                    <label>Select From Date: </label>
                    <input type='date' value='<?php echo $StartDate;?>'  class="form-control" name="FromDate" id="FromDate"></input>
					</div>

					<div class="form-group col-sm-3">
					<label>Select To Date: </label>
                    <input type='date' value='<?php echo $PurchaseDate;?>' class="form-control" name="ToDate" id="ToDate"></input>
					</div>
				 
				  
				  
				  
				  </div>
				  					<div class="form-group col-sm-3">

				  <button id="list" align="right" class='btn btn-success btn-sm btn-flat'>	Get List</button>
				
								  </div>
 
          <div class="box">
		  
            <div class="box-body" id="ATT_DATA">
              
                                    </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
    </section>
				
<script>
/*if ( $('[type="date"]').prop('type') != 'date' ) {
    $('[type="date"]').datepicker();
}

*/

 function PrintLot() {

	var FromDate=$('#FromDate').val();
	var ToDate=$('#ToDate').val();
	var Unit=$('#Unit').val();
var URL='/Purchase/Excel.php?Type=Count&FromDate='+FromDate+'&ToDate='+ToDate;

	window.open(URL, '_blank');

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