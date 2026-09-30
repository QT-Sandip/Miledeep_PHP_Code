<?php
include "../includes/config.php";



 $date_start = $_POST['FROM_DATE'];
 $date_end = $_POST['TO_DATE'].' 23:59:59';
 $UNIT = $_POST['UNIT'];
 if($UNIT!=0){
 $UNIT_SQL = "and UNIT='$UNIT'";
 }else{
	$UNIT_SQL = ""; 
 }
     $sql_ABS="SELECT count(`LOT_NO`) as TOTAL,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='2'and `PURCHASE_DATE` 
BETWEEN '$date_start' AND '$date_end' $UNIT_SQL ) as ACCEPTED,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='1' and 
`PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL) as REJECTED,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='3' and 
`PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL) as HOLD,
(SELECT count(`STATUS`) FROM `inputmaterial_details` where `STATUS`='1' and 
`PURCHASE_DATE` BETWEEN '$date_start' AND '$date_end' $UNIT_SQL) as RETURN_L,
(SELECT count(`ANTI_BIOTIC_STATUS`) FROM `inputmaterial_details` where `ANTI_BIOTIC_STATUS`='0'and 
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
  
$content = '<div class="row">
       
		
		<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green"><img align="right"  src="../QualityControl/images/procure.png" /></span>

            <div class="info-box-content">
              <span class="info-box-text">Procured Lots</span>
              <span class="info-box-number">'.
			   $TOTAL .'
			  </span>
			   <a href="#" target="blank" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		
		 <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><img align="right"  src="../QualityControl/images/anti.png" /></span>

            <div class="info-box-content">
              <span class="info-box-text">Antibiotic Reports</span>
              <span class="info-box-number">'.$TOTAL_TESTING.'
			  </span>
			   <a href="#" target="blank" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		
		   <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><img align="right"  src="../QualityControl/images/rm_analysiss.png" /></i>
			 </span>
        
            <div class="info-box-content">
              <span class="info-box-text">Rm Analysis</span>
              <span class="info-box-number">'.$ORG_COUNT.'</span>
			  <a href="#" target="blank" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          
            </div>
			
            <!-- /.info-box-content -->
          </div>
		   
          <!-- /.info-box -->
        </div>
		
		 <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><img align="right"  src="../QualityControl/images/grade.png" /></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Grading Reports</span>
              <span class="info-box-number">'.$GRD_COUNT.'</span>
			  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
         
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
		

      </div>
	
	
	  <div class="box-body" id="ATT_DATA">
	
	
      <div class="row">
	  
	  
        <div class="col-md-12">
          <div class="box">
          
			
			
            <!-- /.box-header -->
            <div class="box-body">
             <!-- <div class="row">
                <div class="col-md-6">
                 
				  <p class="text-center">
                    <strong>Processing Details</strong>
								  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
         
                  </p>
				  
				  

                 <div class="progress-group">
                    <span class="progress-text">Grading PENDING</span>
                    <span class="progress-number"><b>'. $NO_GRADING.'</b>/'. $TOTAL .'</span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $NO_GRADING_PERC .'%;background: RED;"></div>
                    </div>
                   </div> 
				   
				   <div class="progress-group">
                    <span class="progress-text">Bills Pending</span>
                    <span class="progress-number"><b>'. $NO_BILLS.'</b>/'. $TOTAL .'</span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $NO_BILLS_PERC .'%;background: #00c0ef;"></div>
                    </div>
                   </div>
				   
				   <div class="progress-group">
                    <span class="progress-text">Costing Pending</span>
                    <span class="progress-number"><b>'. $NO_COSTING.'</b>/'. $TOTAL .'</span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $NO_COSTING_PERC .'%;background: #f39c12;"></div>
                    </div>
                   </div>
                   
				  
				  
                </div>-->
                <!-- /.col -->
                <div class="col-md-12">
                  <p class="text-center">
                    <strong>AB Reports</strong>
					<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
					</p>
				  
				  

                 <div class="progress-group">
                    <span class="progress-text">ACCEPTED</span>
                    <span class="progress-number"><b>'. $ACCEPTED.'</b>/'. $TOTAL .'</span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $ACCEPTED_PERC .'%;background: green;"></div>
                    </div>
                   </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">REJECTED</span>
                    <span class="progress-number"><b>'. $REJECTED .'</b>/'.$TOTAL.'</span>

                    <div class="progress sm">
                      <div class="progress-bar" style="width: '. $REJECTED_PERC .'%;background: red;"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  
                  <div class="progress-group">
                    <span class="progress-text">HOLD</span>
                    <span class="progress-number"><b>'. $HOLD .'</b>/'.$TOTAL.'  </span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $HOLD_PERC .'%;background: blue;"></div>
                    </div>
                  </div>
                <div class="progress-group">
                    <span class="progress-text">PENDING</span>
                    <span class="progress-number"><b>'. $PENDING .'</b>/'.  $TOTAL.'  </span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $PENDING_PERC .'% ;background: gray;"></div>
                    </div>
                  </div>
				  
				  <div class="progress-group">
                    <span class="progress-text">RETURN</span>
                    <span class="progress-number"><b>'. $RETURN .'</b>/'.  $TOTAL.'  </span>

                    <div class="progress sm">
                      <div class="progress-bar " style="width:'. $RETURN_PERC .'% ;background: purple;"></div>
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

	