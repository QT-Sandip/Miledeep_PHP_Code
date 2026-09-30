<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$IDD=$_GET['LOT_ID'];
//$uid=$_SESSION['r_id'];
$uid= $_SESSION["UserID"];



extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	

	
	$doa = date("d-m-Y", strtotime($doa));
	$doi = date("d-m-Y", strtotime($doi));
	
	 $sql_org_insert="UPDATE `ORGN_INSP_REPORT`  SET 
	 `AUTH_REMARKS`='$Remarks',
	 `VERIFIED_BY`='$uid',
	 `DEDUCTIONS`='$DEDUCTIONS'
	 WHERE LOT_ID='$IDD'";

	$stmt1=$iconn->query($sql_org_insert);
	
	if($stmt1){
		//echo "hii";
		
		header('Location:RmReportCreate.php?LOT_ID='.$IDD);
	}
	
	}
 
/*if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}*/

$id=$_GET['LOT_ID'];

	
	$SULPHITE=$row_org_insert['SULPHITE'];


$SQL_CODE_LIST="SELECT * FROM `DAILY_LAB_REPORT_LIST` where  `LOT_NUMBER`='$LOT_NUMBER'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
$ROW_CODE_LIST=$STMT_CODE_LIST->fetch_array();
	
	$sql_list="SELECT * FROM `inputmaterial_details` WHERE id=$id";
    $stmt_ID=$iconn->query($sql_list);  
	$row_list=$stmt_ID->fetch_array();
	
	$LOT_NO=$row_list['LOT_NO'];
	$PURCHASE_COUNT=$row_list['COUNT'];
	$AGENT_NAME=$row_list['AGENT_NAME'];
	
	 $sql_list="SELECT * FROM `DAILY_LAB_REPORT_LIST` WHERE LOT_NUMBER='$LOT_NO'";
    $stmt_ID=$iconn->query($sql_list);  
	$row_d_lab=$stmt_ID->fetch_array();
	
	  $RESULT=$row_d_lab['PARAMETER'];
	  
	  $SULPHITE=$row_d_lab['SULPHITE'];

	$sql_INSP="SELECT * FROM `ORGN_INSP_REPORT` WHERE LOT_ID=$id";
    $stmt_INSP=$iconn->query($sql_INSP);  
	
	while($row_INSP=$stmt_INSP->fetch_array()){
	$arry_INSP[]=$row_INSP;
	}
	
	
	$ary=$arry_INSP;
	
	$CHECK_COUNT=$row_INSP['CHECK_COUNT'];







$CHECKED_COUNT=$ary[0]['CHECK_COUNT']+$ary[1]['CHECK_COUNT']+$ary[2]['CHECK_COUNT'];
$CHECKED_COUNT=round($CHECKED_COUNT/3);


  $Avg_weight= '1000'/$CHECKED_COUNT;


 $RED_BLACK_GILLS=$ary[0]['RED_BLACK_GILLS']* $Avg_weight +$ary[1]['RED_BLACK_GILLS']* $Avg_weight+$ary[2]['RED_BLACK_GILLS']* $Avg_weight;

 $RED_BLACK_GILLS=ROUND($RED_BLACK_GILLS/30,1);
	
	$SEMI_SOFT_PAPER_SOFT=$ary[0]['SEMI_SOFT_PAPER_SOFT']* $Avg_weight+$ary[1]['SEMI_SOFT_PAPER_SOFT']* $Avg_weight+
	$ary[2]['SEMI_SOFT_PAPER_SOFT']* $Avg_weight;
$SEMI_SOFT_PAPER_SOFT=round($SEMI_SOFT_PAPER_SOFT/30,1);
	
	$DROOPING_HEAD=$ary[0]['DROOPING_HEAD']* $Avg_weight +$ary[1]['DROOPING_HEAD'] * $Avg_weight+ $ary[2]['DROOPING_HEAD']*
	$Avg_weight;
$DROOPING_HEAD=round($DROOPING_HEAD/30,1);
	
	$BLACK_SPOT=$ary[0]['BLACK_SPOT']* $Avg_weight+$ary[1]['BLACK_SPOT'] * $Avg_weight +$ary[2]['BLACK_SPOT']* $Avg_weight;
$BLACK_SPOT=round($BLACK_SPOT/30,1);
	
	$BROKEN=$ary[0]['BROKEN']* $Avg_weight +$ary[1]['BROKEN']* $Avg_weight +$ary[2]['BROKEN']* $Avg_weight;
$BROKEN=round($BROKEN/30,1);
	
	$IMPROPER_LEGS=$ary[0]['IMPROPER_LEGS']* $Avg_weight+$ary[1]['IMPROPER_LEGS']* $Avg_weight+$ary[2]['IMPROPER_LEGS']* $Avg_weight;
$IMPROPER_LEGS=round($IMPROPER_LEGS/30,1);


	$BENDS=$ary[0]['BENDS']* $Avg_weight+$ary[1]['BENDS']* $Avg_weight+$ary[2]['BENDS']* $Avg_weight;
$BENDS=round($BENDS/30,1);

//$FIT_FOR_HEAD_ON=$stmt_INSP ['FIT_FOR_HEAD_ON'];
	

 ?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
<?php 

 $sql="select * from inputmaterial_details WHERE  SAMPLE_TYPE!='PRE HARVEST' and RM_ANA_STATUS='$ID'";
	


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
    
	 <h2 align="center"> <b> <i> R.M Analysis Preview ( <span style="color:Tomato; bold"><?php echo $row_list['LOT_NO'] ?></span> ) </i></b></h2>
	<ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">R.M Analysis Preview </li>
      </ol>
    </section>
	

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
		
		
 <!--  <h2 align="center">LOT NUMBER - </h3>  -->

          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>
	         
                <th  align="center" > Description </font></th>
                <th  align="center" > Standard </font></th>
                <th  align="center" > 1 </font></th>
                <th  align="center" > 2  </font></th>
                <th  align="center" > 3 </font></th>
                <th  align="center"> Average% </font></th>
  
    </thead>


    <tbody>

 <tr> 
           		
               <th  align="center" > Farmer Agent</font></th>
                <th  align="center" >-</font></th>
                <th  align="center" > <?php echo $AGENT_NAME ?><th  align="center" >

               
                
               
                
				</tr>
				
				 <tr> 
           		
               <th  align="center" >  Purchase count/Kg </font></th>
               <th  align="center" >  - </font></th>
                <th  align="center" > <?php echo $PURCHASE_COUNT ?> </font></th>
               <th  align="center" > <?php echo $PURCHASE_COUNT ?> </font></th>
                <th  align="center" > <?php echo $PURCHASE_COUNT ?>  </font></th>
                <th  align="center" > <?php echo $PURCHASE_COUNT ?>  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Checked count/Kg </font> </th>
                <th  align="center" > -  </font></th>
                 <th  align="center" ><?php echo $ary[0]['CHECK_COUNT']?> </font></th>
                 <th  align="center" ><?php echo $ary[1]['CHECK_COUNT']?> </font></th>
                 <th  align="center" ><?php echo $ary[2]['CHECK_COUNT']?> </font></th>
               <th  align="center" ><?php echo $CHECKED_COUNT?> </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" >  Red/Black gill in% </font></th>
                <th  align="center" > 5% </font></th>
                 <th  align="center" ><?php echo   ROUND($ary[0]['RED_BLACK_GILLS']*$Avg_weight/10,1) ?> </font></th>
                <th  align="center" ><?php echo  ROUND($ary[1]['RED_BLACK_GILLS']*$Avg_weight/10,1) ?>   </font></th>
                <th  align="center" ><?php echo   ROUND($ary[2]['RED_BLACK_GILLS']*$Avg_weight/10,1) ?> </font></th>
                 <th  align="center" ><?php echo  $RED_BLACK_GILLS ?>  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Soft/Semi Soft/Paper Soft </font></th>
                <th  align="center" >  10% </font></th>
                <th  align="center" ><?php echo  ROUND($ary[0]['SEMI_SOFT_PAPER_SOFT']*$Avg_weight/10,1) ?> </font></th>
                <th  align="center" ><?php echo  ROUND($ary[1]['SEMI_SOFT_PAPER_SOFT']*$Avg_weight/10,1) ?>  </font></th>
                 <th  align="center" ><?php echo   ROUND($ary[2]['SEMI_SOFT_PAPER_SOFT']*$Avg_weight/10,1) ?>  </font></th>
                <th  align="center" ><?php echo   $SEMI_SOFT_PAPER_SOFT ?> </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Dropping Head in %</font></th>
                <th  align="center" >  5% </font></th>
                <th  align="center" ><?php  echo ROUND($ary[0]['DROOPING_HEAD']*$Avg_weight/10,1)?>  </font></th>
                <th  align="center" ><?php echo  ROUND($ary[1]['DROOPING_HEAD']*$Avg_weight/10,1)?>  </font></th>
                 <th  align="center" ><?php echo   ROUND($ary[2]['DROOPING_HEAD']*$Avg_weight/10,1)?>  </font></th>
                <th  align="center" ><?php echo  $DROOPING_HEAD ?>  </font></th>
                
               
                
				</tr>
				
				
				 <tr> 
           		
                <th  align="center" >  Black spot/Black Dot in %</font></th>
                <th  align="center" >  Nil </font></th>
                 <th  align="center" ><?php echo  ROUND($ary[0]['BLACK_SPOT']*$Avg_weight/10,1)?> </font></th>
                 <th  align="center" ><?php echo  ROUND($ary[1]['BLACK_SPOT']*$Avg_weight/10,1)?> </font></th>
                 <th  align="center" ><?php echo  ROUND($ary[2]['BLACK_SPOT']*$Avg_weight/10,1)?> </font></th>
                 <th  align="center" ><?php echo  $BLACK_SPOT ?> </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" >  Broken/Damages in %</font></th>
                <th  align="center" >  Nil </font></th>
                <th  align="center" > <?php echo ROUND($ary[0]['BROKEN']*$Avg_weight/10,1)?>  </font></th>
                <th  align="center" >  <?php echo  ROUND($ary[1]['BROKEN']*$Avg_weight/10,1)?>   </font></th>
                <th  align="center" > <?php echo ROUND($ary[2]['BROKEN']*$Avg_weight/10,1)?>  </font></th>
                <th  align="center" >  <?php echo  $BROKEN ?> </font></th>
          
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Improper Legs/Decomposition/Discoloration in %</font></th>
                <th  align="center" > Nil </font></th>
                 <th  align="center" ><?php echo   ROUND($ary[0]['IMPROPER_LEGS']*$Avg_weight/10,1)?>   </font></th>
                <th  align="center" > <?php echo  ROUND($ary[1]['IMPROPER_LEGS']*$Avg_weight/10,1)?>  </font></th>
                 <th  align="center" ><?php echo  ROUND($ary[2]['IMPROPER_LEGS']*$Avg_weight/10,1)?> </font></th>
                 <th  align="center" > <?php echo   $IMPROPER_LEGS ?>  </font></th>
          
				</tr>
				
				 <tr> 
           		
                 <th  align="center" >  Bends in %</font></th>
                 <th  align="center" >  10% </font></th>
                <th  align="center" ><?php echo ROUND($ary[0]['BENDS']*$Avg_weight/10,1)?></th>
                <th  align="center" ><?php echo ROUND($ary[1]['BENDS']*$Avg_weight/10,1)?></th>
                 <th  align="center" ><?php echo ROUND($ary[2]['BENDS']*$Avg_weight/10,1)?></font></th>
                <th  align="center" ><?php  echo $BENDS ?> </font></th>
          
				</tr>
				
				 <tr> 
           		
                 <th  align="center" > Material is fit for Head On Packing or not</font></th>
                 <th  align="center" > -- </font></th>
                
                 <th  align="center" > <?php echo $FIT_FOR_HEAD_ON ?>  </font></th>
              
          
				</tr>

 </tbody>

  </table>
  
  <form role="form" method="post">
		  
		  
		  	

		  
              
			    <div class="box-body">
				
				  <div class="form-group col-sm-12" >
				  <h1> Remarks</h1>
				  </div>
                <div class="form-group col-sm-9" >
               
                  <input required type="text" class="form-control" name='Remarks' id="Remarks" value= "<?php echo $ary[0]['AUTH_REMARKS'] ?>"
				  placeholder="Enter Remarks">
                </div>
				
				<div class="box-body">
				
				  <div class="form-group col-sm-12" >
				  <h1> Deductions</h1>
				  </div>
                <div class="form-group col-sm-9" >
               
                  <input required type="number" class="form-control" 
				  name='DEDUCTIONS' id="DEDUCTIONS" value= ""
				  placeholder="Enter DEDUCTIONS(if any)">
                </div>
				
			  <div class="col-sm-3 box-body"  align="right">
                <button type="submit"  name='submit' style=" font-size : 30px; height:60px;width:180px" 
				class="btn btn-success btn-sm btn-flat">Submit</button>
              </div>
            </form>
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

</html>
