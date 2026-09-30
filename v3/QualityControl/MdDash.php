<?php
session_start();
include "../includes/config.php";


if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}

if(isset($_GET['date'])){
    $date_start = $_GET['date'];
 }else{


$sql_inp_data="SELECT PURCHASE_DATE FROM inputmaterial_details ORDER BY id DESC LIMIT 1";
$stmt_inp=$iconn->query($sql_inp_data);
$row=$stmt_inp->fetch_array();
$date=$row['PURCHASE_DATE'];


$date_start=substr($date,0,10); 
 }
 
$sql_run_po="SELECT COUNT(*) AS pos_count,SUM(INVOICE_VALUE) AS inv_value FROM `CS_POS`";
$stmt_run_po=$iconn->query($sql_run_po);
$row_run_po=$stmt_run_po->fetch_array();

$pos_count=$row_run_po['pos_count'];
$inv_value=$row_run_po['inv_value'];

$sql_shp_po="SELECT COUNT(*) AS pos_count,SUM(INVOICE_VALUE) AS inv_value FROM `CS_POS` where  PO_STATUS =1";
$stmt_shp_po=$iconn->query($sql_shp_po);
$row_shp_po=$stmt_shp_po->fetch_array();

$shp_count=$row_shp_po['pos_count'];




$sql_cs_consig="SELECT count(*) as  CONSIGNEE_count FROM `CS_CONSIGNEE_DETAILS`";
$stmt_cs_consig=$iconn->query($sql_cs_consig);
$row_cs_consig=$stmt_cs_consig->fetch_array();

$cs_consign_count=$row_cs_consig['CONSIGNEE_count'];


$sql_cs_pos="SELECT * FROM `CS_POS`";
$stmt_cs_pos=$iconn->query($sql_cs_pos);
while($row_cs_pos=$stmt_cs_pos->fetch_array()){ 
	$grades[]=(int)$row_cs_pos['INVOICE_VALUE'];
	//$gdate[]=$row_cs_pos['ID'];
	$gdate[]=$row_cs_pos['PO_DATE'];
}

$cs_consign_count=$row_cs_consig['CONSIGNEE_count'];




//$date_start=substr($date,0,10);
$date_end=$date_start.' 23:59:59';




 

$PO_TIME_STAMP = strtotime($date_start);
$DATEt = date("d-mm-Y", $PO_TIME_STAMP);
$purchase_date = date('d/m   /Y',strtotime($date_start));
$PD_END=$DATEt.' 23:59:59';

$PO_TME_STAMPP=strtotime($row['DATE_TIME']);
$DATE_TIME=date("d-m-Y", $PO_TME_STAMPP);


    $sql_inp="SELECT SUPERVISOR,COUNT, IF(SUPERVISOR = 'MADHU',SUM(WEIGHT_IN_KGS), 0) as 'MADHU_WEIGHT' ,
   IF(SUPERVISOR = 'VENU',SUM(WEIGHT_IN_KGS), 0) as 'VENU_WEIGHT' FROM
inputmaterial_details WHERE `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and `DATE_TIME` BETWEEN '$date_start' AND '$date_end' 
 GROUP BY COUNT,SUPERVISOR";


 



 
$stmt_inp=$iconn->query($sql_inp);

$count_v=array();
$count_m=array();

$data_v=array();
$data_m=array();

$COUNT=array();
$M_W=array(); 
$V_W=array();

while($row=$stmt_inp->fetch_array()){
$string=$row['COUNT'];

if(in_array($string,$COUNT)){	
}else{
	$COUNT[]=$string;	
}
//$COUNT[]=$string;
//$V_W[]=$row['VENU_WEIGHT'];
//$M_W[]=$row['MADHU_WEIGHT'];

if($row['MADHU_WEIGHT']=='0'){
$count_v[]=$string;
$data_v[]=$row;
}else{
$count_m[]=$string;
$data_m[]=$row;
}

$data[]=$row;
	
	
}



$added_Counts=array();
foreach($data as $value){
	 $p_count=$value['COUNT'];
	 
	 if(in_array($p_count,$added_Counts)){
	 }else{
	 $added_Counts[]=$p_count;
	 if(in_array($p_count,$count_v)){
		$key_venu = array_search($p_count, $count_v);
		$V_W[]=$data_v[$key_venu]['VENU_WEIGHT'];
		}else{
			$V_W[]='';
		}
	
	if(in_array($p_count,$count_m)){
		$key_madhu = array_search($p_count, $count_m);
		$M_W[]=$data_m[$key_madhu]['MADHU_WEIGHT'];
	}else{
		$M_W[]='';
	}
	
	 }
	
	
	
}
//$date_end=date('Y-m-d', strtotime('today - 30 days'));
//$date_end=$date_end .' 23:59:59';
//$date_start = $date_start.' 23:59:59';


$PO_TIME_STAMP = strtotime($date_start);
$DATEt = date("d-m-Y", $PO_TIME_STAMP);
$PD_END=$DATEt.' 23:59:59';


//total weight of lotd today

  $sql_inp="select COUNT,SUPERVISOR,AVAL_WEIGHT_TO_PROCESS,WEIGHT_IN_KGS,DATE_TIME from 
inputmaterial_details WHERE `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and `PURCHASE_DATE` BETWEEN '$DATEt' AND '$PD_END' 
ORDER BY COUNT ASC";
$stmt_inp=$iconn->query($sql_inp);       
//$row_inp=$stmt_inp->fetch_array();
//$COUNT=$row_inp['COUNT'];

$PURCHASE_WEIGHT=0;
while($row=$stmt_inp->fetch_array()){
	$PURCHASE_WEIGHT=$PURCHASE_WEIGHT+$row['WEIGHT_IN_KGS'];
$result[]=$row;
}

  $LOT_COUNT=count($result);

//end
  $sql="select * from inputmaterial_details WHERE `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and STATUS='0' 
and `DATE_TIME` BETWEEN '$date_end' AND '$date_start' ORDER BY id desc";
//exit;

 
$stmt=$iconn->query($sql);


$sql_s_m_p_d="select * from MATERIAL_PROCESSING_DATA where LOT_NUMBER='$iid'";

$stmt_s_m_p_d=$iconn->query($sql_s_m_p_d);

 
  $sql_count="SELECT count(`REFERENCE_ID`) as TOTAL,
(SELECT count(`STATUS`) FROM `inputmaterial_details` where `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and `STATUS`='0') as PENDING,
(SELECT count(`STATUS`) FROM `inputmaterial_details` where `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and `STATUS`='1') as COMPLETED,
(SELECT count(`STATUS`) FROM `inputmaterial_details` where  `SAMPLE_TYPE`='PRE HARVEST') as PRODUCTION

 FROM `inputmaterial_details`"; 

 
 


 
 
 

$stmt_ip=$iconn->query($sql_count);
$row=$stmt_ip->fetch_array();



$TOTAL_LOTS=$row['TOTAL'];
$PENDING_LOTS=$row['PENDING'];
$COMPLETED=$row['COMPLETED'];
$PRODUCTION=$row['PRODUCTION'];



   $sql_ABS="SELECT count(`PO_NUM`) as TOTAL,
(SELECT count(`DESTINATION_COUNTRY`) FROM `INVOICE_DATA` where `DESTINATION_COUNTRY`='CHINA') as CHINA,
(SELECT count(`DESTINATION_COUNTRY`) FROM `INVOICE_DATA` where `DESTINATION_COUNTRY`='U.S.A') as USA,
(SELECT count(`DESTINATION_COUNTRY`) FROM `INVOICE_DATA` where `DESTINATION_COUNTRY`='SOUTH AFRICA') as S_AFRICA,
(SELECT count(`DESTINATION_COUNTRY`) FROM `INVOICE_DATA` where `DESTINATION_COUNTRY`='NEW ZEALAND') as N_Z,
(SELECT count(`DESTINATION_COUNTRY`) FROM `INVOICE_DATA` where `DESTINATION_COUNTRY`='VIETNAM') as VIETNAM,
(SELECT count(`DESTINATION_COUNTRY`) FROM `INVOICE_DATA` where `DESTINATION_COUNTRY`='CANADA') as CANADA

 FROM `INVOICE_DATA`";

$stmt_ABS=$iconn->query($sql_ABS);
$row_ABS=$stmt_ABS->fetch_array();

 $TOTAL=$row_ABS['TOTAL'];
 $CHINA=$row_ABS['CHINA'];
 $USA=$row_ABS['USA'];
 $S_AFRICA=$row_ABS['S_AFRICA'];
 $N_Z=$row_ABS['N_Z'];
 $VIETNAM=$row_ABS['VIETNAM'];
 $CANADA=$row_ABS['CANADA'];
 
 $CHINA_PERC=($CHINA/$TOTAL)*100;
 $USA_PERC=($USA/$TOTAL)*100;
 $S_AFRICA_PERC=($S_AFRICA/$TOTAL)*100;
 $N_Z_PERC=($N_Z/$TOTAL)*100;
 $VIETNAM_PERC=($VIETNAM/$TOTAL)*100;
 $CANADA_PERC=($CANADA/$TOTAL)*100;




?>

<!DOCTYPE html>
<html>
<head>
<?php include "../includes/meta.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">


<div class="wrapper">
<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
     <section class="content-header">
	  <div class="row">
        <div class="col-md-6">
         
      <h4>
        Sales
        <small></small>
      </h4>
	    </div>
				
				 <div class="col-md-6">
          
	 
       <div class="btn-group" align="right">
                  <button type="button" class="btn btn-info">Action</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Last 24 Hours</a></li>
                    <li><a href="#">Last 7 Days</a></li>
                    <li><a href="#">Last Month</a></li>
                  </ul>
                </div>
				
				</div>
				</div>
    </section>

    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
       
		
		<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="ion ion-bag"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Orders</span>
              <span class="info-box-number"><?php echo $pos_count ?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		
		 <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Shipments</span>
              <span class="info-box-number"><?php echo $shp_count ?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		
		   <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Clients</span>
              <span class="info-box-number"><?php echo $cs_consign_count ?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		
		 <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-fw fa-dollar"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Revenue</span>
              <span class="info-box-number"><?php echo $inv_value ?><small> usd</small></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		

      </div>
	  
	  
	

    <!-- Main content -->
    <section class="content">
	
	

	
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
		

		
		
		
      <div class="row">
	  <div class="col-xs-12">
    			


					<div class="col-xs-3">
                    <label>Select From Date: </label>
                    <input type='date' class="form-control" name="FROM_DATE" id="FROM_DATE"></input>
					</div>
					
					<div class="col-xs-3">
					<label>Select To Date: </label>
                    <input type='date' class="form-control" name="TO_DATE" id="TO_DATE"></input>
					</div>
				  
				  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Exchange Rate</label>
                  <input type="text" class="form-control"  name="EXCHANGE_RATE" id="EXCHANGE_RATE" 
				  placeholder="Enter Exchange Rate">
                </div>
				
				  
				  <div class="form-group col-sm-3">
				  <p><br></p>
                 <button id="list" align="right" class='btn btn-success btn-sm btn-flat'><i class= 'fa fa-search'>
				 </i> Get List</button>				  
                 </div>
				


    				</div>
	  </div>
	  
	  <div class="box-body" id="ATT_DATA">
              
	</div>
	
	
	
      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Monthly Recap Report</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-wrench"></i></button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
			
			
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-8">
                  <p class="text-center">
                    <strong>Sales: 1 Jan, 2014 - 30 Jul, 2014</strong>
                  </p>

                   <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Line Chart</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <canvas id="lineChart" style="height:250px"></canvas>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
				  
				  
                </div>
                <!-- /.col -->
                <div class="col-md-4">
                  <p class="text-center">
                    <strong>Purchase Orders</strong>
                  </p>

                 <div class="progress-group">
                    <span class="progress-text">CHINA</span>
                    <span class="progress-number"><b><?php echo $CHINA ?></b>/<?php echo $TOTAL ?></span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width: <?php echo $CHINA_PERC ?>%;background: #FA8072;"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">USA</span>
                    <span class="progress-number"><b><?php echo $USA ?></b>/<?php echo $TOTAL ?></span>

                    <div class="progress sm">
                      <div class="progress-bar" style="width: <?php echo $USA_PERC ?>%;background: #FFD700;"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Newzland</span>
                    <span class="progress-number"><b><?php echo $N_Z ?></b>/<?php echo $TOTAL ?></span>

                    <div class="progress sm">
                      <div class="progress-bar" style="width: <?php echo $N_Z_PERC ?>%;background: #006400;"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">CANADA</span>
                    <span class="progress-number"><b><?php echo $CANADA ?></b>/<?php echo $TOTAL ?>  </span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width: <?php echo $CANADA_PERC ?>%;background: #191970;"></div>
                    </div>
                  </div>
                <div class="progress-group">
                    <span class="progress-text">South Africa</span>
                    <span class="progress-number"><b><?php echo $S_AFRICA ?></b>/<?php echo $TOTAL ?>  </span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width: <?php echo $S_AFRICA_PERC ?>% ;background: #8B008B;"></div>
                    </div>
                  </div>
                 <div class="progress-group">
                    <span class="progress-text">VIETNAM</span>
                    <span class="progress-number"><b><?php echo $VIETNAM ?></b>/<?php echo $TOTAL ?>  </span>

                    <div class="progress sm">     
                      <div class="progress-bar " style="width: <?php echo $VIETNAM_PERC ?>%;background: #8B4513;"></div>
                    </div>
                  </div>

                  <!-- /.progress-group -->
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
			
			
			
            <!-- ./box-body -->
            <div class="box-footer">
              <div class="row">
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span>
                    <h5 class="description-header">$35,210.43</h5>
                    <span class="description-text">TOTAL REVENUE</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>
                    <h5 class="description-header">$10,390.90</h5>
                    <span class="description-text">TOTAL COST</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>
                    <h5 class="description-header">$24,813.53</h5>
                    <span class="description-text">TOTAL PROFIT</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block">
                    <span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>
                    <h5 class="description-header">1200</h5>
                    <span class="description-text">GOAL COMPLETIONS</span>
                  </div>
                  <!-- /.description-block -->
                </div>
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
	
	

              <!--<h3  class="box-title" align='center'>Running Lots<span style="color:red"> <?php 
			   
$date_end= date("d-m-Y", strtotime($date_end) );
$date_start= date("d-m-Y", strtotime($date_start) );


			  echo $date_end ." To " .$date_start ?></span> </h3>      -->

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
<!-- ChartJS -->
<script src="../bower_components/chart.js/Chart.js"></script>
<!-- FLOT CHARTS -->
<script src="../bower_components/Flot/jquery.flot.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script src="../bower_components/Flot/jquery.flot.resize.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script src="../bower_components/Flot/jquery.flot.pie.js"></script>
<!-- FLOT CATEGORIES PLUGIN - Used to draw bar charts -->
<script src="../bower_components/Flot/jquery.flot.categories.js"></script>





<script type="text/javascript">

$('document').ready(function(){
	$('#list').click(function() {		
		var from_date=$('#FROM_DATE').val();
		var to_date=$('#TO_DATE').val();
		var EX_RATE=$('#EXCHANGE_RATE').val();
		
		if(from_date==''){
		alert("Please Enter From Date");
		$('#FROM_DATE').focus();
		return;
		}else if(to_date==''){
		alert("Please Enter To Date");
		$('#TO_DATE').focus();
		return;
		}else if(EX_RATE==''){
		alert("Please Enter EXCHANGE RATE");
		$('#EXCHANGE_RATE').focus();
		return;
		}else{		
			$.ajax({
			  	url:'../Accounts/AjaxCost.php',
			  	type:'POST',
			  	data:{'FROM_DATE':from_date,'TO_DATE' : to_date,'EXCHANGE_RATE' : EX_RATE },
			  	success:function(data){
				   	console.log(data);
					$("#ATT_DATA").html(data);
					// $('#example1').DataTable()
				}
			});		
		}
	});
});
$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
});
$(document).ajaxComplete(function(){
	$("#wait").css("display", "none");
});
</script>


<script>
  $(function () {
    /* ChartJS
     * -------
     * Here we will create a few charts using ChartJS
     */

    //--------------
    //- AREA CHART -
    //--------------

    // Get context with jQuery - using jQuery's .get() method.
   // var areaChartCanvas = $('#areaChart').get(0).getContext('2d')
    // This will get the first returned node in the jQuery collection.
   // var areaChart       = new Chart(areaChartCanvas)

    var areaChartData = {
      labels  : ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
      labels  : <?php echo  json_encode($gdate) ?>,
      datasets: [
        
        {
          label               : 'Digital Goods',
          fillColor           : 'rgba(60,141,188,0.9)',
          strokeColor         : 'rgba(60,141,188,0.8)',
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : <?php echo  json_encode($grades) ?>
        }
      ]
    }
	
	//alert(data);

    var areaChartOptions = {
      //Boolean - If we should show the scale at all
      showScale               : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : false,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - Whether the line is curved between points
      bezierCurve             : true,
      //Number - Tension of the bezier curve between points
      bezierCurveTension      : 0.3,
      //Boolean - Whether to show a dot for each point
      pointDot                : false,
      //Number - Radius of each point dot in pixels
      pointDotRadius          : 4,
      //Number - Pixel width of point dot stroke
      pointDotStrokeWidth     : 1,
      //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
      pointHitDetectionRadius : 20,
      //Boolean - Whether to show a stroke for datasets
      datasetStroke           : true,
      //Number - Pixel width of dataset stroke
      datasetStrokeWidth      : 2,
      //Boolean - Whether to fill the dataset with a color
      datasetFill             : true,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio     : true,
      //Boolean - whether to make the chart responsive to window resizing
      responsive              : true
    }

    //Create the line chart
  //  areaChart.Line(areaChartData, areaChartOptions)

    //-------------
    //- LINE CHART -
    //--------------
    var lineChartCanvas          = $('#lineChart').get(0).getContext('2d')
    var lineChart                = new Chart(lineChartCanvas)
    var lineChartOptions         = areaChartOptions
    lineChartOptions.datasetFill = false
    lineChart.Line(areaChartData, lineChartOptions)

    //-------------
    //- PIE CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
  //  var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
  //  var pieChart       = new Chart(pieChartCanvas)
    var PieData        = [
      {
        value    : 700,
        color    : '#f56954',
        highlight: '#f56954',
        label    : 'Chrome'
      },
      {
        value    : 500,
        color    : '#00a65a',
        highlight: '#00a65a',
        label    : 'IE'
      },
      {
        value    : 400,
        color    : '#f39c12',
        highlight: '#f39c12',
        label    : 'FireFox'
      },
      {
        value    : 600,
        color    : '#00c0ef',
        highlight: '#00c0ef',
        label    : 'Safari'
      },
      {
        value    : 300,
        color    : '#3c8dbc',
        highlight: '#3c8dbc',
        label    : 'Opera'
      },
      {
        value    : 100,
        color    : '#d2d6de',
        highlight: '#d2d6de',
        label    : 'Navigator'
      }
    ]
    var pieOptions     = {
      //Boolean - Whether we should show a stroke on each segment
      segmentShowStroke    : true,
      //String - The colour of each segment stroke
      segmentStrokeColor   : '#fff',
      //Number - The width of each segment stroke
      segmentStrokeWidth   : 2,
      //Number - The percentage of the chart that we cut out of the middle
      percentageInnerCutout: 50, // This is 0 for Pie charts
      //Number - Amount of animation steps
      animationSteps       : 100,
      //String - Animation easing effect
      animationEasing      : 'easeOutBounce',
      //Boolean - Whether we animate the rotation of the Doughnut
      animateRotate        : true,
      //Boolean - Whether we animate scaling the Doughnut from the centre
      animateScale         : false,
      //Boolean - whether to make the chart responsive to window resizing
      responsive           : true,
      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio  : true,
      //String - A legend template
      legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
    }
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
  //  pieChart.Doughnut(PieData, pieOptions)

    //-------------
    //- BAR CHART -
    //-------------
   // var barChartCanvas                   = $('#barChart').get(0).getContext('2d')
  //  var barChart                         = new Chart(barChartCanvas)
    //var barChartData                     = areaChartData
    //barChartData.datasets[1].fillColor   = '#00a65a'
   // barChartData.datasets[1].strokeColor = '#00a65a'
   // barChartData.datasets[1].pointColor  = '#00a65a'
    var barChartOptions                  = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero        : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : true,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - If there is a stroke on each bar
      barShowStroke           : true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth          : 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing         : 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing       : 1,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to make the chart responsive
      responsive              : true,
      maintainAspectRatio     : true
    }

    barChartOptions.datasetFill = false
  //  barChart.Bar(barChartData, barChartOptions)
  })
</script>



</html>
