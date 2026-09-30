<?php

session_start();
include "../includes/config.php";
include "../includes/Globals.php";

 
$uid=$_SESSION['r_id'];
 $ID=$_GET['id'];





if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password');
}

if(isset($_POST['submit'])){
	 $message = "Data Exist For Tracability";
		echo "<script type='text/javascript'>alert('$message');</script>";
}


 ?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
 </head>
<body class="hold-transition skin-blue sidebar-mini fixed">
<?php 
	
if($_GET['YEAR']!=''){
	$year= $_GET['YEAR'];
}else{
	$year= date("Y");
}


  $sql="select * from CODE_LIST_CREATE where INV_ID=$ID";


$stmt=$iconn->query($sql);
?>

<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
	
			<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
           
            <div class="modal-body">
             		
				<div class="form-group col-sm-12" >              									 
                <label for="exampleInputEmail1">PLEASE ENTER MICRO BIOLOGY REPORT </label>
				
                </div>			
			
            </div>
            <div class="modal-footer">			 
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


	


    </section>

    <!-- Main content -->
    <section class="content">
	
	


	
	
	<h3 align='center'> ANALYTIC REPORTS  LIST </h3>
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	 
       <th>Report ID</th>
       <th>Analytic Reports</th>
	   <th>Print/Edit</th>


      </tr>

    </thead>

	
			

    <tbody>

	

	<?php

	//echo $sql;

	

   while($row=$stmt->fetch_array()){

$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

if($DO_SAMPLING=='01-01-1970'){
	$DO_SAMPLING='*****';
}


	?>

	
        <td id="<?php  echo $row['ID'];?>" > <?php echo $row['CODE_LIST_ID']; ?></td>
		     
		<td id="<?php  echo $row['ID'];?>" > 
		<?php echo ' Analytic Reports:'.$row['FARM_REG_ID']; ?></td>
		
		
		</td>
      	
		
		

		

		<td style="width:200px;"> 
		
		
		<a href="DocAnalyticReport.php?id=<?php  echo $row['CODE_LIST_ID'];?>" target="_blank" 
		class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-print">  PRINT</a>
		
		<!--<a href="EdAnlytcRpt.php?id=<?php  //echo $row['CODE_LIST_ID'];?>" -->
		<a href="Edit_Analytic_Report.php?id=<?php  echo $row['CODE_LIST_ID'];?>" 
		style="background-color:green" target="_blank" 
		class="btn btn-primary btn-sm  btn-flat glyphicon glyphicon-edit">  ADD</a>
		
	 	</td>

        </tr>

	  <?php 

   }

	  ?>

	  

	 

     </tbody>

  </table>
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



<script>
  $(function () {
   $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});
  })
</script>




<script>
$(function(){
  $('#YEAR').change(function(){
    window.location.href = 'ProInspPoList.php?YEAR='+$(this).val();
  });
});
</script>

</html>
