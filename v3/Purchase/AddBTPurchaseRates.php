<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$PageTitle="Purchase Rates";
$PageTitleFolder="Accounts";

	if(isset($_GET['id'])){
	 $IsEdit=1; 
	  $DateId=urlDecode64($_GET['id']);
	}else{
		$IsEdit=0;
	}	
	//if($_SESSION["DeptID"]!=8 || $_SESSION["AccessType"]!=1){
	if($_SESSION["DeptID"]!=8 || $_SESSION["AccessType"]!=2){
//header('Location: index.php?Msg=You Dont Have Access To Change Purchase Rates ');
}

 
 function getPurchaseRates($iconn,$LOT_ID){
 
 global $RmPurchaseRates;	

	$size=40;		
	$size1=60;	
	$textw='30%';
	$text=2;
	
$IsEdit=false;
	if(isset($_GET['id'])){
	 $IsEdit=true;  
 $DateId=urlDecode64($_GET['id']);
 $sql="SELECT * FROM RmPurchaseRates WHERE DateID='$DateId' order by abs(RmCount) asc";

}else{
$sql="SELECT * FROM RmRatesRange where Stype ='BT' order by abs(RmCount) asc";

}
//echo $sql; exit;

		$stmt=$iconn->query($sql);
			$content = '<table >
				
				<tr>
				
				
				<th  align="CENTER" width="4%"><font size="'.$text.'"> <B>Count</B></font></th>
				<th  align="CENTER" width="4%"><font size="'.$text.'"> <B>Rate (INR)</B></font></th></tr>';
		
			$count=0;
			while($PostDetails = $stmt->fetch_array()){
				 $CostRateID= $count;
				$content.= '<th  id="Grade'.$CostRateID.'"  align="CENTER" width="4%"><font size="'.$text.'"><B>'.$PostDetails['RmCount'].'</B></font></th>';
				if($IsEdit){
				$content.='<th><input required type="text" step="0.5" class="form-control"  name="DayCode" id="DayCode'.$CostRateID.'
				
				"placeholder="Enter " value="'.$PostDetails['RmPrice'].'"></th></tr>';
				}else{
					$content.='<th><input required type="text" step="0.5" class="form-control"  name="DayCode" id="DayCode'.$CostRateID.'
				
				"placeholder="Enter " value=""></th></tr>';
				
				}
				
				
				$count++;
			}
			
		if(!$IsEdit){
			$content.= '<tr>
				<th  align="CENTER" width="4%"><font size="'.$text.'">Select Date</th>
				<th  align="CENTER" width="4%"><font size="'.$text.'">
				 <input type="date" value=""  class="form-control" name="SelectDate" id="SelectDate"></input>
				 </th>
				</tr>';
		}
				
				$content.= '</table>';
			
			$DATA[]=$count;
			$DATA[]=$content;
			$DATA[]=$IsEdit;
		//return json_encode($content);
		return $DATA;
	}




 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 <?php  include ($IncludesPath."DbLinks.php");?>
         <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
				

                        <?php include ($IncludesPath."page-title.php");?>

    <!-- Main content -->


      <!-- Small boxes (Stat box) -->
	  
<div id="tdetails" class="table-responsive" >  
        <div align="center" class="card" >
          
        <div class="card-body">
		
  <table id="PurchaseRatesTable" class="table table-bordered table-hover" style="width:50%">
 </table>
		 
 <tr>      

		  <button  id='SubmitBtn' value="" onClick="reply_click()" class="btn btn-success"> Submit</button>
	 	
</tr>
</div>
</div>


   </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>

</body>
<script>
$('document').ready(function(){
  var data ='<?php echo  json_encode(getPurchaseRates($iconn,$LOT_ID)) ;?>';
  
  var data = data.replace("[", "");
   var data = data.replace("]", "");
   var data = data.replace(/["']/g, "");
    var strArray = data.split(",");
    var IsEdit = strArray[2];
	
  
  $("#PurchaseRatesTable").html(strArray[1]);
  $("#SubmitBtn").val(strArray[0]);
  
});

</script>


<script>
  function reply_click() {

  var InvID=$("#SubmitBtn").val();	
  var SelectDate=$("#SelectDate").val();
  var IsEdit='<?php echo $IsEdit ?>';
  var DateId='<?php echo $DateId ?>';
//  alert(InvID);
//alert(IsEdit);
if(!IsEdit && SelectDate==''){
	alert("Please Date");
	 document.getElementById("SelectDate").focus();
	 return;
}  


		//  
			var Cost = [];
			var grades = [];
		 for (i = 0; i < InvID; i++) {
			 
			
			   var DayCodeID='#DayCode'+i;
			   var GradeID='#Grade'+i;
			    var Grade=$(GradeID+"").text();
			    var DayCode=$(DayCodeID+"").val();
				Cost.push(DayCode);
				grades.push(Grade);
				
				
				
				if(!Number(DayCode)) {
				 alert('Only Number Allowed for Count ' +Grade );
				 
				  document.getElementById("DayCode"+i).focus();
					
					return;
					
				}
				
				 if(DayCode=='' || DayCode==0){
				 alert('Enter Price for  ' +Grade );
				 
				  document.getElementById("DayCode"+i).focus();
				 
				 return;
				
			 } 
			 
			 
			}
			
			 $('#myModal').modal('show');
			 

	 
		$.ajax({
		  url:'Ajax/AjaxUpdatePricing.php',
		  type:'POST',
		  data:{'IsEdit' : IsEdit,'DateId' : 
		  DateId, 'Grades':grades,'Rates':Cost,'SType':'BT','SelectDate' : SelectDate},
		  success:function(data){
			  if(data=='Success'){
				  
				   window.location = "index.php?Msg=Rm Rates Updated Successfully";
			  }else{
				  
				  alert(data);
			  }
			 
			 
			}
			});	


$(document).ajaxStart(function(){
  $('#myModal').modal('show');
  });
  $(document).ajaxComplete(function(){
    $('#myModal').modal('hide');
  });			
  }
</script>
