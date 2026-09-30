<?php
include "../includes/config.php";


IF(ISSET($_SESSION['YEAR'])==''){
	$_SESSION['YEAR'] = date("Y");
	$_SESSION['MONTH'] = date("m");
}

$YEAR=$_SESSION['YEAR'];
$MONTH=	$_SESSION['MONTH'];
 $HEDAER=$_SESSION['page']; 
//$HEDAER="test";


$START_DATE = date("Y-m-d",strtotime("yesterday"));
$PUR_DATE = date("Y-m-d",strtotime("yesterday"));
$PURCHASE_DATE=date('d-m-Y', strtotime($PUR_DATE));


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
		
		if($_GET['MONTH']!=''){
	$month= $_GET['MONTH'];
}else{
	 
	$month= date("m");
$month= trim($month);

}

if($_GET['YEAR']!=''){
	$year= $_GET['YEAR'];
}else{
	 
	$year= date("Y");

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
				 

		<div class="col-md-3 col-sm-6 col-xs-12">
                    <label>Select From Date: </label>
                    <input type='date' value='<?php echo $START_DATE;?>'
					class="form-control" name="FROM_DATE" id="FROM_DATE"></input>
					</div>

						<div class="col-md-3 col-sm-6 col-xs-12">
					<label>Select To Date: </label>
                    <input type='date' value='<?php echo $START_DATE;?>' class="form-control"
					name="TO_DATE" id="TO_DATE"></input>
					</div>


				   
				  
				  
				  
				  </div>
				  
				  <div class="footer">
				  <div align="center" class="row" >
		 
				<button id="list" align="right" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i> Get List</button>
				
			
				
				
				</div>
				  
				  </div>
		
				  
		
          <div class="box">
		 
	  
            <div class="box-body" id="ATT_DATA">
              
            </div>
          </div>
        </div>
      
	  
	  
	  </div>
    </section>  
