<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$QueryParms=$_GET['Q']; 

 if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  

  //$date=$_GET['date'];
   $Year=$_GET['Year'];
   $Month=$_GET['Month'];
   $StationID=$_GET['PsID'];
   $Type=$_GET['Type']; 



    $monthName = date("F", mktime(0, 0, 0, $Month, 10));

if($Type==1){
	  $Header='HL Yield Report-('.$monthName.')';

 }
 else{
 $Header='HL Yield Report-('.getFormatedDate($Month).' To '.getFormatedDate($Year).')';
	 
 }

if($Type==1){
			$Query= "month(VA.ProcessingDate)='$Month'  and year(VA.ProcessingDate)='$Year' ";
		 }else {
			$Query= "VA.ProcessingDate between '$Month'  and '$Year'"; 
		 }
		 
		 
 
  $content1 .= '
			  			  <h3 align="center">'.$Header.'</h3>';
//echo $content1;

$UnitName='';
 
     //$table_data=getP_TABLE($iconn,$Year,$Month,$UnitName,$hl_stmt);	  
     ///$table_data=getP_TABLE($iconn,$Year,$Month,$UnitName);	  
	  //echo $table_data['TABLE'];
	
  $PageTitle=$Header;
$PageTitleFolder="Reports";
  
  


$file_name='HL Yeild REPORT -'.$monthName.'.pdf';


//uma 09-0-2023//
$SqlCosting="SELECT * FROM SalesCostingRates WHERE TrackID= (SELECT MAX(TrackID) from SalesCostingRates)";
		$StmtCosting=$iconn->query($SqlCosting);

		while($RowCosting = $StmtCosting->fetch_assoc()){
			$ArrayCostingRates[]=$RowCosting;
		}
function getGradesBup(){
	global $iconn;
  global $text;
  global $text1;
  global $ArrayCostingRates;


	return $ArrayCostingRates;
}
function getGradesData($GradingBrkLotID){
  global $iconn;
  global  $text1;

			$GradQty=0;
			$GradCount=0;
			
			   $SqlGrdBrk="SELECT sum(HLWeight) as HLWeight,HLCount FROM `PpcHLGradingBreakup` 
			   WHERE LotID in ($GradingBrkLotID) GROUP by HLCount ORDER by HLCount ASC";


			$StmtGrdBrk=$iconn->query($SqlGrdBrk);
			while($RowBrk=$StmtGrdBrk->fetch_array()){
				
				$HLCount= $RowBrk['HLCount'];
				$HLWeight= $RowBrk['HLWeight'];
				$AryCounts[]= $HLCount;
				$ArySamples[]= $RowBrk;				
				$GradQty+=$HLWeight;
				$GradCount+=($HLWeight*$HLCount *2.2);				
			}
		



			$ary_lot_grade=array();
			$AryGrades=getGradesBup();


		
		
	foreach($AryGrades as $RowCosting){
				
				 $LowLimit=$RowCosting['LowerLimit'];
			 $UpLimit=$RowCosting['UpperLimit'];
			
				

		foreach($ArySamples  as $RowGrading){
			  $CheckCount=$RowGrading['HLCount'];
			
			if($CheckCount >= $LowLimit  && $CheckCount <= $UpLimit){
			 $HLWeight=$RowGrading['HLWeight'];
			 $HLCount=$RowGrading['HLCount'];
			   $CheckCount;
			  break;
			}else{
			 $HLWeight='0';
			}
		}

		//$content.='<td  align="CENTER" >'. $HLWeight.'</font></td>';

		$sample['GRADE']=$RowCosting['Grade'];
		$sample['WEIGHT']=$HLWeight;
		$sample['COUNT']=$HLCount;
		$ary_lot_grade[]=$sample;
	}

		$ArrayDayGrades[]=$ary_lot_grade;

		$Data['GradCount']=$GradCount;
		$Data['GradQty']=$GradQty;
		$Data['Trows']=$content;
		$Data['TotalData']=$ArrayDayGrades;

return $Data;
}

function getMixedData($IDS,$RowLotsData){
global $iconn;

   $sql_LOTS="SELECT
GROUP_CONCAT(LotNumber SEPARATOR ', ') as LotNumber,
 GROUP_CONCAT(PurchaseCount SEPARATOR ', ') as PurchaseCount,
 AVG(PurchaseCount) as RmAvgCount,
 sum(round(PurchaseWeight,2)) as PurchaseWeight,
(select sum(round(WtAfterDH,2)) from PpcDeheading WHERE  LotID in ($IDS)) as WAVA,
 (select GROUP_CONCAT(CountAfterDH  SEPARATOR ', ')  from PpcDeheading WHERE  LotID in ($IDS)) as CAVA
 from `RmLotDetatils`
WHERE RmLotID in ($IDS)";


$stmt_LOTS=$iconn->query($sql_LOTS);
$ROW_LOTS = $stmt_LOTS->fetch_array();

$LotNumbers=$ROW_LOTS['LotNumber'];
$PurchaseCount=$ROW_LOTS['PurchaseCount'];
$LotWeight=$ROW_LOTS['PurchaseWeight'];
$LotNumbers1=$ROW_LOTS['LotNumber'];
$RowLotsData['WAVA']=$ROW_LOTS['WAVA'];
 $RowLotsData['CAVA']=$ROW_LOTS['CAVA'];
$RowLotsData['RmAvgCount']=$ROW_LOTS['RmAvgCount'];
$RowLotsData['LotNumber']=$LotNumbers;
$RowLotsData['PurchaseCount']=$PurchaseCount;
$RowLotsData['PurchaseWeight']=$LotWeight;
return $RowLotsData;
}


 

  $SqlLotsData="SELECT IM.PurchaseDate,IM.PurchaseStationID,IM.NetWeight as CountNetQty,IM.LotNumber,
IM.AgentID,IM.GraderID,IM.ProductionWeight, IM.PurchaseWeight,IM.PurchaseCount,IM.SupevisorID,IM.RmLotID,
 
 PHYD.`LotID`, PHYD.`BillCount`, PHYD.`CheckingCount1`, PHYD.`CheckingCount2`,
 PHYD.`Batch1`,PHYD.`Batch2`,PHYD.`Batch3`,PHYD.`Batch4`,
 PHYD.`Batch5`,PHYD.Batch6,HW.IsMix, HW.MixIDs,RUG.UserName as GraderName,RUA.UserName as AgentName
FROM `RmLotDetatils` AS IM
left join PpcDeheading as VA on IM.RmLotID=VA.LotID
left join PpcHLYeildData as PHYD ON PHYD.LotID=IM.RmLotID
left join PpcHLGradingDetails as HW ON HW.LotID=IM.RmLotID 
left join RmUsersList as RUG ON RUG.ID=IM.GraderID 
left join RmUsersList as RUA ON RUA.ID=IM.AgentID 

where  $Query group by  VA.LotID order by PurchaseDate ASC,LotNumber ASC";
$StmtLotsData=$iconn->query($SqlLotsData);
//$RowLotsData = $StmtLotsData->fetch_array();


?>

<!doctype html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />     


		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
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
						
                      

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="showDataTable">
									<table  id="scroll-vertical-datatable"  data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
                                     

	<tr>

                <th> <B>Deheading Date </B></th>
                <th> <B>RmDate </B></th>
                <th> <B>Unit</B></th>
                <th> <B>LotNumber</B></th>
                <th> <B>AgentName</B></th>
                <th> <B>GraderName</B></th>
                <th> <B>Purchase Weight</B></th>
              <!--  <th> <B>Production Weight</B></th> -->
                <th> <B>BillCount</B></th>
				<th> <B>CHECKING COUNT-1</B></th>
				<th> <B>CHECKING COUNT-2</B></th> 
				<th> <B>HL.QTY</B></th>
				<th> <B>Grading QTY</B></th>
				<th> <B>HL YIELD</B></th>
				<th> <B>CountNetYield </B></th>  
				<th> <B>Count Net Qty(Kgs) </B></th>  
				<th> <B>StandardYield </B></th>  
			    <th> <B>(+) OR (-) Kgs/Count NetYield%</B></th>
			    <th> <B>Supervisor</B></th>
			    <th> <B>PHONE NO</B></th>
			    <th> <B>BATCH1</B></th>
			    <th> <B>BATCH2</B></th>
			    <th> <B>BATCH3</B></th>
			    <th> <B>BATCH4</B></th>
			    <th> <B>BATCH5</B></th>
			    <th> <B>BATCH6</B></th>
               </tr>

			 

	<?php	while($RowLotsData = $StmtLotsData->fetch_array()){
		

//while($RowLotsData=$hl_stmt->fetch_array()){
		
			$RmLotID=$RowLotsData['RmLotID'];
			
			$SqlDeheadingData=" Select  VA.LotID,VA.WtAfterDH,VA.CountAfterDH,VA.NetCount,
 VA.ProcessingDate,VA.CheckingCount,VA.StationID, RUS.UserName as SupevisorName
 from PpcDeheading as VA 
left join RmUsersList as RUS ON RUS.ID=VA.SupervisorID where LotID='$RmLotID'";
//left join RmUsersList as RUS ON RUS.ID=VA.SupervisorID where LotID='24807'";

$StmtDeheadingData=$iconn->query($SqlDeheadingData);

$Count=0;
$HLQty=0;
$CheckingCount2=0;
$CheckingCount2Avg=0;
$NetCountAvg=0;
$HLCountAvg=0;
$NetCount=0;
$HLCount=0;
while($RowDeheadingData=$StmtDeheadingData->fetch_array())
{
	
	 $StationID=$RowDeheadingData['StationID'];
	 $Supevisor[]=$RowDeheadingData['SupevisorName'];
	 $SupevisorName=implode(',',$Supevisor);
	 $ProcessingDate=date("d-m-Y", strtotime($RowDeheadingData['ProcessingDate']));


	 $HLQty +=$RowDeheadingData['WtAfterDH'];


$SqlProcessingStns="SELECT * FROM `PpcVAStations` where StationID=$StationID";
$StmtProcessingStns=$iconn->query($SqlProcessingStns);
$RowProcessingStns= $StmtProcessingStns->fetch_array();
$ProcessingStation[]=$RowProcessingStns['UnitName'];
$DHNetCount=$RowDeheadingData['NetCount'];
$PrcsngStnName=implode(',',$ProcessingStation);	
if($DHNetCount!=''  && $DHNetCount!='NILL'){
  $Count +=1; 
}
 $CheckingCount2 += $RowDeheadingData['CheckingCount'];
 $NetCount += $RowDeheadingData['NetCount'];
 $HLCount +=$RowDeheadingData['CountAfterDH'];



}
 $CheckingCount2Avg=$CheckingCount2/$Count; 
$NetCountAvg=$NetCount/$Count;
$HLCountAvg=$HLCount/$Count;
//echo $HLCountAvg ;exit;

unset($ProcessingStation);
unset($Supevisor);


		 $GradingBrkLotID=$RowLotsData['RmLotID'];
		 $IsMix=$RowLotsData['IsMix'];
		 $MixIDs=$RowLotsData['MixIDs'];




	 
$GradData=getGradesData($GradingBrkLotID);


	if($IsMix ==1){
	$GradQty='<b style="color: red;">Mixed </b>';
	//$GradQty='<b style="color: red;>Mixed </b>';
	 }else{
	$GradQty=$GradData['GradQty'];	 
	 }

//}
$PurchaseDate=date("d-m-Y", strtotime($RowLotsData['PurchaseDate']));
$StationName=$RowLotsData['PurchaseStationID'];
$LotNumber=$RowLotsData['LotNumber'];
$AgentName=$RowLotsData['AgentName'];
$GraderName=$RowLotsData['GraderName'];
$PurchaseWeight=$RowLotsData['ProductionWeight'];  // Alter Entry At Lot Creation
$ProductionWeight=$RowLotsData['PurchaseWeight'];
$HoNQty=$RowLotsData['PurchaseWeight'];

$BillCount=$RowLotsData['PurchaseCount'];  //# Ramesh Info On SME
$CheckingCount1=$RowLotsData['CheckingCount1'];#no need to calculate average because it is single entry in PpcHLYeildData #uma

 $CountNetQty=$RowLotsData['CountNetQty'];
 //$HeadOnQtyEntered=$RowLotsData['HeadOnQtyEntered'];
$Batch1=$RowLotsData['Batch1'];
$Batch2=$RowLotsData['Batch2'];
$Batch3=$RowLotsData['Batch3'];
$Batch4=$RowLotsData['Batch4'];
$Batch5=$RowLotsData['Batch5'];
$Batch6=$RowLotsData['Batch6'];	
//$HLYeild=ROUND((($HLQty/$ProductionWeight)*100),2).'%';  //ProductionWeight here it holds purchaseweight value
$HLYeild=ROUND((($HLQty/$PurchaseWeight)*100),2).'%';  //ProductionWeight here it holds purchaseweight value

//IF($HLCount!=''){
//IF($HLCountAvg!=''){
IF($BillCount!=''){
 //$SqlYeildsList="SELECT * FROM `RmStandardYields` WHERE Type='HL' AND $HLCount BETWEEN LowerCount AND UpperCount ";
 $SqlYeildsList="SELECT * FROM `RmStandardYields` WHERE Type='HL' AND $BillCount BETWEEN LowerCount AND UpperCount ";
$StmtYeildsList=$iconn->query($SqlYeildsList);
$RowYeildsList = $StmtYeildsList->fetch_array();
}

$SqlPurchaseStations="SELECT * FROM `RmPurchaseStations` where PurchaseStationID=$StationName";
$StmtPurchaseStations=$iconn->query($SqlPurchaseStations);
$RowPurchaseStations= $StmtPurchaseStations->fetch_array();




$StationName=$RowPurchaseStations['StationName'];

$StandardYeild=$RowYeildsList['Yield'];
 
//$Cal1=$NetCount/100;
$Cal1=$NetCountAvg/100;

//$CountNetYeiild=round((($HLQty/$Cal1)-$PurchaseWeight),2);
//$CountNetYeiild=round((($HLQty/$Cal1)-$ProductionWeight),2);//ProductionWeight here it holds purchaseweight value
$CountNetYeiild=round((($HLQty/$Cal1)-$PurchaseWeight),2);//ProductionWeight here it holds purchaseweight value

?>
<tr>
			 
			<td><?php echo $ProcessingDate ?></td>
			<td><?php echo $PurchaseDate ?></td>
			<td><?php echo $PrcsngStnName ?></td>
			<td><?php echo $LotNumber ?></td>
			<td><?php echo $AgentName ?></td>
			<td><?php echo $GraderName ?></td>
			<td><?php echo $PurchaseWeight ?></td>
			<td><?php echo $BillCount ?></td>
			<td><?php echo $CheckingCount1 ?></td>
			<td><?php echo number_format($CheckingCount2Avg,2) ?></td>
			<td><?php echo $HLQty ?></td>
			<td><?php echo $GradQty ?></td>
			<td><?php echo $HLYeild ?></td>
			<td><?php echo $NetCountAvg.'%' ?></td>
			<td><?php echo $CountNetQty ?></td>
			<td><?php echo $StandardYeild.'%' ?></td>
			<td><?php echo $CountNetYeiild.'%' ?></td>
			<td><?php echo $SupevisorName.'%' ?></td>
			<td><?php echo $Batch1 ?></td>
			<td><?php echo $Batch2 ?></td>
			<td><?php echo $Batch3 ?></td>
			<td><?php echo $Batch4 ?></td>
			<td><?php echo $Batch5 ?></td>
			<td><?php echo $Batch6 ?></td>
			
			
			
								
	
	 </tr>

 <?php $S_NO++;
}
?>



		</table>
  
                                        

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
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
</html>

<script>
$('document').ready(function(){
		showData();



    	
    });



</script>

<script>

			
					function showData(data){
			
					$("#showDataTable").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        scrollY: "600px",
        scrollX:true,
        sScrollXInner: "150%", 
        paging:true,
        destroy: true,
        fixedColumns: true,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel',  'colvis']// 'pdf',
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}


</script>
