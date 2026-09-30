<?php
session_start();
include "../includes/config.php";

include "../includes/Globals.php";

   $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

  //$date=$_GET['date']; 
$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
   

 //$month_name= date("F", strtotime($date)); 
 $month_name= date("F", strtotime($from_date)); 

  $originalDate1 = $to_date;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $from_date;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;

 //global $B_TYPE;
	/* $date_end=$date.'-31 23:59:59';  
	 $date=$date.'-01'; */
	
	//$date_end=$date.' 23:59:59';  
	$date_end=$to_date.' 23:59:59';  
	
	
	if($B_TYPE == 1){ 
	//$B_TYPE == 1;
	$query.="and imd.id='0'";}
  $sql_lots="SELECT * from inputmaterial_details as imd left join
	CS_PALLET_INPUT_DATA as SCK on imd.id=SCK.PURCH_LOT_NUM where SCK.DATE_TIME 
	BETWEEN '$from_date' AND '$date_end' $query  order by PURCHASE_DATE,LOT_NO asc"; 
			  
			   $sql_lots;
			   $stmt_lots=$iconn->query($sql_lots);
	
			  $S_NO=1;




?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
</head>
<body class="skin-blue sidebar-mini fixed sidebar-mini-expand-feature sidebar-collapse">


<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

	<section class="content-header">
	  <div class="row">
        <div class="col-md-12">

      <h3 ALIGN ="CENTER"><B>IQF PACKING(MELTING) REPORT ( </B><strong style="
    color: orangered; "><?php echo $date ?> )</strong> </h3> 
         
     
	    </div>
				<div class="btn-group"  ALIGN ="right">


                  <button type="button"  class="btn btn-info">Actions</button>

				 <button   type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<a target="_blank" href="#" id="btnPrint"
					class="btn btn-warning btn-sm  btn-flat glyphicon
						glyphicon">Print this to PDF  </a> 
					</li>
					<li>
					<a target="_blank" href="#"
					class="btn btn-success btn-sm  btn-flat glyphicon
						glyphicon">Download  </a> 
					</li>

				 </ul>



				 <div class="col-md-6">


 <!--<div id="pdf">
            <button id="btnPrint"><font size="3" color="red">print this to pdf</font></button>
        </div>
-->
				</div>
				</div>
    </section>

    <section class="content">
      <!-- Small boxes (Stat box) -->









      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >
        <div class="box">

        <div class="box-body">





      <div class="row">
	  <div class="col-xs-12">

				


				<table id="example1" border="1" cellspacing="0" cellpadding="1"
				class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">



								
				<th  align="center"> S No.</th>
				<th  align="center">TUB NO</th>
				<th  align="center"> ID NO</th>
                <th  align="center"> RM DATE</th>
                <th  align="center">IQF DATE</th>
                
                <th  align="center"> ITEM</th>
				<th  align="center"> COUNT</th>
				<th  align="center">GRADE</th>

				
				 <th  align="center"> BRAND</th>
				<th  align="center">CARTONS</th>
				<th  align="center">PACKING</th>
				<th  align="center">NET QTY</th>
				<th  align="center">REMARKS</th>

												 
							
							

								</thead>
								<tbody>
								
<?php while($r_lots = $stmt_lots->fetch_array()){
			$W=$r_lots['CARTONS_NUMBER']*$r_lots['TOTAL_SLABS'] * $r_lots['SLAB_WEIGHT'];
		
		if($r_lots['UOM']=='Lbs'){
		$N_W_KG=round(( $W * 454.4)/1000,2);
		}else if($r_lots['UOM']=='Grams'){
		$N_W_KG=round(($W )/1000,2);
		}else if($r_lots['UOM']=='Kgs'){
		$N_W_KG=round(($W ),2);
		}
		
		$PURCHZ_DATE=$r_lots['PURCHASE_DATE'];
		$PURCHASE_DT = date("d-m-Y", strtotime($PURCHZ_DATE));
	
	?>
	
	<tr>
  <td  align="CENTER" ><B><?php echo $S_NO ?></B></td>

  <td  align="LEFT" ><?php echo $r_lots['TUB_NO']?></td>
  <td  align="CENTER" ><?php echo $r_lots['LOT_NO']?></B></td>
  <td  align="CENTER" > <?php echo $PURCHASE_DT?></td>
  <td  align="CENTER"  > <?php echo $r_lots['PURCHASE_DATE']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['FORM']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['COUNT']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['GRADES']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['BRAND']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['CARTONS_NUMBER']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['PACKING_STYLE']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['NET_WT']?></td>
  <td  align="CENTER"  ><?php echo$r_lots['REMARKS']?></td>
  </tr>

	<?php $S_NO++;}?>
								</tbody>

								</table>





    				</div>
	  </div>
	  </div>

	          <div class="box-footer">

			   <div  align="center" class="form-group col-md-12 col-sm-12 col-xs-12">
				  <p><br></p>
         <!--        <button id="list"  class='btn btn-success btn-sm btn-flat'><i class= 'fa fa-search'>
				 </i> Get List</button>  -->



                 </div>

			  </div>




	  <div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		  <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>



	</div>



 <div class="box-body" id="ATT_DATA">



	</div>

        <!-- /.col (RIGHT) -->
      </div>
      <!-- /.row -->



        <!-- /.col (RIGHT) -->

	  </div>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  </div>
 <?php include "../includes/footer.php"; ?>
</body>






<script type="text/javascript">

 $('#example1').DataTable();

</script>


<script type="text/javascript" 
src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	 <script type="text/javascript">
			 $("#btnPrint").live("click", function () {


					divToPrint=document.getElementById("example1");
					var date="<?php  echo  $ShowDate ?>";
					var originalTitle = "GRADING DETAILS - "+date;
					var printWindow = window.open('', '', 'height=600,width=1000');

 				 	// printWindow.document.write(date.outerHTML);
 				 	 printWindow.document.write(originalTitle);
					 printWindow.document.write(divToPrint.outerHTML);
					 printWindow.document.close();
					 printWindow.print();
			 });
			 
			 
			 

</script>



</html>
