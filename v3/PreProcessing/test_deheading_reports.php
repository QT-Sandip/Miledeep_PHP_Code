<?php
session_start();
include "../includes/config.php";

include "../includes/Globals.php";

$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
  
//$UNIT=$_GET['U'];


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));

  $originalDate1 = $to_date;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $from_date;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;



 $UNIT_NAME='';
  if($UNIT ==0){

  $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS`";
  $StmtProStations=$iconn->query($SqlProStations);

  	while($Row=$StmtProStations->fetch_array()){
      $UNIT=$Row['ID'];
      $UNIT_NAME=$Row['UNIT_NAME'];
	  
	
	  
    }
  }else{
    $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
    $StmtProStations=$iconn->query($SqlProStations);
	$Row=$StmtProStations->fetch_array();
	$UNIT_NAME=$Row['UNIT_NAME'];
   
  }

//global $UNIT;

if($UNIT !=0){
  $Query="and PS.ID='$UNIT'";
}else{
    $Query="";
}
	$date_end=$to_date.' 23:59:59';


	 $sql_lots="SELECT
	  imd.LOT_NO,
	  imd.ANTI_BIOTIC_STATUS,
	  imd.PURCHASE_DATE,
	  imd.WEIGHT_IN_KGS,
	  VA.COUNT,
	  VA.PROCESSING_DATE,
	  VA.SENT_WEIGHT,
	  VA.WgtAfterValueAddition,
	  VA.HL_QTY_RCVD,
	  VA.RETURN_WEIGHT,
	  VA.VARIETY ,
	  VA.SHIFT ,
	  VA.REMARKS ,
	  PS.UNIT_NAME
	  from inputmaterial_details as imd
			 left join PPC_VALUE_ADDITION as VA on imd.id=VA.LOT_ID
			 left join RM_PROCESSING_STATIONS as PS on VA.STATION_ID =PS.ID
			  where VA.PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'
			  and VA.VARIETY ='DeHeading' $Query
			  order by PURCHASE_DATE ASC,LOT_NO ASC,VARIETY ASC";
			  
			  $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);

if($UNIT_NAME==''){
   $UNIT_NAME= $r_lots['UNIT_NAME'];
}


$SQL_PROCESSING_STATIONS="SELECT * FROM `RM_PROCESSING_STATIONS`";
$stmt_PROCESSING_STATIONS=$iconn->query($SQL_PROCESSING_STATIONS);	





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

      <h3 ALIGN ="CENTER"><B>DEHEADING REPORT( </B><strong style="
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
				
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SELECT STATION</label>
						
		 <select class="form-control" name="STATION" id="STATION">		   
 <?php while($row_PROCESSING_STATIONS=$stmt_PROCESSING_STATIONS->fetch_array()) { 
			

        $STATION_ID=$row_PROCESSING_STATIONS['ID'];			
				?>  

				
				<option Value="<?php echo $row_PROCESSING_STATIONS['ID'];?>">
				<?php echo $row_PROCESSING_STATIONS['UNIT_NAME'];?></OPTION>				
				<?php } ?>
				</select> 
                </div>

    </section>
	 <section class="content">
<button id="list" align="CENTER" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i> Get List</button>
  
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
				<th  align="center">LOT NO</th>
				<th  align="center"> RM Date</th>
                <th  align="center">De Heading Date</th>
                <th  align="center">HON Weight</th>
                <th  align="center">HON Count</th>
                <th  align="center">HON Qty Recieved</th>
                <th  align="center">HON Return</th>
                <th  align="center">HL Qty</th>
                <th  align="center">YEILD</th>
                <th  align="center">Remarks</th>
               								 
							
							

								</thead>
								<tbody>
						

<?php 
		$S_NO=1;

$TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $stmt_lots->fetch_array()){




$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$WgtAfterValueAddition=$r_lots['WgtAfterValueAddition'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];

$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;

	$yield=round(($WgtAfterValueAddition/($RECEIVED_WEIGHT-$RETURN_WEIGHT))*100,2);

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		$COUNT=$r_lots['EXP_COUNT'];

		//$STANDARD_YIELD=getStandardYeild($iconn,$COUNT);

		if($yield1 < $STANDARD_YIELD ){
			$COLOR='red';
		}else{
			$COLOR='GREEN';
		}





		$RETURN_WEIGHT= isset($r_lots['RETURN_WEIGHT']) ? $r_lots['RETURN_WEIGHT'] : "-";
	 
	?>
	
	<tr>
  <td  align="CENTER" ><B><?php echo $S_NO ?></B></td>

  <td  align="LEFT" ><?php echo $r_lots['LOT_NO'] ?></td>
  <td  align="CENTER" ><?php echo $PURCHASE_DATE ?></B></td>
  <td  align="CENTER"  > <?php echo $PROCESSING_DATE?></td>
  <td  align="CENTER"  > <?php echo $r_lots['SENT_WEIGHT']?></td>
  <td  align="CENTER"  > <?php echo $r_lots['COUNT']?></td>
  <td  align="CENTER"  > <?php echo $r_lots['HL_QTY_RCVD']?></td>
  <td  align="CENTER"  > <?php echo $RETURN_WEIGHT?></td>
  <td  align="CENTER"  > <?php echo $r_lots['WgtAfterValueAddition']?></td>
  <td  align="CENTER"  > <?php echo $yield1?></td>
  <td  align="CENTER"  > <?php echo $r_lots['REMARKS']?></td>
  </tr>

								<?php	$S_NO++;
 } ?>
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
					var originalTitle = "DEHEADING REPORT - "+date;
					var printWindow = window.open('', '', 'height=600,width=1000');

 				 	// printWindow.document.write(date.outerHTML);
 				 	 printWindow.document.write(originalTitle);
					 printWindow.document.write(divToPrint.outerHTML);
					 printWindow.document.close();
					 printWindow.print();
			 });
			 
			 
			 

</script>



</html>
