<?php
include "../../includes/config.php";



 $date_start = $_POST['FROM_DATE'];
 $date_end = $_POST['TO_DATE'].' 23:59:59';
 $UNIT = $_POST['UNIT'];
 $TYPE = $_POST['TYPE'];

 if($UNIT!=0){
 $UNIT_SQL = "and UNIT='$UNIT'";
 }else{
	$UNIT_SQL = "";
 }
 
 $SqlItems="SELECT * FROM `OPTIONS` WHERE TYPE='HR_DASH_OPTIONS'";
$StmtItems=$iconn->query($SqlItems);



$sql_lots="SELECT count(*) AS SLOTS FROM inputmaterial_details
           WHERE LOT_NO LIKE 'S%' and
          `PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end'";
$stmt_lots=$iconn->query($sql_lots);
$row_lots=$stmt_lots->fetch_array();

$SHED_LOTS=$row_lots['SLOTS'];
 
 
     $sql_ABS="SELECT count(`LOT_NO`) as TOTAL,
(SELECT count(*)  FROM `HRM_DEPARTMENT_MASTER`) as DeptCount,
(SELECT count(*)  FROM `HRM_DESIGNATION`) as DesiMaster,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` 
where `ANTI_BIOTIC_STATUS`='3' and
`PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL) as HOLD,
(SELECT count(`STATUS`) FROM `inputmaterial_details` where `STATUS`='1' and
`PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL) as RETURN_L,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` 
where `ANTI_BIOTIC_STATUS`='0'and
`PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL) as PENDING

 FROM `inputmaterial_details` where `PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL ";



$stmt_ABS=$iconn->query($sql_ABS);
$row_ABS=$stmt_ABS->fetch_array();



 $TOTAL=$row_ABS['TOTAL'];
 $ACCEPTED=$row_ABS['ACCEPTED'];
 $REJECTED=$row_ABS['REJECTED'];
 $HOLD=$row_ABS['HOLD'];
 $PENDING=$row_ABS['PENDING'];
 $RETURN=$row_ABS['RETURN_L'];
 $PENDING=$PENDING-$SHED_LOTS;
 $TOTAL=$TOTAL-$SHED_LOTS;
 $ACCEPTED_PERC=($ACCEPTED/$TOTAL)*100;
 $REJECTED_PERC=($REJECTED/$TOTAL)*100;
 $HOLD_PERC=($HOLD/$TOTAL)*100;
 $PENDING_PERC=($PENDING/$TOTAL)*100;
 $RETURN_PERC=($RETURN/$TOTAL)*100;




  $sql_ABS="SELECT sum(TOTAL_REPORTS) as TOTAL_REPORTS FROM `DAILY_LAB_DATA` where REPORT_DATE BETWEEN '$date_start' and '$date_end'";

$stmt_ABS=$iconn->query($sql_ABS);
$row_ABS=$stmt_ABS->fetch_array();


$TOTAL_TESTING=(isset($row_ABS['TOTAL_REPORTS']) ? $row_ABS['TOTAL_REPORTS'] : "0");





    $sql_orc="SELECT  count(DISTINCT LOT_ID) as TOTAL FROM `ORGN_INSP_REPORT`
 join inputmaterial_details on inputmaterial_details.id=ORGN_INSP_REPORT.LOT_ID where
   `SUBMISSION_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL" ;
$stmt_orc=$iconn->query($sql_orc);
$row_orc=$stmt_orc->fetch_array();
 $ORG_COUNT=(isset($row_orc['TOTAL']) ? $row_orc['TOTAL'] : "0");



  $sql="SELECT count(*) as TOTAL FROM PPC_GRADING_HL
 join inputmaterial_details on inputmaterial_details.id=PPC_GRADING_HL.LOT_ID
 where SUBMISSION_DATE  BETWEEN '$date_start' AND '$date_end' $UNIT_SQL";


$stmt=$iconn->query($sql);
$row_grd=$stmt->fetch_array();
$GRD_COUNT=$row_grd['TOTAL'];



  $sql="SELECT count(*) as NO_BILLS from inputmaterial_details
	LEFT join PPC_GRADING_HL on inputmaterial_details.id=PPC_GRADING_HL.LOT_ID
	where PPC_GRADING_HL.DATA_TYPE='Headless Grading' and PURCHASE_DATE
	BETWEEN '$date_start' AND '$date_end' and NOT EXISTS
        (
        SELECT  LOT_ID
        FROM    PURCHASE_DETAILS d
        WHERE   d.LOT_ID = PPC_GRADING_HL.LOT_ID
        ) $UNIT_SQL";


$stmt=$iconn->query($sql);
$row_grd=$stmt->fetch_array();
$NO_BILLS=$row_grd['NO_BILLS'];

 $sql="SELECT count(*) AS NO_GRADING from inputmaterial_details where PURCHASE_DATE
		BETWEEN '$date_start' AND '$date_end' and NOT EXISTS
        ( SELECT  LOT_ID FROM    PPC_GRADING_HL  d WHERE   d.LOT_ID = inputmaterial_details.id ) $UNIT_SQL";

$stmt=$iconn->query($sql);
$row_grd=$stmt->fetch_array();
$NO_GRADING=$row_grd['NO_GRADING'];

  $sql="SELECT count(*) AS NO_COSTING from PURCHASE_DETAILS as PD
LEFT join PPC_GRADING_HL as GD on GD.LOT_ID=PD.LOT_ID
LEFT join inputmaterial_details as IMD on IMD.id=PD.LOT_ID where
  IMD.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' and GD.STATUS=0  $UNIT_SQL";

  $stmt=$iconn->query($sql);
$row_grd=$stmt->fetch_array();
$NO_COSTING=$row_grd['NO_COSTING'];

 $NO_BILLS_PERC=($NO_BILLS/$TOTAL)*100;
 $NO_GRADING_PERC=($NO_GRADING/$TOTAL)*100;
 $NO_COSTING_PERC=($NO_COSTING/$TOTAL)*100;
$TYPE=1;
if($TYPE==1){
  $sql_form="SELECT DISTINCT(date) as Date from attendance WHERE date 
  BETWEEN '$date_start' AND '$date_end' $UNIT_SQL order by date asc ";
 $InpQry="WHERE COUNT!=0 $UNIT_SQL and PURCHASE_DATE ";
}else if($TYPE==2){
  $sql_form="SELECT DISTINCT(AGENT_NAME)  as COUNT FROM `inputmaterial_details` where PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' $UNIT_SQL order by AGENT_NAME asc ";
 $InpQry="WHERE PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' $UNIT_SQL and AGENT_NAME ";
}else if($TYPE==3){
  $sql_form="SELECT DISTINCT(round(COUNT))   as COUNT FROM `inputmaterial_details` where PURCHASE_DATE  BETWEEN '$date_start' AND '$date_end' $UNIT_SQL order by COUNT asc ";
 $InpQry="WHERE PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' $UNIT_SQL and COUNT ";
}



$stmt_form=$iconn->query($sql_form);

$COUNT=array();
$M_W=array();
$V_W=array();



while($row=$stmt_form->fetch_array()){
 $Date = $row['Date'];

 $sql_mcs="SELECT count(ON_TIME) as OnTime,
(SELECT count(ON_TIME) FROM `attendance` where date='$Date' and ON_TIME=0) as Late
FROM `attendance` where date='$Date' and ON_TIME=1";


 $stmt_mcs=$iconn->query($sql_mcs);
 $row_mcs=$stmt_mcs->fetch_array();
 $MCS = ($row_mcs['OnTime']);
 $Late = ($row_mcs['Late']);
 $COUNT[] = $Date;
 $M_W[] = $MCS;
 $V_W[] = $Late;
}


$content = '<div class="row">';

while($RowItems=$StmtItems->fetch_array()){
	
		$content .= '<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-lightgreen"><img align="right"  src="../qc/images/hrmlogo.png" /></span>

            <div class="info-box-content">
              <span class="info-box-text">'.
			   $RowItems['OPTIONS'] .'</span>
              <span class="info-box-number">
			  </span>
			   <a href="../Purchase/LotsList.php" target="blank" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>

            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>';
}



	 $content.= '<div class="box-body" id="ATT_DATA">


      <div class="row">


      			<div class="col-md-12">



      		    <div class="box box-success">
                  <div class="box-header with-border">
                    <h3 class="box-title">Attendance Details</h3> 
					


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


      


            <!-- ./box-body -->
            <div class="box-footer">
                           <!-- /.row -->
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->



              <!--<h3  class="box-title" align="center">Running Lots<span style="color:red"> <?php

$date_end= date("d-m-Y", strtotime($date_end) );
$date_start= date("d-m-Y", strtotime($date_start) );


			  echo $date_end ." To " .$date_start ?></span> </h3>      -->

</div>'	;




echo $content;



	?>

  <!-- ChartJS -->
  <script src="../bower_components/chart.js/Chart.js"></script>
  <!-- FLOT CHARTS -->
  <script src="../bower_components/Flot/jquery.flot.js"></script>
  <!-- FLOT PIE PLUGIN - also used to draw donut charts -->
  <script src="../bower_components/Flot/jquery.flot.pie.js"></script>


  <!-- Page script -->
  <script>
    $(function () {
     $('#example1').dataTable({
     "order": [[ 0, 'desc' ]]
  });
    })
  </script>




  <script>


    $(function () {


      /*
       * DONUT CHART
       * -----------
       */
  var PIE_DATA=<?php echo json_encode($dounat_DATA); ?>;
  var G_DATA=<?php echo json_encode($G_DATA); ?>;

      var donutData = PIE_DATA
      $.plot('#donut-chart', donutData, {
        series: {
          pie: {
            show       : true,
            radius     : 1,
            innerRadius: 0.5,
            label      : {
              show     : true,
              radius   : 2 / 3,
              formatter: labelFormatter,
              threshold: 0.1
            }

          }
        },
        legend: {
          show: true
        }
      })

  	var donutData = G_DATA
      $.plot('#PIE_LOT_ASS', donutData, {
        series: {
          pie: {
            show       : true,
            radius     : 1,
            innerRadius: 0.5,
            label      : {
              show     : true,
              radius   : 2 / 3,
              formatter: labelFormatter,
              threshold: 0.1
            }

          }
        },
        legend: {
          show: true
        }
      })
      /*
       * END DONUT CHART
       */

    })

    /*
     * Custom Label formatter
     * ----------------------
     */
    function labelFormatter(label, series) {
      return '<div style="font-size:13px; text-align:center; padding:2px; color:red; font-weight: 600;">'

        + '<br>'
  	  +'<div class="timeline-footer"><a class="btn btn-danger btn-xs">'
        + Math.round(series.percent) + '% </a></div></div>'
    }
  </script>



  <script>
    $(function () {

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
            fillColor           : '#e30e0e',
            strokeColor         : '#e30e0e',
            pointColor          : '#e30e0e',
            pointStrokeColor    : '#e30e0e',
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


      //-------------
      //- BAR CHART -
      //-------------


     // var AR_ASS_CA                   = $('#AR_ASS').get(0).getContext('2d')
  	// var AR_ASS_barChart                         = new Chart(AR_ASS_CA)

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
     // AR_ASS_barChart.Bar(barChartData, barChartOptions)
    })
  </script>


  <script>
    $(function () {
      "use strict";
   // LINE CHART
   var test=<?php echo json_encode($data); ?>;

   //alert(test);
        // LINE CHART
      var line = new Morris.Line({
        element: 'line-chart',
        resize: false,

        data: test,
        xkey: 'MADHU_WEIGHT',
        ykeys: ['MADHU_WEIGHT','VENU_WEIGHT'],
        labels: ['MADHU','VENU'],
        lineColors: ['#f39c12', '#3c8dbc'],
        hideHover: 'auto',
  	  gridIntegers: true,
        ymin: 0,
        xmin: 0
      });

    });
  </script>
