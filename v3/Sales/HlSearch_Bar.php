<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


IF(ISSET($_SESSION['YEAR'])==''){
	$_SESSION['YEAR'] = date("Y");
	$_SESSION['MONTH'] = date("m");
}

$YEAR=$_SESSION['YEAR'];
$MONTH=	$_SESSION['MONTH'];
 $HEDAER=$_SESSION['page']; 
//$HEDAER="test";

?> 
 
 
    <!-- Main content -->
      
        <div class="col-12">
		                                <div class="card">

		 <div class="card-body">
		
				<div class="row">
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit</label>
                  <select class="form-control select2" name="UNIT" id="UNIT">
				  <!-- <option value="2019">2019</option> -->
				   <option value="1">SME</option>
				   
				  <select>
				  </div>
				 
				  
				  <div class="form-group col-lg-2">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="YEAR" id="YEAR">
                         
                           <?php
							 						
                            for($Startyear; $Startyear <= $endYear; $Startyear++) { 
						
									$year=($Startyear-1).'-'.($Startyear); ?>
                                                            
                        <option  
						
						<?php  if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>
							
                                                         
							<?php }   ?>
                   <select>
				  </div>

				  

				  
				   <div class="col-lg-3">
												
                                                    <div class="form-group">
                                                  <label for="exampleInputEmail1">Month</label>

                                                        <select class="form-control select2" name="MONTH" id="MONTH" size='1'>
                                                     <?php
													 
													  $start = strtotime('first day of this month');
                                                     for ($i = 0; $i < 12; $i++) {
                                                          $time = strtotime(sprintf('%d months', $i), $start);   
													   
                                                          $label = date('F', $time);   
                                                          $value = date('n', $time);
                                                          echo "<option value='$value'>$label</option>";
                                                         }
														 
			                                                   
                                                         ?>
                                                      </select>
													</div>
                                                </div>
				  
				  </div>
				  
				 
				  
				  
				  
				  
				  
				 
				
                <div align="right" class="col-lg-12">
                 
            <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button>
				 
                 </div>
				  
				
				
				  
				  </div>
				  </div>
		
				  
		
         <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="PIList">
                                        <!--<h4 class="card-title">Proforma Invoice List</h4>-->
                                        

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
        </div>
      
	  
	  
	  </div>
