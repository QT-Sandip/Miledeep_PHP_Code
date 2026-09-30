<?php
session_start();
include "../includes/config.php";

include "../includes/Globals.php";

   $date_start=$_GET['from_date'];
  $date_end=$_GET['to_date'];
 // $Agent=$_GET['Agent'];
  
  $originalDate1 = $date_end;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $date_start;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;

 $sql="SELECT DISTINCT
	imd.REFERENCE_ID,
	imd.LOT_NO,
	imd.PURCHASE_DATE,
	imd.COUNT,
	SCK.T_NO,
	SCK.SoakingDate,
	SCK.LOT_ID,
	SCK.Variety,
	SCK.QTY,
	SCK.C1,
	SCK.C2,
	SCK.APP_QT,
	SCK.DEST,
	SCK.TimePeriod,
	SCK.CreatedDateTime from inputmaterial_details as
	imd left join soaking_report as SCK on imd.id=SCK.LOT_ID where
	`SoakingDate` BETWEEN '$from_date' AND '$date_end'
	order by SoakingDate asc,LOT_NO asc,Variety asc"; 


 
  // $sql="SELECT DISTINCT(AGENT_NAME) AS AGENT from inputmaterial_details where
   //PURCHASE_DATE BETWEEN  '$date_start' AND '$date_end' order by AGENT_NAME ASC" ;
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

      <h3 ALIGN ="CENTER"><B>Soaking report ( </B><strong style="
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



								
				<th  align="center"> SI No.</th>
				<th  align="center">TUB NO</th>
				<th  align="center"> ID NO</th>
                <th  align="center"> RM DATE</th>
                <th  align="center">Soaking DATE</th>
                
                <th  align="center"> VARIETY</th>
				<th  align="center">GRADE</th>
				<th  align="center">IN QUANTITY</th>
				<th  align="center">IN COUNT</th>
				
				 <th  align="center"> TIME PERIOD</th>
				<th  align="center">OUT COUNT</th>
				<th  align="center">DEST</th>
				<th  align="center">OUT QUANTITY</th>
												 
							
							

								</thead>
								<tbody>
								
<?php while($rowAgent=$stmt->fetch_array()){
									
		 //$AGENT_NAME=$rowAgent['AGENT'];
									
		 //$sql="SELECT * FROM `inputmaterial_details`  WHERE AGENT_NAME='$AGENT_NAME' and 
			//		PURCHASE_DATE BETWEEN  '$date_start' AND '$date_end'  ORDER BY 
				//	PURCHASE_DATE,LOT_NO,AGENT_NAME asc"; 
  
		//$stmtLots=$iconn->query($sql);

		//while($row=$stmtLots->fetch_array()){
    
	$SNO++;
	$PURCHASE_DATE = strtotime($rowAgent['PURCHASE_DATE']);
$PURCHASE_DATE = date("d-m-Y", $PURCHASE_DATE);
$CreatedDateTime=date("d-m-Y", strtotime($rowAgent['CreatedDateTime']));
if($CreatedDateTime=='01-01-1970'){
	
	$CreatedDateTime='Not Updated';
}
	?>
	
	<tr>
  <td  align="CENTER" ><B><?php echo $SNO ?></B></td>

  <td  align="LEFT" ><?php echo $rowAgent['TUB_NO'] ?></td>
  <td  align="CENTER" ><?php echo $rowAgent['LOT_NO'] ?></B></td>
  <td  align="CENTER" > <?php echo $PURCHASE_DATE?></td>
  <td  align="CENTER"  > <?php echo $CreatedDateTime?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['Variety']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['COUNT']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['QTY']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['C1']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['TimePeriod']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['C2']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['DEST']?></td>
  <td  align="CENTER"  ><?php echo$rowAgent['APP_QT']?></td>
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
