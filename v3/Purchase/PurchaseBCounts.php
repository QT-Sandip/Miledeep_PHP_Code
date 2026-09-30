<?php
session_start();
include "../includes/config.php";

   $date_start=$_GET['from_date'];
  $date_end=$_GET['to_date'];
  
  $originalDate1 = $date_end;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $date_start;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;

if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password');
}


  $sql="SELECT DISTINCT(AGENT_NAME) AS AGENT,
(SELECT COUNT(*) FROM inputmaterial_details as C1 WHERE C1.AGENT_NAME=imd.AGENT_NAME and C1.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' ) as TOTAL ,
(SELECT COUNT(*) FROM inputmaterial_details as C1 WHERE C1.AGENT_NAME=imd.AGENT_NAME and C1.COUNT >= 30 and C1.COUNT < 34 and C1.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CONE,
(SELECT COUNT(*) FROM inputmaterial_details as C2 WHERE C2.AGENT_NAME=imd.AGENT_NAME and C2.COUNT >= 40 and C2.COUNT < 44 and C2.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CTWO,
(SELECT COUNT(*) FROM inputmaterial_details as C3 WHERE C3.AGENT_NAME=imd.AGENT_NAME and C3.COUNT >= 50 and C3.COUNT < 54 and C3.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CTHREE,
(SELECT COUNT(*) FROM inputmaterial_details as C4 WHERE C4.AGENT_NAME=imd.AGENT_NAME and C4.COUNT >= 60 and C4.COUNT < 64 and C4.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CFOUR,
(SELECT COUNT(*) FROM inputmaterial_details as C5 WHERE C5.AGENT_NAME=imd.AGENT_NAME and C5.COUNT >= 70 and C5.COUNT < 74 and C5.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CFIVE,
(SELECT COUNT(*) FROM inputmaterial_details as C6 WHERE C6.AGENT_NAME=imd.AGENT_NAME and C6.COUNT >= 80 and C6.COUNT < 84 and C6.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CSIX,
(SELECT COUNT(*) FROM inputmaterial_details as C7 WHERE C7.AGENT_NAME=imd.AGENT_NAME and C7.COUNT >= 90 and C7.COUNT < 94 and C7.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CSEVEN,
(SELECT COUNT(*) FROM inputmaterial_details as C8 WHERE C8.AGENT_NAME=imd.AGENT_NAME and C8.COUNT >= 100 and C8.COUNT < 104 and C8.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end') as CEIGHT

 FROM `inputmaterial_details` as imd WHERE imd.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' ORDER BY imd.AGENT_NAME asc"; 


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

      <h3 ALIGN ="CENTER"><B>Purchase - Border Counts ( </B><strong style="
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



								
								<td><B>S No</B></td>
								<td><B>Agent Name</B></td>
								<td><B>Supplied Lots</B></td>
								<td><B>30 - 33 C</B></td>
								<td><B>40 - 43 C</B></td>
								<td><B>50 - 53 C</B></td>
								<td><B>60 - 63 C</B></td>
								<td><B>70 - 73 C</B></td>
								<td><B>80 - 83 C</B></td>
								<td><B>90 - 93 C</B></td>
								<td><B>100 - 103 C</B></td>
								<td><B>Border Lots</B></td>
								<td><B>Percentage (%)</B></td>
								<td><B>Options</B></td>
									 
							
							

								</thead>
								<tbody>
								
								<?php while($row=$stmt->fetch_array()){
    $SNO++;
	$AGENT=$row['AGENT'] ;
	$DropCount=$row['CONE']+$row['CTWO']+$row['CTHREE']+$row['CFOUR']+$row['CFIVE']
	+$row['CSIX']+$row['CSEVEN']+$row['CEIGHT'];
	$DropCounts=round($DropCount/$row['TOTAL'],2)*100;
	?>
	<tr>
  <td  align="CENTER" ><B><?php echo $SNO ?></B></td>

  <td  align="LEFT" ><?php echo $row['AGENT'] ?></td>
  <td  align="CENTER" ><B><?php echo $row['TOTAL']?></B></td>
  <td  align="CENTER" > <?php echo $row['CONE']?></td>
  <td  align="CENTER"  > <?php echo $row['CTWO']?></td>
  <td  align="CENTER"  ><?php echo$row['CTHREE']?></td>
  <td  align="CENTER"  ><?php echo$row['CFOUR']?></td>
  <td  align="CENTER"  ><?php echo$row['CFIVE']?></td>
  <td  align="CENTER"  ><?php echo$row['CSIX']?></td>
  <td  align="RIGHT"  ><?php echo$row['CSEVEN']?></td>
    <td  align="RIGHT" ><?php echo$row['CEIGHT']?></td>
    <td  align="RIGHT" ><b><?php echo$DropCount?></b>	</td>
    <td  align="RIGHT" ><?php echo$DropCounts?> % </td>
	<td><a  target='_blank' href="BorderLotsByAgent.php?Agent=<?php echo$AGENT?>&date_start=<?php echo  $date_start ?>
	&date_end=<?php echo  $date_end ?>" class="btn btn-primary">view</a></td>

  </tr>

<?php } ?>
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
