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
  
  $Startyear=$currently_selected - 1;
  $endYear=$currently_selected + 1;

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
                  <select class="form-control Select2" name="YEAR" id="YEAR">
				  
				    <option value="">Select Year</option>
                  <?php
														
                   for($Startyear; $Startyear <= $endYear; $Startyear++) {
                                                            
                echo '<option value="'.$Startyear.'">'.$Startyear.'</option>';
                                                             
                                      } 
                                                         
                          ?>
				  <select>
				  </div>
				  
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Month </label>
                  <select class="form-control" name="MONTH" id="MONTH">
				  <?php
				  $start = strtotime('first day of this month');
                    for ($i = 0; $i < 12; $i++) {
                        $time = strtotime(sprintf('%d months', $i), $start);   
													   
                       $label = date('F', $time);   
                       $value = date('n', $time);
                       echo "<option value='$value'>$label</option>";
                         }  ?>
				 	<select>
				  </div>
				  
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">PAN</label>
                  <select class="form-control" name="PAN" id="PAN">
				   <option value="0">WITH PAN</option>
				   <option value="1">WITHOUT PAN</option>

				  <select>
				  </div>
				  
				 
				  
				  
				   
				  </div>
				  
				  <div class="footer">
				  <div align="center" class="row" >
		 
				<button id="list" onclick="getData()" align="right" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
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
