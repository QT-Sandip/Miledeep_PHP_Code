<?php
include "../includes/config.php";
include "../includes/Globals.php";




$HEDAER=$_SESSION['page'];
//$HEDAER="test";
$sql_add_select="SELECT * FROM `$TblAdminExporterDetails`";
	$stmt_add_select=$iconn->query($sql_add_select);
	
	
	
?> 
 
 <section class="content-header">
      <h1> <?php echo $HEDAER ?>   </h1>
      <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
      <div class="row">
        <div class="col-xs-12">
		 <div class="box-body">
		
				<div class="col-xs-6">
                    <label>Select Exporter: </label>
				
				<select class="form-control" name="NAME_OF_PROCESSOR" id="NAME_OF_PROCESSOR">
				   
				   <?php while($row_add=$stmt_add_select->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'].' - '.$row_add['ExpApprovalNo'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div>
					
				
				 
				  
				   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Year</label>
                  <select class="form-control" name="YEAR" id="YEAR">
                         
                           <?php
														
                            for($Startyear; $Startyear <= $endYear; $Startyear++) { ?>
                                                            
                        <option  <?php if($Startyear == $Currentyear){ ?> selected="selected" <?php }?>
						
				   
				   value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>
							
                                                         
							<?php }   ?>
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
		 
				<button id="list"  align="right" 
				class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i>PRINT</button>
				
				</div>
				  
				  </div>
		
				  
		
          <div class="box">
		  <div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		  <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>
		
	  
            <div class="box-body" id="ATT_DATA">
              
            </div>
          </div>
        </div>
      
	  
	  
	  </div>
    </section>  
