<?php
session_start();

include "../includes/config.php";
include "../includes/Globals.php";
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';
$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];
 
if($_GET['M']!=''){

$MONTH=$_GET['M'];
$YEAR=$_GET['YEAR'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';
}


 $accessType=$_SESSION['ACCESS_TYPE'];

   $sql="SELECT COALESCE(SUM(CARTONS_NUMBER),0) AS MASTERS,DATE_TIME,
(SELECT COALESCE(SUM(CARTONS_NUMBER),0) FROM
 `CS_PALLET_INPUT_DATA` WHERE DUMMY_MASTER ='DUMMY'  AND DATE_TIME=csi.DATE_TIME) AS DUMMY,

(SELECT round(COALESCE(SUM(NET_WT),0),2) FROM `CS_PALLET_INPUT_DATA` 
where DATE_TIME=csi.DATE_TIME and TYPE='BLOCK') AS BlockNetWeight,
(SELECT round(COALESCE(SUM(NET_WT),0),2) FROM `CS_PALLET_INPUT_DATA` 
where DATE_TIME=csi.DATE_TIME and TYPE='IQF') AS IQFNetWeight,
(SELECT round(COALESCE(SUM(NET_WT),0),2) FROM `CS_PALLET_INPUT_DATA` 
where DATE_TIME=csi.DATE_TIME ) AS TotalNetWeight

 FROM
 `CS_PALLET_INPUT_DATA` AS csi WHERE 
 csi.DUMMY_MASTER='MASTER' AND csi.DATE_TIME 
 BETWEEN '$date_start' AND '$date_end' 
 GROUP BY csi.DATE_TIME ORDER BY DATE_TIME DESC";
 
$StmtSoakng=$iconn->query($sql);

		


$content .= '<table id="example1" border=1 cellspacing="0" cellpadding="3" class="table table-bordered" >


			<thead>

                  <th align="CENTER">ID</th>
                  <th align="CENTER">IQF Date</th>
				  <th align="CENTER">Master Cartons</th>
				  <th align="CENTER">Dummy Cartons</th>
				  <th align="CENTER">Total Cartons</th>
				  <th align="CENTER">Block</th>
				  <th align="CENTER">Iqf</th>
				  <th align="CENTER">Total Weight</th>';
				  if($_GET['M']==''){
				 $content .= ' <th align="CENTER">Tools</th>';
				  }
                $content .= '</thead>
                <tbody>';

	$sno=1;

 
 while($RSoakng=$StmtSoakng->fetch_array()){
	 
	 $IQFDate= getFormatedDate($RSoakng['DATE_TIME']);

 
	$MASTER_CARTONS= $RSoakng['MASTERS'];
	 $DUMMY_CARTONS=$RSoakng['DUMMY'];
	 
	 $BlockNetWeight=$RSoakng['BlockNetWeight'];
	 $IQFNetWeight=$RSoakng['IQFNetWeight'];
	 $TotalNetWeight=$RSoakng['TotalNetWeight'];
	$TOTAL_CARTONS=$MASTER_CARTONS+$DUMMY_CARTONS;
	IF($OUTQTY==0){
		
	$YIELD=NA;	
	}ELSE{
	 
	 
	 
	 $YIELD=round(($OUTQTY/$INQTY)*100,2);
	}
	
	// $RLOTS=$stmt_lots->fetch_array();
$TOTAL_WEIGHT=$RLOTS['WEIGHT'];

				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$IQFDate.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.$MASTER_CARTONS.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$DUMMY_CARTONS.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$TOTAL_CARTONS.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$BlockNetWeight.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$IQFNetWeight.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$TotalNetWeight.'</font></td>
               
				 
                ';
				if($_GET['M']==''){
				$content.='<td align="CENTER" width="10%" >  

		<a target="_blank" href="/Production/BlockReportPrint.php?from_date='.$RSoakng['DATE_TIME'] .'&to_date='.$RSoakng['DATE_TIME'].'"
                 class="btn btn-primary" >View</button></td>';
				  }
	  
		

	  
		$content.='</tr>';
$sno++;
 }
	 
	
	$content.='</tbody></table>'	;

echo $content;





	?>
