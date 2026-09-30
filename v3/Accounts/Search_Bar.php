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

//uma
  $currently_selected = date('Y');
   $ending_year = date('Y', strtotime('+2 year'));
  

if($_GET['YEAR']!=''){
	$year= $_GET['YEAR'];
}else{
	$year= date("Y");
}

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
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
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

		
      ?>
      <div class="row">
        <div class="col-xs-12">
		 <div class="box-body">
		
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit</label>
                  <select class="form-control" name="UNIT" id="UNIT">
				  <!-- <option value="2019">2019</option> ROBO-->
				  
				   <option value="0">BOTH</option>
				   <option value="1">JME</option>
				   <option value="2">Mourya</option>
				  <select>
				  </div>
				 
				  
				   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Year</label>
                  <select class="form-control" name="YEAR" id="YEAR">
				  
				  <!-- <option value="2019">2019</option> -->
				  
				 
				   <option value="2019" <?php if($year==2019) echo 'selected="selected"'; ?>>2019</option>
				   <option value="2020" <?php if($year==2020) echo 'selected="selected"'; ?>>2020</option>
				   <option value="2021" <?php if($year==2021) echo 'selected="selected"'; ?>>2021</option>
				 	
				  <select>
				  </div>
				  
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Month </label>
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
				  </div>
				  
				 
				  
				  
				  
				  </div>
				  
				  <div class="footer">
				  <div align="center" class="row" >
		 
				<button id="list" align="right" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i> Get List</button>
				
				<!--<button id="print" align="right" class='btn btn-warning'><i class='fa fa-print'>
				</i> Print Lots</button> -->
				
				
				</div>
				  
				  </div>
		
				  
		
          <div class="box">
		 
	  
            <div class="box-body" id="ATT_DATA">
              
            </div>
          </div>
        </div>
      
	  
	  
	  </div>
    </section>  
