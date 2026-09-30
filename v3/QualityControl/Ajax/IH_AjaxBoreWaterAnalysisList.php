<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $TYPE=$_POST['TYPE']; 
 $FROM_DATE=$_POST['FROM_DATE']; 
 $TO_DATE=$_POST['TO_DATE']; 
 
 /* $date_start=$FROM_DATE; 
  $date_end=$TO_DATE'23:59:59'; 
*/
 
 
$uid=$_SESSION['r_id'];



 if($TYPE=='1'){
   $sql="SELECT * FROM `QcIHBoreWater` WHERE SampleDate BETWEEN '$FROM_DATE' and '$TO_DATE' order by ID desc"; 
   
   $PrintPage='Reports/IHDlyBoreWaterReport.php'; 
   $EditPage='IHAddBoreWaterAnlys.php'; 
   
 }else if($TYPE=='2'){
	 
$sql="SELECT * FROM `QcOutletWaterAnalysis` WHERE SampleDate BETWEEN '$FROM_DATE' and '$TO_DATE' order by ID desc"; 
	 
	$PrintPage='Reports/IHDlyOutletWaterReport.php';  
	$EditPage='IHAddOutletWater.php';
	
 }else if($TYPE=='3'){
	 
 $sql="SELECT * FROM `QcIHSWABBReprt` WHERE SampleDate BETWEEN '$FROM_DATE' and '$TO_DATE' order by ID desc"; 
	 
$PrintPage='Reports/IHSWABReport.php';
	$EditPage='IHAddSWABReport.php';
	$AddPage='IHAddSWABResult.php';

 	 
 }else if($TYPE=='4'){
	 
   $sql="SELECT * FROM `QcIHIceNWaterRprt` WHERE SampleDate BETWEEN '$FROM_DATE' and '$TO_DATE' order by ID desc"; 
	 
$PrintPage='Reports/IHSanitaryReport.php';	 
$EditPage='IHAddIce_WaterReport.php';
$AddPage='IHAddIce_WaterResult.php';

 }else if($TYPE=='5'){
	 
 $sql="SELECT * FROM `QcIHTPCMicroAnalysisRprt` WHERE SampleDate BETWEEN '$FROM_DATE' and '$TO_DATE' order by ID desc"; 
	 
$PrintPage='Reports/IHDlyWaterTPDMicroReport.php';	 
$EditPage='IHAddDlyWaterTPDMicroResult.php';
$AddPage='IHAddDlyWaterTPDMicroReport.php';

 }


//echo $PrintPage;

$stmt=$iconn->query($sql);  ?>



<table id="key-datatable" class="table dt-responsive nowrap w-100">
              

    <thead>

      <tr>
	  
	  
	 
        <th>S.NO</th>

        <th>Report Date</th>

        
		<th>Actions</th>

      

      </tr>

    </thead>

	

	

    <tbody>

	

	<?php

	//echo $sql;

	$s_no=1;

	
   while($row=$stmt->fetch_array()){

	

	?>

        <td id="<?php  echo $row['ID'];?>" style="width:280px;"> <?php echo $s_no; ?></td>
		
		<?php $SampleDate = $row['SampleDate']?>

        <td id="c_name<?php  echo $row['C_ID'];?>"><?php echo  date("d-m-Y", strtotime($SampleDate)) ?></td>

		

      <td style="width:280px;"> 
	  
		  
	  <div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                   
					
                    <li>
			<a target="_blank" class="btn btn-SUCCESS" 
			href="<?php echo $PrintPage ?>?ID=<?php echo $row['ID']?>"> 
	  <span class="glyphicon glyphicon-print"></span> Print  
	  
	  </a>
					</li>
					
					 <li>
			<a target="_blank" class="btn btn-EDIT" 
			href="<?php echo $EditPage ?>?ID=<?php echo $row['ID']?>"> 
	  <span class="glyphicon glyphicon-print"></span> EDIT  
	  
	  </a>
					</li>
				<?php IF($TYPE>=3){	?>
					 <li>
			<a target="_blank" class="btn btn-EDIT" 
			href="<?php echo $AddPage ?>?ID=<?php echo $row['ID']?>"> 
	  <span class="glyphicon glyphicon-print"></span> ADD More  
	  
	  </a>
					</li>
				<?php  } ?>
                  </ul>
                </div>
	  
	  
				 
						 
					
						 </td>		


        </tr>

	  <?php 

 $s_no++;  }

	  ?>

	  

	 

     </tbody>

  </table>
</div>

