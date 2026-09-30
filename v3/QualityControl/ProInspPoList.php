<?php

session_start();
include "../includes/config.php";
include "../includes/Globals.php";

 
$uid=$_SESSION['r_id'];

if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
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


  $sql="select * from INVOICE_DATA t1 where exists (select 1 from INSP_DATA t2 where t2.INV_ID = t1.ID ) AND t1.YEAR='$year' ";
//$sql="select * from INVOICE_DATA LEFT JOIN INSP_DATA ON INVOICE_DATA.ID=INSP_DATA.INV_ID   WHERE INVOICE_DATA.YEAR='$year' ";

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
<div class="row">
	
	 <div class="form-group col-sm-3">
        <!-- <a href="DocPoCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus">
		</i> Create New Purchase Report</a> -->
      </div>
	  
	  <div class="form-group col-sm-2">
		 <!-- <a href="TEST.PHP" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i>Download</a> -->
		 </div>
		 <div class="form-group col-sm-2">
                	
                   <select class="form-control" name="YEAR" id="YEAR">
                   
				   
				   <option value="2019" <?php if($year==2019) echo 'selected="selected"'; ?>>2019</option>
				   <option value="2020" <?php if($year==2020) echo 'selected="selected"'; ?>>2020</option>
				 			
             <select>
				  </div>
				  </div>
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
                
				
						
						
						 <table id="PO_ID" value="" class="table table-bordered table-hover">

								<thead class="process_dt_dt" id="process_dt_dt">
								
								
						

                                 <tr><td>Code List Not Issued</td><td>
								
								
								 <button class="btn btn-success" 
									id="CLNIstatus"
									a="CLNIstatus"
									onClick="status_update_click(this.value)"
									value="0" >Update Status
									</button>
								 </td>
								 
								</tr>
								<tr><td>Code List Issued But Not Submitted</td><td>
								 <button class="btn btn-success" 
									id="CLINSstatus"
									onClick="status_update_click(this.value)"
									value="1" >Update Status
									</button></td>
								</tr>
								
								<tr><td>Sample Submitted</td><td>
								 <button class="btn btn-success" 
									id="SSstatus"
									onClick="status_update_click(this.value)"
									value="2" >Update Status
									</button></td>
								</tr>
								
								<tr><td>Report Awaited</td><td>
								 <button class="btn btn-success" 
									id="RWstatus"
									onClick="status_update_click(this.value)"
									value="3" >Update Status
									</button></td>
								</tr> 
								
								<tr><td>Draft Report Received</td><td>
								<button class="btn btn-success" 
									id="DRRstatus"
									onClick="status_update_click(this.value)"
									value="4" >Update Status
									</button></td>
								</tr>
								
								<tr><td>Final Report Received</td>
								<td>
								<button class="btn btn-success" 
									id="FRRstatus"
									onClick="status_update_click(this.value)"
									value="5" >Update Status
									</button></td>
								</tr>
								
								<tr><td>Antibiotic Detected</td>
								<td>
								 <button class="btn btn-success" 
									id="ADstatus"
									onClick="status_update_click(this.value)"
									value="6" >Update Status
									</button></td>
								</tr>
								
								
								</thead>
								
								</table>

								
							
								
								
				
				
				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="delete_cnf" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body">
				<table id="PO_ID" value="" class="table table-bordered table-hover">
							<thead class="process_dt_dt" id="process_dt_dt">
								<tr><td>Delete Po </td><td>
									<button class="btn btn-danger" 
									id="DELETE_TR"
									a="DELETE_TR"
									onClick="delete_click(this.value)"
									value="0" >Delete
									</button>
								 </td>
								</thead>
								</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	
	
	<h3 align='center'> Avaiable  List </h3>
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  

	  
	 
       <th>Report ID</th>
       <th>Country</th>
	   <th>P.O Number</th>
	   <th>LOT Number</th>
        <th>Invoice Number & Date</th>
		<!--<th>EIA Submisn Date</th>
		<th>Status</th>-->
		<th>Actions</th>
		
		
		
		

		

      

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

$PO_TIME_STAMP = strtotime($row['INVOICE_DATE']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);

	

	?>

	
        <td id="<?php  echo $row['ID'];?>" > <?php echo $row['ID']; ?></td>
		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['DESTINATION_COUNTRY']; ?></td>
		<td id="po_num<?php  echo $row['ID'];?>" value="<?php echo $row['PO_NUM']; ?>"><?php echo $row['PO_NUM']; ?></td>        
		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['INTERNAL_LOT_NUM']; ?></td>
		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['INVOICE_NUMBER'].' & '
		. $INVOICE_DATE ; ?></td>  
		
		
		</td>
      	
		
		

		

		<td style="width:110px;"> 
		
		
		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li>
					<a target="_blank" href="DocInspEdit.php?id=<?php echo $row['ID'];?>" 
					class="btn btn-warning btn-sm  btn-flat glyphicon 
						glyphicon">  Edit  </a>
					</li>
		
		
		<li>
					<a target="_blank" class="btn btn-primary btn-sm edit btn-flat"
					href="DocProductInspection.php?id=<?php echo $row['ID'];?>" >Print </a>
					</li>
                    <li><a target="_blank" class="btn btn-success btn-sm edit btn-flat"
					href="UploadFolder.php?id=<?php echo $row['ID'];?>" >Upload Images </a></li>
                    <!--<li><a target="_blank" class="btn btn-danger btn-sm edit btn-flat"

					href="DocAnalyticList.php?id=<?php echo $row['ID'];?>" >Analytic Reports</a></li>
									href="Edit_Analytic_Report.php?id=<?php echo $row['ID'];?>" >Analytic Reports</a></li>-->

				   
                  </ul>
                </div>
	 	

        
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



<Script>

$('document').ready(function(){
	
	// $.stickysidebarscroll("#tests",{offset: {top: 10, bottom: 200}});
    
    	$(document).on('click','#payBtn',function(e){
			
			//alert("test");
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();			
						var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
						PO_ID.value = ""+refid;  
						var large2='TEST'                                     						
						$('.modheading').text('Update Status');
						$('#myModal').modal('toggle');								
			//return false;
    	});
		
		
		$(document).on('click','#delete_id',function(e){
			
			//alert("test");
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();	
			var po_num=$('#po_num'+refid).text();	

				//	alert(refid);			
					//alert(po_num);			
						var PO_ID = document.getElementById('DELETE_TR'); //or grab it by tagname etc
						PO_ID.value = ""+refid;  
						var large2='TEST'                                     						
						$('.modheading').text('Are You Sure to Delete - ' +po_num);
						$('#delete_cnf').modal('toggle');								
			//return false;
    	});
		
		
		
		
		
    
    	$('#elisa').on('click',function(){
    alert("test");
    		$('form').submit();
    	});
    });    

</script>

<script>
  $(function () {
   $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});
  })
</script>


<script>
  function status_update_click(status) {

	var po_id=$('#PO_ID').val();
	//alert(status);
	
	  
		$.ajax({
		  url:'po_status.php',
		  type:'POST',
		  data:{'STATUS':status,'INVOICE_ID':po_id},
		  success:function(data){
			  console.log(data);			  
		
			 alert("DONE");
location.reload();
			}
			});	
   			
  }
</script>


<script>
  function delete_click(status) {

	var po_id=$('#PO_ID').val();
	var po_id=status;
	//alert(status);
	//alert(po_id);
	//return;
	  
		$.ajax({
		  url:'po_delete.php',
		  type:'POST',
		  data:{'STATUS':status,'INVOICE_ID':po_id},
		  success:function(data){
			  console.log(data);			  
		
			 alert(data);
			location.reload();
			}
			});	
   			
  }
</script>

<script>
$(function(){
  $('#YEAR').change(function(){
    window.location.href = 'ProInspPoList.php?YEAR='+$(this).val();
  });
});
</script>

</html>
