<?php

session_start();
include "../includes/config.php";

 
$uid=$_SESSION['r_id'];

if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}

if(isset($_POST['send_sms'])){
	
	 		
			
//post

$API_KEY='VP32DKN3DW6E6NBJJEYCU1H45BHCM28Z';
$SECRET='2T6CRKJH5ON3PO4I';
$USER_TYPE='stage';
$PHONE='9642444934';
$SENDER_ID='JMEQCL';
$MSG=$T_MSG;

$url="https://www.way2sms.com/api/v1/sendCampaign";
$message = urlencode($MSG);// urlencode your message
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);// set post data to true
curl_setopt($curl, CURLOPT_POSTFIELDS, "apikey=".$API_KEY."&secret=".$SECRET."&usetype=".$USER_TYPE."&phone=".$PHONE."&senderid=".$SENDER_ID."&message=".$message);// post data
// query parameter values must be given without squarebrackets.
 // Optional Authentication:
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);
echo $result;
EXIT;
}


 ?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
 </head>
<body class="hold-transition skin-blue sidebar-mini fixed">
<?php 
	

$sql="select * from DAILY_LAB_DATA  ";

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
         <a href="DailyLabABCreate.php?DOA=<?php echo date("d-m-Y")?>&DOR=<?php echo date('d-m-Y', strtotime("+1 day"));?>" class="btn btn-primary">Create Report</a>
	<ol class="breadcrumb">
        <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Daily Lab Reports</li>
      </ol>
    </section>
	
	
			<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body">
             			
								
								
					
								
								
				 <div class="form-group col-sm-12" >
                  <label for="exampleInputEmail1">To Phone Numbers</label>
                  <input   type="text" value="9490785410" class="form-control"  name="TYPE_OF_PACKING" id="SHORT_NAME" placeholder="Enter Short Name">
                </div>
				
				<div class="form-group col-sm-12" >
                  <label for="exampleInputEmail1">Type</label>
                 
				 <select class="form-control" name="TYPE" id="DATA_TYPE">
					<option value="GREEN">ACCEPTED</option>
					<option value="RED">REJECTED</option>
					<option value="BLUE">HOLD</option>
					<option value="GRAY">TEST PENDING</option>
				 <select>
				 
				 </div>
				
				 <div class="form-group col-sm-12" >
                  <label for="exampleInputEmail1">Message</label>
                 
				<textarea id="FULL_NAME" name="Text1" cols="80" rows="10"></textarea>				  
			   </div>				
								
								
				
				
				
            </div>
            <div class="modal-footer">
			 <button type="button" class="btn btn-default" id="addSHORTNAme" data-dismiss="modal">SEND SMS</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



	
		<h3 align='center'> Daily  Lab Reports </h3>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  
	 
        <th>Report ID</th>

        <th>Analysis Date</th>

        <th>Report Date</th>

		<th>Total Reports</th>

		<th>Day Total</th>
		
		<th>Night Total</th>
		<th>Actions</th>

      

		

		

      

      </tr>

    </thead>

	

	

	 

			

			

			

    <tbody>

	

	<?php

	//echo $sql;

	

   while($row=$stmt->fetch_array()){

	

	?>

	


     

 	

        <td id="<?php  echo $row['ID'];?>" > <?php echo $row['ID']; ?></td>
		
		<?php $ANALYSIS_DATE = $row['ANALYSIS_DATE']?>
		<?php $REPORT_DATE = $row['REPORT_DATE']?>

        <td id="c_name<?php  echo $row['C_ID'];?>"><?php echo  date("d-m-Y", strtotime($ANALYSIS_DATE)) ?></td>

		<td id="a_type<?php  echo $row['C_ID'];?>"><?php echo date("d-m-Y", strtotime($REPORT_DATE)) ?></td>

		<td id="p_number<?php  echo $row['C_ID'];?>"><?php echo $row['TOTAL_REPORTS']; ?></td>

		<td id="address<?php  echo $row['C_ID'];?>"><?php echo $row['DAY_TOTAL']; ?></td>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo $row['NIGHT_TOTAL']; ?></td>

      <td style="width:280px;"> 
	  
	  <button id='sms' type="submit" value="<?php echo $REPORT_DATE ?>"  name='send_sms' class="btn btn-primary">sms</button>
	  
	<!--  <a  href="DailyLabReportCreate.php?DOA=<?php echo date("d-m-Y", strtotime($ANALYSIS_DATE))?>&DOR=<?php echo date("d-m-Y", strtotime($REPORT_DATE));?>"  class="btn btn-primary">Add</a>-->
	  <a  href="DailyLabABCreate.php?DOA=<?php echo date("d-m-Y", strtotime($ANALYSIS_DATE))?>&DOR=<?php echo date("d-m-Y", strtotime($REPORT_DATE));?>"  class="btn btn-primary">Add</a>

						
<?php if($row['DAY_TOTAL']!=0) {?>
	<a href="DailyLabReportsPrint.php?id=<?php  echo $row['ID'];?>&shift=DAY" target="_blank" class="btn btn-primary glyphicon glyphicon-print">  Day</a>
<?php } ?>

<?php  if($row['NIGHT_TOTAL']!=0) {?>
	 <a href="DailyLabReportsPrint.php?id=<?php  echo $row['ID'];?>&shift=NIGHT" target="_blank" class="btn btn-primary glyphicon glyphicon-print">  Night</a>
<?php } ?>
						
						 
						 
					
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



<Script>

$('document').ready(function(){
	
$(document).on('change','#DATA_TYPE',function(e){
	
	e.preventDefault();
		
			
			
		var DATE=$('#sms').val();
		var DATA_TYPE=$('#DATA_TYPE').val();
		//alert(DATE);
		  $.ajax({ 
		  url:'ajax/sendsms.php',
		  type:'POST',
		  data:{'TYPE':'SHOW_DATA','DATE':DATE,'DATA_TYPE':DATA_TYPE},
		  success:function(data){
			  console.log(data);
			 
			 $('#FULL_NAME').text('');
			 var string =data.toString();
			
			var lines = string.split('<br>');
			   
			   $('.modheading').text('Send SMS');
			   
			   var number = lines.length;
    container = document.getElementById('FULL_NAME');
	
	//container.innerHTML = lines;

	


for (i = 0; i < number; i++) {
    container.appendChild(document.createTextNode( lines[i] +"\n" ));
    var input = document.createElement("input");
    input.type = "number";
    container.appendChild(input);
}

			   					
		  }
						
					}); 	

	
	
	
});
    
    	$(document).on('click','#sms,#D_F_NAME',function(e){
    	e.preventDefault();
		 $('#FULL_NAME').text('');
			
			
		var DATE=$(this).val();
		var DATA_TYPE=$('#DATA_TYPE').val();
		//alert(DATE);
		  $.ajax({ 
		  url:'ajax/sendsms.php',
		  type:'POST',
		  data:{'TYPE':'SHOW_DATA','DATE':DATE,'DATA_TYPE':DATA_TYPE},
		  success:function(data){
			  console.log(data);
			  encodeURIComponent("\n")
			 //  var obj = JSON.parse(data);
			 var string =data.toString();
			
			var lines = string.split('<br>');
			   
			   $('.modheading').text('Send SMS');
			  
			    
			  var number = lines.length;
    container = document.getElementById('FULL_NAME');
	
	//container.innerHTML = lines;

	
//container.style.whiteSpace = 'pre';

for (i = 0; i < number; i++) {
    container.appendChild(document.createTextNode( lines[i] +"\n" ));
    var input = document.createElement("input");
    input.type = "number";
    container.appendChild(input);
}

			   
			 //  $('#FULL_NAME').text(lines);

			$('#myModal').modal('toggle');						
		  }
						
					}); 	

    	});
		
		
		
		$(document).on('click','#addSHORTNAme',function(e){
    	e.preventDefault();
		
			
			
		var NUMBER= $('#SHORT_NAME').val();
		var MSG= $('#FULL_NAME').val();
	
		  $.ajax({ 
		  url:'ajax/sendsms.php',
		  type:'POST',
		  data:{'TYPE':'MSG','NUMBER':NUMBER,'MSG':MSG},
		  success:function(data){
			  console.log(data);
			   var obj = JSON.parse(data);
			 
				alert(obj.status);
			
			   
			   $('.modheading').text('Send SMS');
			   $('#FULL_NAME').val('');

			//$('#myModal').modal('toggle');						
		  }
						
					}); 	

    	});
		
		
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
    });    

</script>

</html>
