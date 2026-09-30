<?php

session_start();
include "../includes/config.php";

 
$uid=$_SESSION['r_id'];



$INV_ID=$_GET['id'];
$PO_NUM=$_GET['PO_NUM'];

if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}


 ?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
 </head>
<body class="hold-transition skin-blue sidebar-mini fixed">

<?php 
	

$sql_inv="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$INV_ID'";
$sql_declearation="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$INV_ID'";
$sql_elisa="select * from IH_ELSIA_TEST_REPORT WHERE INVOICE_ID='$INV_ID' ";
$sql_elisa_ph="select * from IH_ELSIA_PH_TEST_REPORT WHERE INVOICE_ID='$INV_ID' ";
$sql_microb="select * from IH_MICROBIOLOGY_TEST_REPORT WHERE INVOICE_ID='$INV_ID' ";
$sql_c_microb="select * from BUYER_MB_REPORT WHERE INVOICE_ID='$INV_ID' ";
$sql_salmonella="select * from SALMONELLA_TEST_REPORT  WHERE INVOICE_ID='$INV_ID' ";
$sql_sample="select * from SAMPLE_REG_FORM_DATA  WHERE INVOICE_ID='$INV_ID' ";
$sql_eia="select * from EIA_REPORTS  WHERE INVOICE_ID='$INV_ID' ";
$sql_code_list="select * from INVOICE_DATA  WHERE ID='$INV_ID' ";
$sql_short_shipment="select * from S_SHIPMENT_REG  WHERE INV_ID='$INV_ID' ";
$sql_dsp="select * from DSP_REPORT  WHERE INV_ID='$INV_ID' ";
$sql_labels="SELECT * FROM `PO_LABELS`  WHERE INV_ID='$INV_ID' ";


$stmt_inv=$iconn->query($sql_inv);
$row_inv=$stmt_inv->fetch_array();

$stmt_declearation=$iconn->query($sql_declearation);
$stmt_elisa=$iconn->query($sql_elisa);
$stmt_elisa_ph=$iconn->query($sql_elisa_ph);
$stmt_shrimp_ph=$iconn->query($sql_elisa_ph);
$stmt_microb=$iconn->query($sql_microb);
$stmt_c_microb=$iconn->query($sql_c_microb);
$stmt_salmonella=$iconn->query($sql_salmonella);
$stmt_sample=$iconn->query($sql_sample);
$stmt_eia=$iconn->query($sql_eia);
$stmt_code_list=$iconn->query($sql_code_list);
$stmt_code_list_mpeda=$iconn->query($sql_code_list);
$stmt_short_shipment=$iconn->query($sql_short_shipment);
$stmt_dsp=$iconn->query($sql_dsp);
$stmt_lables=$iconn->query($sql_labels);



?>

<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
	
 <button class="btn btn-primary btn-sm btn-flat" type="submit" name="confirm" value="<?php  echo $INV_ID ?>" id="payBtn"><i class="fa fa-plus"></i> Click to Add Reports</button></td>
	   
	   	
	<ol class="breadcrumb">
        <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">View Avaiable Reports</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
	
	<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body">
             			
						
						 <table id="example" class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">
								
								
						<?php if($row_inv['DEC_STATUS']=='0') { ?>
								
								<tr id="td_code_list"><td>Code List </td>
								 <td><a  id='code_list' href="" class="btn btn-primary" > <i class="fa fa-plus"> Create Report</a></td>
								</tr>
						
						<?php }else   {	?>	
								<tr id="tr_code_list"><td>Code List </td>
								 <td><a  id='code_list' href="" class="btn btn-primary" > <i class="fa fa-plus"> Create Report</a></td>
								</tr>
						
								<tr id="tr_c_note"><td>Covering Note</td>
								 <td><a  id='c_report' href="" class="btn btn-primary" > <i class="fa fa-plus"> Create Report</a></td>
								</tr>
								<tr id="tr_d_note"><td>Declaration Letter</td>
								 <td><a  id='d_report' href="" class="btn btn-primary" > <i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_s_ship"><td>Short Shipment Delaration</td>
								 <td><a  id='short_shipment' href="" class="btn btn-primary" > <i class="fa fa-plus"> Create Report</a></td>
								</tr>

                                 <tr id="tr_e_r"><td>Internal Elisa Test Report -EIA</td>
								 <td><a  id='elisa' href="" class="btn btn-primary" > <i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_e_ph"><td>Elisa Pre-Harvest Test Report -EIA</td>
								 <td><a  id='ph_elisa' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_mb_r_t"><td>Microbiology Report(TRACEABILITY Wise) -EIA</td>
								 <td><a  id='microbiology' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_s_e_lab"><td>Covering Letter for sampling External Labs Drawn Sample </td>
								 <td><a  id='sample_reg' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr> 
								
								<tr id="tr_c_letter_eia"><td>Covering Letter for drawn sample - EIA </td>
								 <td><a  id='eia_report' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_c_mb"><td>Microbiology Report(RAW/COOKED)</td>
								 <td><a  id='c_mb_report' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_salmonella"><td>Salmonella Report</td>
								 <td><a  id='salmonalla' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_mpeda_pr"><td>Purchase Register</td>
								 <td><a  id='mpeda_pr' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_mpeda_dsp"><td>DSP Report</td>
								 <td><a  id='mpeda_dsp' href="" class="btn btn-primary"><i class="fa fa-plus"> Create Report</a></td>
								</tr>
								
								<tr id="tr_label"><td>Label Details</td>
								 <td><a  id='label_pr' href="" class="btn btn-primary"><i class="fa fa-plus"> Upload Label </a></td>
								</tr>
						<?php } ?>
								
								</thead>
								
								</table>

								
						
						
						
						
								
								
								
								
								
								
								
								
								
								
				
				
				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	<h3 align='center'>Reports for Purchase Order -<span style="color:Tomato; bold"><?php echo $row_inv['PO_NUM'] ?></span></h3>
	
	
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body nav-tabs-custom">
		
		<ul class="nav nav-tabs">
   

  
  <li><a data-toggle="tab" href="#<?php echo  $value ?>"><?php echo  $value ?></a></li>
  
    <li id="value" value="home" class="active"><a  data-toggle="tab" href="#home">Export Documentation</a></li>
    <li><a data-toggle="tab" href="#menu1">Covering Letter for Testing</a></li>
    <li><a data-toggle="tab" href="#menu2">Internal Test Reports</a></li>
    <li><a data-toggle="tab" href="#MPEDA">MPEDA DOCS</a></li>
    <li><a data-toggle="tab" href="#SHRIMP">SIMP DOCS</a></li>   
   
    <li><a data-toggle="tab" href="#label">LABELS</a></li>
	<li><a data-toggle="tab" href="#menu">Product Inspection</a></li>
    
	
 
  </ul>

                    
 <div class="tab-content">

  <div id="SHRIMP" class="tab-pane fade">
     <table id="example1" class="table table-bordered table-hover">
             
    <thead>
	<tr>
		<th>Avaiable Reports</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>
  
  	<?php
   while($row=$stmt_shrimp_ph->fetch_array()){

	?>

      <tr>

		
		
		</td>
		
		 <td id="p_number<?php  echo $row['C_ID'];?>">SIMP & FILTH<span style="color:red; bold">
		 
		( <?php  
		
		$CODE_LIST=$row['FARM_ID_POND_ID']; 
		
		$SQL_CODE_LIST="select * from CODE_LIST_CREATE WHERE CODE_LIST_ID='$CODE_LIST'";		
		
		$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
		
		$CODE_ROW=$STMT_CODE_LIST->fetch_array();
		//uma
		$CODE_LIST_ID=$CODE_ROW['CODE_LIST_ID'];
		
		echo $CODE_ROW['FARM_REG_ID'];
		$CLR='chocolate';
		$COLOR='crimson';
		
		?> 
		
		)</span></td>
		
      <td style="width:300px;"> 
	  
	  <a href="DocEditPHRprt.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" style="background:<?php echo $COLOR; ?>"class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  
	   
	  
	   <a href="DocShrimpDoc.php?id=<?php  echo $row['ID'];?>" target="_blank" style="background:<?php echo $CLR; ?>"class="btn btn-primary btn-sm glyphicon-print"> SIMP  Print</a>

	  <a href="DocFilthPrint.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID.'&CODE_LIST_ID='.$CODE_LIST_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print"> FILTH  Print</a>
	  
	  </td>		

      </tr>  

	  <?php 

   }

	  ?>
	  
	  </tbody>
	  </table>
	  
  </div>
  
  
  
  
  
  <div id="menu" class="tab-pane fade in active"></div>
  
  
  
    <div id="home" class="tab-pane fade in active">
					
  <table id="example1" class="table table-bordered table-hover">
             
    <thead>
	<tr>
		<th>Avaiable Reports</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>
	<?php
   while($row=$stmt_declearation->fetch_array()){
	   IF($row['DEC_STATUS']==2){ 
	?>
		<tr>
		<td id="p_number<?php  echo $row['C_ID'];?>">Cosignment Covering Note for -EIA</td>
		<td style="width:300px;"> 	  	
	 	<a href="DocDecltrEdit.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>
		<a href="DocCoverNotePrint.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	   <tr>
		<td id="p_number<?php  echo $row['C_ID'];?>">Payment Slip</td>
		<td style="width:300px;"> 	  	
		<a href="DocFeePayments.php?id=<?php  echo $row['ID'] ?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	   
	   <tr>
		<td id="p_number<?php  echo $row['C_ID'];?>">Declearation Letter</td>
		<td style="width:300px;"> 	  	
	 	<a href="DocDecltrEdit.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>
		<a href="DocDeclarationPrint.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	  <?php 
   } }
?>	
	



<?php

   while($row=$stmt_short_shipment->fetch_array()){

	?>		
		<td id="p_number<?php  echo $row['C_ID'];?>">Short Shipment Letter</td>
      <td style="width:300px;"> 
	  
	  	<a href="DocShipmentCreate.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocShShipmentPrint.php?id=<?php  echo $INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	  <?php 
   }
?>


<?php

   while($row=$stmt_code_list->fetch_array()){ if($row['DEC_STATUS']>'0') {

	?>		
	<tr>
		<td id="p_number<?php  echo $row['C_ID'];?>">Code List -EIA</td>

		
		

      <td style="width:300px;"> 
	  
	  	<a href="DocClEdit.php?id=<?php  echo $INV_ID;;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocEiaClPrint.php?id=<?php  echo $INV_ID.'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>	

			

		 </tr>
		
		
		<tr>
		
		<td id="p_number<?php  echo $row['C_ID'];?>">Sampling WorkSheet</td>
		<td style="width:300px;"> 
	  
	  	<a href="DocClEdit.php?id=<?php  echo $INV_ID;;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocSamplingWsPrint.php?id=<?php  echo $INV_ID.'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>	
	  </tr>
		

      
	  
      
	  <?php 
   } }
?>

	  
	  

	  
	  
	

	




	  

	 

     </tbody>

  </table>
  
  </div>
  
  
  
  
 	<div id="menu1" class="tab-pane fade">
        <table id="example1" class="table table-bordered table-hover">
             
    <thead>
	<tr>
		<th>Avaiable Reports</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>
<!-------------------------------------------	Covering Letter for drawn sample-------------------------------------->
	<?php
		while($row=$stmt_eia->fetch_array()){ ?>

		<td id="p_number<?php  echo $row['C_ID'];?>">Covering Letter for drawn sample - EIA<span style="color:red; bold">
		
		( <?php echo $row['PRODUCT_NAME']  ?> )</span>
		
		</td>
		

      <td style="width:300px;">

	  <a href="DocCvLetterEIAEdit.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>"  
	  class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>
	  
	  <a href="DocCvEiaLetterPrint.php?id=<?php  echo $row['ID'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon 
	  glyphicon-print">  Print</a>
	  </td>		
        </tr>

	  <?php 

   }
?>


<!-------------------------------------------	Covering Letter for drawn sample-------------------------------------->

<!-----------------------------------------	Covering Letter for sampling External Labs Drawn Sample -------------------------->

	  <?php

	//echo $sql;

	

   while($row=$stmt_sample->fetch_array()){

	

	?>

	


     

 
       
		

		<td id="p_number<?php  echo $row['C_ID'];?>">Covering Letter for sampling External Labs Drawn Sample <span style="color:red; bold">
		
		( <?php echo $row['TYPE_N_NATURE']  ?> )</span> </td>
		

      <td style="width:300px;"> 
	  
	  	  <a href="DocEditSamplLetterExtrnl.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>"  class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocSamplLetterPrint.php?id=<?php  echo $row['ID'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		

	 				


        

        </tr>

	  <?php 

   }

	  ?>

<!----------------------------	Covering Letter for sampling External Labs Drawn Sample ------------------------>
	
	</tbody>

  </table>
    </div>
	
	<div id="menu2" class="tab-pane fade">
              <table id="example2" class="table table-bordered table-hover">
             
    <thead>
	<tr>
		<th>Avaiable Reports</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>
	
	

	
	<?php

   while($row=$stmt_elisa->fetch_array()){

	?>
	
		
		
		
		
		<td id="p_number<?php  echo $row['C_ID'];?>">Elisa Report<span style="color:red; bold">
		
		( <?php  
		
		 $CODE_LIST=$row['FARM_REG_NO']; 
		
		$SQL_CODE_LIST="select * from CODE_LIST_CREATE WHERE CODE_LIST_ID='$CODE_LIST'";		
		
		$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
		
		$CODE_ROW=$STMT_CODE_LIST->fetch_array();
		
		ECHO $CODE_ROW['FARM_REG_ID'];
		 
		?> 
		
		)</span></td>


		
		

      <td style="width:300px;"> 
	  
	  	<a href="DocEditElisa.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocElisaPrint.php?id=<?php  echo $row['ID'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	  <?php 
   }
?>

	<?php

	//echo $sql;

	

   while($row=$stmt_elisa_ph->fetch_array()){

	

	?>

	


 	

      
		


		<td id="p_number<?php  echo $row['C_ID'];?>">Elisa Pre Harvest Report-EIA <span style="color:red; bold">
		
		( <?php  
		
		$CODE_LIST=$row['FARM_ID_POND_ID']; 
		
		$SQL_CODE_LIST="select * from CODE_LIST_CREATE WHERE CODE_LIST_ID='$CODE_LIST'";		
		
		$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
		
		$CODE_ROW=$STMT_CODE_LIST->fetch_array();
		
		ECHO $CODE_ROW['FARM_REG_ID'];
		 
		?> 
		
		)</span></td>

		

      <td style="width:300px;"> 
	  
	  <a href="DocEditPHRprt.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocEiaPhPrint.php?id=<?php  echo $row['ID'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  

	  </td>		

	 				


        

        </tr>

	  <?php 

   }

	  ?>
	  
	  
	  
	  <?php

	//echo $sql;

	

   while($row=$stmt_microb->fetch_array()){

	

	?>

	


     

 
       
		<td id="p_number<?php  echo $row['C_ID'];?>">Microbiology Report <span style="color:red; bold">
		
		( <?php  
		 
		$CODE_LIST=$row['FARM_REG_NO'];
		
		$SQL_CODE_LIST="select * from CODE_LIST_CREATE WHERE CODE_LIST_ID='$CODE_LIST'";		
		
		$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
		
		$CODE_ROW=$STMT_CODE_LIST->fetch_array();
		
		ECHO $CODE_ROW['FARM_REG_ID'];
		
		?> 
		
		)</span></td>

		

      <td style="width:300px;"> 
	  
	  	  <a href="DocEditMBReport.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocMBPrint.php?id=<?php  echo $row['ID'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		

	 				


        

        </tr>

	  <?php 

   }

	  ?>
	  
	  
	  <?php

	//echo $sql;

	

   while($row=$stmt_c_microb->fetch_array()){

	

	?>

	


     

 
       

		<td id="p_number<?php  echo $row['C_ID'];?>">Commbined Microbiology Report-EIA<span style="color:red; bold">( <?php  echo $row['VARITY']?> )</span></td>

		

      <td style="width:300px;"> 
	  
	 
	  
	  <a href="DocEditCmbReport.php?id=<?php  echo $row['ID'].'&COUNTRY='.$row['COUNTRY'].'&INV_ID='.$INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>
	  <a href="DocAddCombinedResult.php?id=<?php  echo $row['ID'].'&COUNTRY='.$row['COUNTRY'];?>" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">Add More </a>
	  <a href="DocCmbp.php?id=<?php  echo $row['ID'].'&COUNTRY='.$row['COUNTRY'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		



        </tr>

	  <?php 

   }

	  ?>
	  
	  
	  <?php

	//echo $sql;

	

   while($row=$stmt_salmonella->fetch_array()){

	

	?>

	
	

     

 


		<td id="p_number<?php  echo $row['C_ID'];?>">Salmonella Report</td>


      <td style="width:300px;"> 
	  
	  <a href="Edit_SalmonellaReport.php?id=<?php  echo $row['ID'].'&INV_ID='.$INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>
	  <a href="DocSalmonellaPrint.php?id=<?php  echo $row['ID'];?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		

	 				


        

        </tr>

	  <?php 

   }

	  ?>
	  
	</tbody>

  </table>
	  
	  
    </div>
	
	
	  <div id="MPEDA" class="tab-pane fade">
   
   <table id="example1" class="table table-bordered table-hover">
             
    <thead>
	<tr>
		<th>Avaiable Reports</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>
  
  <?php

   while($row=$stmt_code_list_mpeda->fetch_array()){ if($row['DEC_STATUS']>'0') {
 

	?>		
		<td id="p_number<?php  echo $row['C_ID'];?>">Code List -MPEDA</td>

		
		

      <td style="width:300px;"> 
	  
	  	<a href="DocClEdit.php?id=<?php  echo $INV_ID;;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocMpedaCodeList.php?id=<?php  echo $INV_ID.'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
	  
	  </tr>

		
		
	   
	   
	   		<tr>
<td id="p_number<?php  echo $row['C_ID'];?>">Purchase Register -MPEDA</td>
      <td style="width:300px;"> 
	  
	  	<a href="DocEditPHRprt.php?id=<?php  echo $INV_ID;;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocMpedaRegister.php?id=<?php  echo $INV_ID.'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	  <?php 
   } 
   
   }
   
  

   
   while($row=$stmt_dsp->fetch_array()) {

	
   
?>


<tr>
		<td id="p_number<?php  echo $row['C_ID'];?>">Ds Report -MPEDA</td>
      <td style="width:300px;"> 
	  
	  	<a href="DocAddDsReport.php?id=<?php  echo $INV_ID;?>" class="btn btn-success btn-sm  btn-flat"><i class="fa fa-edit"></i> Edit</a>

	  <a href="DocDsReport.php?id=<?php  echo $INV_ID.'&INV_ID='.$INV_ID;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		
       </tr>
	   
	     <?php 
   } 
   


	
   
?>
  </tbody>
  </table>
  </div>
  
  
   	<div id="label" class="tab-pane fade">
        <table id="example1" class="table table-bordered table-hover">
             
    <thead>
	<tr>
		<th>Avaiable Reports</th>
		<th>Actions</th>
	</tr>
    </thead>
<tbody>
	  <?php

	//echo $sql;

	

   while($row=$stmt_lables->fetch_array()){

	

	?>

		<td id="p_number<?php  echo $row['C_ID'];?>">Label </span> </td>
		

      <td style="width:300px;"> 

	  <a href="po_labels/<?php  echo $row['FILE_NAME'] ;?>" target="_blank" class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  Print</a>
	  </td>		

        </tr>

	  <?php 

   }

	  ?>

<!----------------------------	LABELS ------------------------>
	
	</tbody>

  </table>
    </div>
	
	
	<div>
</div>
</div>
</div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  </div>
 <?php include "../includes/footer.php"; ?>
</body>



<Script>

$('document').ready(function(){
	var tab_cn='home';
	
	 $('.nav-tabs a').click(function(){
        var id=$(this).val();
		//a//lert(id);
		//alert('test');
    });
    
	 $('.nav-tabs a').on('shown.bs.tab', function (e) {
		 // alert(tab_cn);
		  tab_cn = e.target;
       // alert(tab_cn);
		
     //   var current_tab = tab_cn.split("#");
       // var previous_tab = e.relatedTarget;
		
		//alert(current_tab[1]);
    });
	
    	$(document).on('click','#payBtn',function(e){
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();
						//alert(refid);


						         
                      
					   //console.log(refid);
					   
					   var status =<?php echo $row_inv['DEC_STATUS'] ?>;
					   
					   if(status==0){
						 var elisa = document.getElementById('code_list'); //or grab it by tagname etc
							elisa.href = "DocClCreate.php?id="+refid;  
					   }else {
						    var elisa = document.getElementById('code_list'); //or grab it by tagname etc
							elisa.href = "DocClCreate.php?id="+refid;  
						   
						   var elisa = document.getElementById('d_report'); //or grab it by tagname etc
							elisa.href = "DocDecltrEdit.php?id="+refid;
							
							var elisa = document.getElementById('c_report'); //or grab it by tagname etc
							elisa.href = "DocDecltrEdit.php?id="+refid;
							
							var elisa = document.getElementById('short_shipment'); //or grab it by tagname etc
							elisa.href = "DocShipmentCreate.php?INV_ID="+refid;
							
							var elisa = document.getElementById('elisa'); //or grab it by tagname etc
							elisa.href = "DocAddElsaRpt.php?id="+refid;
							
							var ph_elisa = document.getElementById('ph_elisa'); //or grab it by tagname etc
							ph_elisa.href = "DocAddPhRpt.php?id="+refid;
							
							var microbiology = document.getElementById('microbiology'); //or grab it by tagname etc
							microbiology.href = "DocAddCmbp.php?id="+refid;
							
							var sample_reg = document.getElementById('sample_reg'); //or grab it by tagname etc
							sample_reg.href = "DocSampleLetter.php?id="+refid;
							
							var eia_report = document.getElementById('eia_report'); //or grab it by tagname etc
							eia_report.href = "DocCVLetterEIA.php?id="+refid;
						
							var c_mb_report = document.getElementById('c_mb_report'); //or grab it by tagname etc
							c_mb_report.href = "DocAddCombinedRprt.php?id="+refid;
						
							var salmonalla = document.getElementById('salmonalla'); //or grab it by tagname etc
							salmonalla.href = "DocAddSalmonella.php?id="+refid;
							
							var mpeda_pr = document.getElementById('mpeda_pr'); //or grab it by tagname etc
							mpeda_pr.href = "DocAddMpedaPurchaseReg.php?id="+refid;
							
							var mpeda_dsp = document.getElementById('mpeda_dsp'); //or grab it by tagname etc
							mpeda_dsp.href = "DocAddDsReport.php?id="+refid;
							
							var mpeda_dsp = document.getElementById('label_pr'); //or grab it by tagname etc
							mpeda_dsp.href = "../UploadLabel.php?id="+refid;
							
							
							
						
					   }

					   
					   //  alert(tab_cn);
					   //console.log(tab_cn.href);
						
						
						if(tab_cn=='home'){
							var ref='home';
						}else{
							testt=tab_cn.href;
					        var current_tab = testt.split("#");
							var ref=current_tab[1];
							
						}
						
						//alert(current_tab[1]);
					  
						  $('#tr_code_list').hide(); 
						  $('#tr_c_note').hide(); 
						  $('#tr_d_note').hide(); 
						  $('#tr_s_ship').hide(); 
						  $('#tr_e_r').hide(); 
						  $('#tr_e_ph').hide(); 
						  $('#tr_mb_r_t').hide(); 
						  $('#tr_s_e_lab').hide(); 
						  $('#tr_c_letter_eia').hide(); 
						  $('#tr_c_mb').hide(); 
						  $('#tr_salmonella').hide(); 
						  
						   $('#tr_mpeda_pr').hide(); 
						  $('#tr_mpeda_dsp').hide(); 
						  $('#label_pr').hide(); 

					  if(ref=='home'){
						  $('#tr_code_list').show(); 
						  $('#tr_c_note').show(); 
						  $('#tr_d_note').show(); 
						  $('#tr_s_ship').show(); 
						
					   }else if(ref=='menu1'){
						  $('#tr_c_letter_eia').show(); 
						  $('#tr_s_e_lab').show(); 						 						
					   }else if(ref=='menu2'){
						  $('#tr_e_r').show(); 
						  $('#tr_e_ph').show(); 						 						
						  $('#tr_mb_r_t').show(); 						 						
						  $('#tr_c_mb').show(); 						 						
						  $('#tr_salmonella').show(); 						 						
					   }else if(ref=='MPEDA'){
						  $('#tr_mpeda_pr').show(); 
						  $('#tr_mpeda_dsp').show();  
					   }else if(ref=='label'){
						  $('#label_pr').show(); 
						 
					   }
					   
					  
					   
							
							
							
						
						
						
						$('.modheading').text('Choose Report');

						$('#myModal').modal('toggle');						

		 
			
			//return false;
			 
    		
    
    	});
    
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
    });    

</script>





</html>
