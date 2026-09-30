<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $TYPE=$_POST['TYPE']; 
 $FromDate=$_POST['FromDate']; 
 $ToDate=$_POST['ToDate']; 
 
 
 
 
$uid=$_SESSION['r_id'];

 $sql_result="SELECT DISTINCT(ReportID) FROM `QcIHMBParmResult` WHERE AnalysisDate BETWEEN '$FromDate' and '$ToDate'

order by QcIHMBParmResult.ID desc";

$stmt_result=$iconn->query($sql_result);

    
   
   $PrintPage='Reports/IHMicroBIOLOGYReport.php'; 
   $EditPage='IHAddMBRprt.php'; 
   $AddMore='IHAddMBResult.php'; 
   
 

//echo $PrintPage;

?>



<table id="key-datatable" class="table table-hover table-bordered">
              

    <thead>

      <tr>
	  
	  
	 
        <th>S.NO</th>

        <th>PO No</th>
        <th>Buyer Name</th>
        <th>Report Date</th>

        
		<th>Actions</th>

      

      </tr>

    </thead>

	

	

    <tbody>

	

	<?php

	//echo $sql;

	$s_no=1;

	
   while($row_result=$stmt_result->fetch_array()){
	   $report_id=$row_result['ReportID'];


 /*$sql_mb="SELECT * FROM `QcIHMBParmResult` left join INVOICE_DATA on 
	INVOICE_DATA.ID=QcIHMBParmResult.InvID LEFT JOIN ScrmConsigneeDetails as scrm 
	on scrm.ConID=INVOICE_DATA.CONSIGNEE_ID WHERE ReportID='$report_id' order by QcIHMBParmResult.ID desc";*/
$sql_mb="SELECT * FROM `QcIHMBParmResult` 
left join ScrmPoData as SPD on SPD.PoID=QcIHMBParmResult.InvID LEFT JOIN ScrmConsigneeDetails as scrm on scrm.ConID=SPD.ConsigneeID 
WHERE ReportID='$report_id' order by QcIHMBParmResult.ID desc;";
	
	
	$stmt_mb=$iconn->query($sql_mb);  
$row_mb=$stmt_mb->fetch_array();



	/*$sql="SELECT * FROM `QcIHMBReport`  WHERE ID='$report_id' order by ID desc";  
$stmt=$iconn->query($sql);  
$row=$stmt->fetch_array();*/

	?>

        <td id="<?php  echo $row_mb['ID'];?>" style="width:280px;margin: 0px !important;padding: 0px 0px 0px 10px !important;"> <?php echo $s_no; ?></td>
		 <td id="c_name<?php  echo $row_mb['ID'];?>"><?php echo  $row_mb['PoNo'] ?></td>
        <td id="c_name<?php  echo $row_mb['ID'];?>"><?php echo  $row_mb['ConsigneeName'] ?></td>

		<?php $AnalysisDate = $row_mb['AnalysisDate']?>

        <td id="c_name<?php  echo $row_mb['ID'];?>"><?php echo  date("d-m-Y", strtotime($AnalysisDate)) ?></td>
       
		

      <td style="width:280px;margin: 0px !important;padding: 0px 0px 0px 10px !important;">
	  
		  
	  <div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                   
					
                    <li>
			<a target="_blank" class="btn btn-SUCCESS" 
			href="<?php echo $AddMore ?>?ReportID=<?php echo $row_mb['ReportID']?>"> 
	  <span class="glyphicon glyphicon-print"></span> Add More  
	  
	  </a>
					</li>
					 
					 
					 <li>
			<a target="_blank" class="btn btn-SUCCESS" 
			href="<?php echo $PrintPage ?>?ID=<?php echo $row_mb['ReportID']?>"> 
	  <span class="glyphicon glyphicon-print"></span> Print  
	  
	  </a>
					</li>
					
					 <li>
			<a target="_blank" class="btn btn-EDIT" 
			href="<?php echo $EditPage ?>?ID=<?php echo $row_mb['ReportID']?>"> 
	  <span class="glyphicon glyphicon-print"></span> EDIT  
	  
	  </a>
					</li>
				   
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

