<?php
session_start();
include "../includes/config.php";

include "../includes/Globals.php";

    $from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];

  $UNIT=$_GET['U'];
  $TYPE=$_GET['TYPE'];  // melting =1 NEW =0


 $month_name= date("F", strtotime($from_date));
  
  $originalDate1 = $to_date;
$newDate1 = date("d-m-Y", strtotime($originalDate1));

$originalDate2 = $from_date;
$newDate2 = date("d-m-Y", strtotime($originalDate2));

$date=$newDate2. ' TO  ' .$newDate1;


 
 
 $UNIT_NAME='';
      if($UNIT ==0){
		
      $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS`";
      $StmtProStations=$iconn->query($SqlProStations);

      	while($Row=$StmtProStations->fetch_array()){
          $UNIT=$Row['ID'];
		            //$UNIT=$Row['UNIT_ID'];

          $UNIT_NAME=$Row['UNIT_NAME'];
          /*$table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
          
		  if($table_data['TOTAL_SENT_WEIGHT']!=0){
			$pdf->writeHTML($table_data['TABLE']);
			}*/
        }
      }else{
		  
		  
        $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
		
        $StmtProStations=$iconn->query($SqlProStations);
$Row=$StmtProStations->fetch_array();
		
  $UNIT_NAME=$Row['UNIT_NAME'];      
       /*$table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
      $pdf->writeHTML($table_data['TABLE']);*/
      }
if($UNIT!=0){
$query="and PS.ID='$UNIT'";
}else{
  $query="";
}
if($TYPE==1){
$query.="and imd.id='0'";
}else{
 // $query="";
}
//global $UNIT,$TYPE;
	$date_end=$to_date.' 23:59:59';
	
		
	 $sql_lots="SELECT DISTINCT
	  imd.id,
	  imd.LOT_NO,
	  imd.ANTI_BIOTIC_STATUS,
	  imd.PURCHASE_DATE,
	  imd.WEIGHT_IN_KGS,
	  VA.COUNT,
	  VA.PROCESSING_DATE,
	  VA.SENT_WEIGHT,
    VA.HL_QTY_RCVD,
    VA.RETURN_WEIGHT,
	  VA.WgtAfterValueAddition,
	  VA.VARIETY ,
	  VA.SHIFT ,
	  VA.REMARKS ,
	  VA.PROCESSING_DATE ,
	  PS.UNIT_NAME
	  from inputmaterial_details as imd
			 left join PPC_VALUE_ADDITION as VA on imd.id=VA.LOT_ID
			 left join RM_PROCESSING_STATIONS as PS on VA.STATION_ID =PS.ID
			  where  PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'
			  and VA.VARIETY!='DeHeading' $query
			  order by PURCHASE_DATE ASC,LOT_NO ASC,VARIETY ASC";
		


			 $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);
				
$SQL_PROCESSING_STATIONS="SELECT * FROM `RM_PROCESSING_STATIONS`";
$stmt_PROCESSING_STATIONS=$iconn->query($SQL_PROCESSING_STATIONS);	





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

      <h3 ALIGN ="CENTER"><B>Value Addition Report ( </B><strong style="
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



				
				<div class="btn-group" >
                  <label for="exampleInputEmail1">SELECT STATION</label>
						
		 <select class="form-control" name="STATION" id="STATION">		   
 <?php while($row_PROCESSING_STATIONS=$stmt_PROCESSING_STATIONS->fetch_array()) { 
			

        $STATION_ID=$row_PROCESSING_STATIONS['ID'];			
				?>  

				
				<option Value="<?php echo $row_PROCESSING_STATIONS['ID'];?>">
				<?php echo $row_PROCESSING_STATIONS['UNIT_NAME'];?></OPTION>				
				<?php } ?>
				</select> 
                </div>

				</div>
				
				
				
				
				
    </section>
	<div>
	<button id="list" align="CENTER" class='btn btn-success btn-sm btn-flat'><i class='fa fa-search'>
				</i> Get List</button>
</div>

    <section class="content">
      <!-- Small boxes (Stat box) -->









      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >
        <div class="box">

        <div class="box-body">





      <div class="row">
	  <div class="col-xs-12">

				


				


    				</div>
	  </div>
	  </div>

	          <div class="box-footer">

			   <div  align="center" class="form-group col-md-12 col-sm-12 col-xs-12">
				  <p><br></p>
         <!--        <button id="list"  class='btn btn-success btn-sm btn-flat'><i class= 'fa fa-search'>
				 </i> Get List</button>  -->


				 <div class="box-body" id="ATT_DATA">



	</div>

                 </div>

			  </div>




	  <div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		  <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>



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

<script>
		$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			//var MONTH=$('#MONTH').val();
			/*var YEAR=$('#YEAR').val();
			var FROM_DATE=$('#FROM_DATE').val();
			var TO_DATE=$('#TO_DATE').val();*/
			var STATION=$('#STATION').val();
			alert(STATION);
			var from_date='<?php echo $from_date; ?>';
			var to_date='<?php echo $to_date; ?>';
			
			//alert(YEAR);
			
			   
			
			$.ajax({
		  url:"test_ajax.php?from_date="+from_date+"&to_date="+to_date,
		  type:'POST',
		  data:{'STATION':STATION},
		  success:function(data){
			  // console.log(data);
			   $("#ATT_DATA").html(data);
             $('#example1').DataTable();
					}
			});
			//return false;
			
    		  
    
    	});	</script>

<script type="text/javascript" 
src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
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
