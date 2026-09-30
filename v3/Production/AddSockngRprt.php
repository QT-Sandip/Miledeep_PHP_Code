<?php


session_start();

include "../includes/config.php";

//$INVOICE_ID=$_GET['id'];
$LOT_ID=$_GET['ID'];
$SoakRefId=$_GET['SckID'];
$sql_LOTS="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt_LOTS=$iconn->query($sql_LOTS);
$ROW_LOTS=$stmt_LOTS->fetch_array();

  $today_date=date('Y-m-d');

extract($_POST);
extract($_GET);


$SqlSoakingData="SELECT * FROM `soaking_report` where LOT_ID='$LOT_ID' AND  id='$SoakRefId'";
$StmtSoakingData=$iconn->query($SqlSoakingData); 
$RowSoakData=$StmtSoakingData->FETCH_ARRAY();

//exit;
if(isset($_POST['submit'])){
	
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
	 

	
$sql_org_insert="insert into soaking_report
(`user_id`,`LOT_ID`,`TIME1`, `T_NO`, `L_NO`, `Variety`,`QTY`, `C1`, 
`Salt_kg`, `CHEM`, `SUP1_SIGN`, `TIME2`, `C2`, `APP_QT`, `SUP2_SIGN`,
 `DEST`, `Remarks`, `TimePeriod`, `Source`, `CreatedDateTime`,`SoakingDate`) 
values('$USER_ID', '$LOT_ID','$time1', '$tno', '$lno', '$variety', '$qty', '$c1',
'$salt_kg', '$chem', '$sup1_sign', '$time2', '$c2', '$APP_QT', '$sup2_sign', 
'$dest', '$remarks', '$timeperiod', '$source', '$TIME_STAMP','$SoakingDate')";

	$stmt1=$iconn->query($sql_org_insert);
	$SoakRefId = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		
		$DOA_DOC=$DOA.'/'.$DOC;
		
		$sql_org_insert="SELECT * FROM `soaking_report` where LOT_ID='$LOT_ID'";	
	 
	$stmt2=$iconn->query($sql_org_insert);
		
		
	}
	
	if($stmt2){
		header('Location: AddSockngRprt.php?ID='.$LOT_ID.'&SckID='.$SoakRefId );
	}
}

			
$SQL_PROCESSING_STATIONS="SELECT * FROM `RM_PROCESSING_STATIONS`";
$stmt_PROCESSING_STATIONS=$iconn->query($SQL_PROCESSING_STATIONS);	


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

<h2 align="center">Soaking Report<span style="color:Tomato; bold">
<?php echo $ROW_LOTS['LOT_NO'] ?>(<?php echo $ROW_LOTS['COUNT'] ?>C<?php// echo $ROW_LOTS['WEIGHT_IN_KGS'] ?>)</span></h3>

	</a>
		</a>

<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Soaking Report</li>

      </ol>

    </section>


    <!-- Main content -->

    <section class="content">
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">		
          <form role="form" method="post">		             
			    <div class="box-body">
				
			<!--	<div class="form-group col-sm-2" align="center" >
				<label for="exampleInputEmail1">COUNT:<b><span style="color:Tomato; bold"><?php echo $ROW_LOTS['COUNT']?></span></b></label>
				</div> 
				
				--> 
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Soaking Date</label>
                  <input required type="date" class="form-control" name='SoakingDate' id="SoakingDate"
				  max="<?php echo $today_date; ?>"value="<?php echo $RowSoakData['SoakingDate']; ?>" placeholder="Enter Start time">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Time</label>
                  <input required type="time" class="form-control" name='time1' id="time1" 
				  value="<?php echo $RowSoakData['TIME1']; ?>" placeholder="Enter Start time">
                </div>
				
				
				
			

		 	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Source Station</label>
						
		 <select class="form-control" name="source" id="source">		   
 <?php while($row_PROCESSING_STATIONS=$stmt_PROCESSING_STATIONS->fetch_array()) { 
		
                 $STATION_ID=$row_PROCESSING_STATIONS['ID'];			
				 ?>  

				<option Value="<?php echo $row_PROCESSING_STATIONS['ID'];?>"
				<?php if( $row_PROCESSING_STATIONS['ID']==$RowSoakData['Source'])
					  echo 'selected="selected"'; ?>>
				<?php echo $row_PROCESSING_STATIONS['UNIT_NAME'];?></OPTION>				
				<?php } ?>
				<select> 
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Time Period</label>
                  <select class="form-control" name="timeperiod" id="timeperiod">
                    <option Value="30">30</OPTION>
                    <option Value="60">60</OPTION>
                    <option Value="90">90</OPTION>
                    <option Value="120">120</OPTION>
                    <option Value="150">150</OPTION>
                    <option Value="180">180</OPTION>
					<select> 
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Variety</label>
                   <select class="form-control" name="variety" id="variety">
                    <option Value="VMHLSO">VMHLSO</OPTION>
                    <option Value="VMHLSO G2">VMHLSO G2</OPTION>
                    <option Value="VMEZPL">VMEZPL</OPTION>
                    <option Value="VMPD">VMPD</OPTION>
                    <option Value="PD-BUTTERFLY">PD-BUTTERFLY</OPTION>
                    <option Value="VMPDTO">VMPDTO</OPTION>
                    <option Value="PDTO 50%-PARCUT">PDTO 50%-PARCUT</OPTION>
                    <option Value="PDTO 70%-BUTTERFLY">PDTO 70%-BUTTERFLY</OPTION>
				    <option Value="VMPUD">VMPUD</OPTION>
				    <option Value="PULL VEIN">PULL VEIN</OPTION>
					 <option Value="PD FULL CUT">PD FULL CUT</OPTION>
				    <option Value="OTHER">OTHER</OPTION>
				   <select> 
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Select Shift</label>
                  <select class="form-control" name="SHIFT" id="SHIFT">
                    <option Value="A-SHIFT">A-SHIFT</OPTION>
                    <option Value="B-SHIFT">B-SHIFT</OPTION>
                    <option Value="C-SHIFT">C-SHIFT</OPTION>
                    <option Value="GENERAL SHIFT">GENERAL SHIFT</OPTION>
                    <option Value="NIGHT SHIFT">NIGHT SHIFT</OPTION>
					<select> 
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Salt/Kg </label>
                  <input required type="text" class="form-control" name='salt_kg' id="salt_kg" 
				  value="<?php echo $RowSoakData['Salt_kg']; ?>" placeholder="Enter SALT/Kg">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Chemical </label>
                  <input required type="text" class="form-control" name='chem' id="chem" 
				  value="<?php echo $RowSoakData['CHEM']; ?>" placeholder="Enter CHEMICAL used">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Incharge Name</label>
                  <input required type="text" class="form-control" name='sup1_sign' id="sup1_sign" 
				  value="<?php echo $RowSoakData['SUP1_SIGN']; ?>" placeholder="Enter Incharge Name">
                </div> 
				

				
				
				<div class="form-group col-sm-12" >
                <H3 style="color:GREEN"> Soaking Details</H3>
				 </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tub Number</label>
                  <input required type="text" class="form-control" name='tno' id="tno" 
				  value="" placeholder="Enter tub number">
                </div> 	
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">In Count</label>
                  <input required type="text" class="form-control" name='c1' id="c1" 
				  value="<?php echo $RowSoakData['C1']; ?>" placeholder="Enter INCOUNT">
                </div> 
							
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">In Quantity</label>
                  <input required type="text" class="form-control" name='qty' id="qty" 
				  value="<?php echo $RowSoakData['QTY']; ?>" placeholder="Enter IN QUANTITY ">
                </div> 
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Out Count</label>
                  <input required type="text" class="form-control" name='c2' id="c2" 
				  value="<?php echo $RowSoakData['C2']; ?>" placeholder="Enter OUTCOUNT">
                </div> 
							
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Out Quantity</label>
                  <input required type="text" class="form-control" name='APP_QT' id="APP_QT" 
				  value="<?php echo $RowSoakData['APP_QT']; ?>" placeholder="Enter OUT QUANTITY ">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input  type="text" class="form-control" name='remarks' id="remarks" 
				  value="<?php echo $RowSoakData['Remarks']; ?>" placeholder="Enter REMARKS(if any)">
                </div> 

				
			<div class="col-xs-12 box-footer"  align="right">
               <button type="submit"  name='submit' class="btn btn-success">Add</button>
               
			   <a href="LotsList.php"  id="<?php echo $INVOICE_ID; ?>"  class="btn btn-primary ">SUBMIT  </a>
			 
			 </div>

			
            </form>

</div>


	
<div id="tdetails" class="table-responsive col-sm-12" >  
    <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover" ALIGN="CENTER">
    <thead >
      <tr >
	  
	  <th align='center' >S.No</th>
	  <th align='center'>Tub Number</th>
	  <th align='center'>Soaking Date</th>
	  <th align='center'>Variety</th>
	  <th align='center'>Time Period </th>
	  <th align='center'>Start Time</th>
	  <th align='center'>In Count </th>
	  <th align='center'>In Quantity</th>
	  <th align='center'>Out Count</th>
	  <th align='center'>Out Quantity</th>
	  <th align='center'>Station</th>
	 <th align='center'>Remarks</th>
	 <th align='center'>Remove</th>

      </tr>
    </thead>
    <tbody>

	<?php
	
	

 $SQL_SOACKING="SELECT * FROM `soaking_report` where LOT_ID='$LOT_ID' ORDER BY SoakingDate DESC";
$STMT_SOACKING=$iconn->query($SQL_SOACKING);

$S_NO=1;
	//echo $sql;
   while($row_SOACKING=$STMT_SOACKING->fetch_array()){

$SHED_ID=$row_SOACKING['Source'];
   
   $SQL_SHEDNAME="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$SHED_ID'";
$STMT_SHEDNAME=$iconn->query($SQL_SHEDNAME);
$row_SHEDNAME=$STMT_SHEDNAME->fetch_array();
	?>
	<tr >
			
	<td  id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['T_NO']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['SoakingDate']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['Variety']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['TimePeriod']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['TIME1']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['C1']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['QTY']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['C2']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['APP_QT']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SHEDNAME['UNIT_NAME']; ?> </td>
	<td id="<?php  echo $row_SOACKING['ID'];?>" > <?php echo $row_SOACKING['Remarks']; ?> </td>
	<TD>
	<button id="<?php  echo $row_SOACKING['id'];?>" 
	onClick="reply_click(this.id)" class="glyphicon-remove btn btn-danger"> Delete</button>
	</TD>
	
    </tr>
	
	
	  <?php 
	  $S_NO++;

   }
	  ?>

     </tbody>

  </table>
</div>
</div>

</div>


    </section>
	
</div>

    <?php include "../includes/footer.php"; ?>
	<?php include "../includes/scripts.php"; ?>
 <?php include "../includes/AjaxLoader.php"; ?>

  </div>

</body>


<script>
  function reply_click(clicked_id) {

   		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'SOACKING_REPORT','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  
			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			location.reload();
			}
			});					  
  } 
</script>


</html>

