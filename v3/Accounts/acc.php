<?php
session_start();
include "../includes/config.php";


if($_SESSION["r_id"]==''){
	header('Location: index.php?msg=Please enter your User name and Password.');
}

  $date_startt = date('Y-m-d'); 
    $date_starttt=date("d-m-Y", strtotime($date_startt));

  $date_endd=date('Y-m-d', strtotime('today - 7 days'));
  $date_enddd=date("d-m-Y", strtotime($date_endd));





$sql_inp_data="SELECT PURCHASE_DATE FROM inputmaterial_details ORDER BY id DESC LIMIT 1";
$stmt_inp=$iconn->query($sql_inp_data);
$row=$stmt_inp->fetch_array();

$date=$row['PURCHASE_DATE'];
$p_date=$row['PURCHASE_DATE'];

$date_start=$date;
$date_end=$date_start.' 23:59:59';


 /* $sql_d="SELECT DATE_TIME`
FROM inputmaterial_details
WHERE DATE_TIME >= DATEADD(day,7, GETDATE())"; 
$stmt_d=$iconn->query($sql_d);
$row_d=$stmt_d->fetch_array();




     $date_t = $row_d['DATE_TIME'];*/
//$date_t= DATEADD(day, -7, GETDATE());


    $sql_inp="SELECT SUPERVISOR,COUNT, IF(SUPERVISOR = 'MADHU',SUM(WEIGHT_IN_KGS), 0) as 'MADHU_WEIGHT' ,
   IF(SUPERVISOR = 'VENU',SUM(WEIGHT_IN_KGS), 0) as 'VENU_WEIGHT' FROM
inputmaterial_details WHERE `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and `DATE_TIME` BETWEEN
 '$date_start' AND '$date_start' 
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
$date_end=date('Y-m-d', strtotime('today - 30 days'));
//$date_end=$date_end .' 23:59:59';
$date_start = $date_start.' 23:59:59';


    $sql="select * from inputmaterial_details WHERE `UNIT_ID`!='' and SAMPLE_TYPE!='PRE HARVEST' and STATUS='0' 
and `PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' ORDER BY id desc"; 
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



    $sql_ABS="SELECT  `PURCHASE_DATE`,count(`LOT_NO`) as TOTAL,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='2') as ACCEPTED,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='1') as REJECTED,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='3') as HOLD,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='0') as PENDING

 FROM `inputmaterial_details` where PURCHASE_DATE between '$date_endd' and '$date_startt'  ";  //'$date_start' AND '$date_endd'

$stmt_ABS=$iconn->query($sql_ABS);
$row_ABS=$stmt_ABS->fetch_array();


    $TOTAL=$row_ABS['TOTAL'];  $ACCEPTED=$row_ABS['ACCEPTED'];
 $REJECTED=$row_ABS['REJECTED'];
 $HOLD=$row_ABS['HOLD'];
 $PENDING=$row_ABS['PENDING'];
 
 $ACCEPTED_PERC=($ACCEPTED/$TOTAL)*100;
 $REJECTED_PERC=($REJECTED/$TOTAL)*100;
 $HOLD_PERC=($HOLD/$TOTAL)*100;
 $PENDING_PERC=($PENDING/$TOTAL)*100;
//uma

  $sql_a="SELECT  count(LOT_NO) as TOTAL FROM `inputmaterial_details` where PURCHASE_DATE between '$date_endd' and '$date_startt'"; 
 $stmt_a=$iconn->query($sql_a);
 $row_a=$stmt_a->fetch_array();
 
   $a=$row_a['TOTAL']; 



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
    <!-- Content Header (Page header) -->
  
  
  
      <div class="row" style="background :white">
	  
	   <div class="box-header with-border">
              <h3 class="box-title"  style="text-align:center">  Accounts Updates: <?php echo $PD_START ?></h3>
              <div class="box-tools pull-right">
                <form class="form-inline">
                  <div class="form-group">
				  
                    <label align ="left">From Date: </label>
					<?php echo $date_starttt ?>
															


                   	
							<label>To Date: </label>
								<?php echo $date_enddd ?>		
										

							
                  </div>
                </form>
              </div>
			  

            </div>
			
			</div>  
	
	
	
    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?php echo $TOTAL ?></h3>

              <p>Total Lots</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="LotsList.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              
			  
			   <h3><?php echo $PENDING_LOTS ?></h3>

              <p>Running Lots</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="LotsList.php?id=0" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3><?php //echo $COMPLETED; ?></h3>

              <p>Pending Lots</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="LotsList.php?id=1" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3><?php echo $PRODUCTION ?></h3>

              <p>Pre Harvest Lots</p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
            <a href="LotsList.php?id=ph" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>
	  
	  
	

    <!-- Main content -->
    <section class="content">
	
	

	
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
		

		
		
		
      <div class="row">
       
			<div class="col-md-6">
        
		 
		  
		    <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Purchase Chart (Weight vs Count)     -<b> <?php echo $purchase_date  ?></b></h3>
			  
			  <span class="label" align="center" style="background:<?php $CLR='#00a65a'; echo $CLR; ?>">BVRM</span>
			  <span class="label" align="center" style="background:<?php $CLR='#f39c12'; echo $CLR; ?>">NSP</span>

			  <div class="bg-active color-palette" align="center" >
			
			</div>
             
			 <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <canvas id="barChart" style="height:260px"></canvas>
              </div>
            </div>
            <!-- /.box-body -->
          </div>

        </div>
         

        
        <!-- /.col (LEFT) -->
       <div class="col-md-6">
	    <div class="box box-primary">
                  <div class="box-header with-border">
              <h3 class="box-title">AntiBiotic Test status</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
			


                  <div class="progress-group">
                    <span class="progress-text">Test Pending Lots</span>
                    <span class="progress-number"><b><?php echo $PENDING ?></b>/<?php echo $TOTAL ?></span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width: <?php echo $PENDING_PERC ?>%;background: gray;"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Rejected Lots</span>
                    <span class="progress-number"><b><?php echo $REJECTED ?></b>/<?php echo $TOTAL ?></span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-red" style="width: <?php echo $REJECTED_PERC ?>%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Accepted Lots</span>
                    <span class="progress-number"><b><?php echo $ACCEPTED ?></b>/<?php echo $TOTAL ?></span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-green" style="width: <?php echo $ACCEPTED_PERC ?>%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">On Hold Lots</span>
                    <span class="progress-number"><b><?php echo $HOLD ?></b>/<?php echo $TOTAL ?>  </span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-blue" style="width: <?php echo $HOLD_PERC ?>%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
				  </div>
				  </div>
                </div>



        </tr>


 </tbody>

  </table>
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
<script src="/bower_components/chart.js/Chart.js"></script>
<script>
  $(function () {
   $('#example1').dataTable({
   "order": [[ 0, 'desc' ]]
});
  })
</script>

<script>
  $(function () {
    "use strict";

 // LINE CHART
 var test=<?php echo json_encode($data); ?>;

 
    var line = new Morris.Line({
      element: 'line-chart',
      resize: false,
	  
      data: test,
      xkey: 'DATE',
      ykeys: ['MADHU_WEIGHT','VENU_WEIGHT'],
      labels: ['MADHU','VENU'],
      lineColors: ['#f39c12', '#3c8dbc'],
      hideHover: 'auto'
    });

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

	
	 var date=<?php echo json_encode($COUNT); ?>;
 var V_W=<?php echo json_encode($V_W); ?>;
 var M_W=<?php echo json_encode($M_W); ?>;

    // Get context with jQuery - using jQuery's .get() method.
    var areaChartCanvas = $('#barChart').get(0).getContext('2d')
    // This will get the first returned node in the jQuery collection.
    var areaChart       = new Chart(areaChartCanvas)
	



    var areaChartData = {
      labels  : date,
 
      datasets: [
        {
          label               : 'Venu',
          fillColor           : '#f39c12',
          strokeColor         : '#f39c12',
          pointColor          : '#f39c12',
          pointStrokeColor    : '#f39c12',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                :  V_W
        },
        {
          label               : 'Madhu',
          fillColor           : '#3c8dbc',
          strokeColor         : '#3c8dbc',
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : M_W
        }
      ]
    }

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
   // areaChart.Line(areaChartData, areaChartOptions)

    //-------------
    //- LINE CHART -
    //--------------
 /*   var lineChartCanvas          = $('#lineChart').get(0).getContext('2d')
    var lineChart                = new Chart(lineChartCanvas)
    var lineChartOptions         = areaChartOptions
    lineChartOptions.datasetFill = false
    lineChart.Line(areaChartData, lineChartOptions)
*/
    
    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas                   = $('#barChart').get(0).getContext('2d')
    var barChart                         = new Chart(barChartCanvas)
    var barChartData                     = areaChartData
    barChartData.datasets[1].fillColor   = '#00a65a'
    barChartData.datasets[1].strokeColor = '#00a65a'
    barChartData.datasets[1].pointColor  = '#00a65a'
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
    barChart.Bar(barChartData, barChartOptions)
  })
</script>


</html>
