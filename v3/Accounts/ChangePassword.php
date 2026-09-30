<?php
session_start();
include "../includes/config.php";
//include "../includes/Globals.php";
$USER_ID=$_SESSION['r_id'];
 $sql="select * from login where USER_ID='$USER_ID'";

$stmt=$iconn->query($sql);
$RowFetchArray=$stmt->fetch_array();




extract($_POST);
extract($_GET);

//print_r($_POST);

if(isset($_POST['CNF_PASSWORD'])){
	
	$oldLogin = legacyAuditFetchRowById('login', 'USER_ID', $USER_ID, 'Accounts');

	 $sql_org_insert="UPDATE `login`
	 SET `USER_PASSWORD` = '$CNF_PASSWORD' WHERE `USER_ID` = '$USER_ID'";	
	  $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	
	if($stmt1){
	legacyAuditLogUpdateById('login', 'USER_ID', $USER_ID, $oldLogin, array('USER_PASSWORD'=>'[redacted]'), array(), 'Accounts');
	header('Location: AccDash.php?Msg=Password Updated SuccessFully');
	}else{
		header('Location: AccDash.php?Msg=Unable To Updated Password');
	}

}else{
	
	
}



 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">

   

<div class="wrapper">

<?php include "../includes/header.php"; ?>

  <!-- Left side column. contains the logo and sidebar -->

 <?php include "../includes/sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

        

<h2 align="center">Change Password</h2>


</a>
</a>

			

			



<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Change Password</li>

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
                
				
						
						
						 <table id="example1" class="table table-bordered table-hover">

              

							<thead class="process_dt_dt" id="process_dt_dt">

                                
								
								</thead>
								
								</table>

								
						
						
						

				
            </div>
            <div class="modal-footer">
                <button type="button" 
				class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	

	



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	 
    <div id="tdetails" class="table-responsive" >  

        <div class="box">
		
		
          <form  id="CpForm" role="form" method="post">
		  
		  
              
			    <div class="box-body">
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">User Name</label>
                  <input readonly required 
				  value="<?php echo $RowFetchArray['USER_NAME'] ?>"
				  type="text" class="form-control" name='USER_NAME' id="USER_NAME"
				  placeholder="Enter User Name">
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Password</label>
                  <input type="password" class="form-control" 
				  name="USER_PASSWORD" id="USER_PASSWORD" 
				 placeholder="Enter Password">
                </div>
				  
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Password</label>
                  <input type="password" class="form-control" 
				  name="CNF_PASSWORD" id="CNF_PASSWORD" 
				 placeholder="Re-enter same  Password">
                </div>
				
				
				 
				<!--<input type="submit" value="submit" /> -->
				
				
				<input  hidden type="submit" value="submit" />
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button   id='Chpwd' class="btn btn-primary">Submit</button>
              </div> 
            </form>



</div>


</div>



    </section>

    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>
   <?php include "../includes/scripts.php"; ?>

  </div>



</body>

<Script>

$('document').ready(function(){
    
    	$(document).on('click','#Chpwd',function(e){
    	e.preventDefault();
		//alert('hi');
		var form = document.getElementById("CpForm");
		var CNF_PASSWORD=$("#CNF_PASSWORD").val();	
		var USER_PASSWORD=$("#USER_PASSWORD").val();
		if(USER_PASSWORD.trim() ==''){
			 alert('Please Enter Password'); 
			document.getElementById("USER_PASSWORD").focus();
		}else if(USER_PASSWORD.length < 8){
			alert('Minimum Password Length 8 Letter');
			document.getElementById("USER_PASSWORD").focus();
		}else if(CNF_PASSWORD==''){
			document.getElementById("CNF_PASSWORD").focus();
		}else if(CNF_PASSWORD != USER_PASSWORD){
		  alert('Password Mismatch'); 
		  document.getElementById("CNF_PASSWORD").focus();
		}else{
			form.submit();
		}
		
		
		
    
    	});
		
		
    
    	$('#continuebtn').on('click',function(){
			alert('hi');
    
    		
    	});
    });    

</script>





</html>

