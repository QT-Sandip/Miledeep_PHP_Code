<?php
session_start();
include "../includes/config.php";

   $date_start=$_GET['from_date'];
  $date_end=$_GET['to_date'];
  $Agent=$_GET['Agent'];
  
  $originalDate1 = $date_end;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $date_start;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;

if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password');
}

 
   $sql="SELECT DISTINCT(AGENT_NAME) AS AGENT from inputmaterial_details where
   PURCHASE_DATE BETWEEN  '$date_start' AND '$date_end' order by AGENT_NAME ASC" ;
   $stmt=$iconn->query($sql);

  




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

      <h3 ALIGN ="CENTER"><B>Purchase - Agentwise ( </B><strong style="
    color: orangered; "><?php echo $date ?> )</strong> </h3> 
         
     
	    </div>

				 <div class="col-md-6">




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

				


				<table id="example1" class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">



								
				<th  align="center"> S.No</th>
				<th  align="center"> AGENT NAME</th>
				<th  align="center"> RM Date</th>
                <th  align="center"> LOT NUMBER</th>
                <th  align="center"> GRADER</th>
                <th  align="center"> COUNT</th>
				<th  align="center"> H.ON QTY(Kgs)</th>
				<th  align="center"> VEHICAL </th>
				<th  align="center"> FARM LOCATION</th>
									 
							
							

								</thead>
								<tbody>
								
<?php while($rowAgent=$stmt->fetch_array()){
									
		 $AGENT_NAME=$rowAgent['AGENT'];
									
		 $sql="SELECT * FROM `inputmaterial_details`  WHERE AGENT_NAME='$AGENT_NAME' and 
					PURCHASE_DATE BETWEEN  '$date_start' AND '$date_end'  ORDER BY 
					PURCHASE_DATE,LOT_NO,AGENT_NAME asc"; 
  
		$stmtLots=$iconn->query($sql);

		while($row=$stmtLots->fetch_array()){
    
	$SNO++;
	$PURCHASE_DATE = strtotime($row['PURCHASE_DATE']);
$PURCHASE_DATE = date("d-m-Y", $PURCHASE_DATE);
	?>
	
	<tr>
  <td  align="CENTER" ><B><?php echo $SNO ?></B></td>

  <td  align="LEFT" ><?php echo $row['AGENT_NAME'] ?></td>
  <td  align="CENTER" ><?php echo $PURCHASE_DATE?></B></td>
  <td  align="CENTER" > <?php echo $row['LOT_NO']?></td>
  <td  align="CENTER"  > <?php echo $row['SUPERVISOR']?></td>
  <td  align="CENTER"  ><?php echo$row['COUNT']?></td>
  <td  align="CENTER"  ><?php echo$row['WEIGHT_IN_KGS']?></td>
  <td  align="CENTER"  ><?php echo$row['VEHICAL_NO']?></td>
  <td  align="CENTER"  ><?php echo$row['ADDRESS']?></td>

  </tr>

								<?php }} ?>
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



</html>
