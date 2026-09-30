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


 $accessType=$_SESSION['ACCESS_TYPE'];

 $sql="SELECT COALESCE(SUM(CARTONS_NUMBER),0) AS MASTERS,DATE_TIME,
(SELECT COALESCE(SUM(CARTONS_NUMBER),0) FROM
 `CS_PALLET_INPUT_DATA` WHERE DUMMY_MASTER ='DUMMY' 
 AND DATE_TIME=csi.DATE_TIME) AS DUMMY FROM
 `CS_PALLET_INPUT_DATA` AS csi WHERE 
 csi.DUMMY_MASTER='MASTER' AND csi.DATE_TIME 
 BETWEEN '$date_start' AND '$date_end' 
 GROUP BY csi.DATE_TIME ORDER BY DATE_TIME DESC";
  
$StmtSoakng=$iconn->query($sql);

$content .= '<table id="example1" class="table table-bordered" >


			<thead>

                  <th align="CENTER">ID</th>
                  <th align="CENTER">IQF Date</th>
				  <th align="CENTER">Master Cartons</th>
				  <th align="CENTER">Dummy Cartons</th>
				  <th align="CENTER">Total Cartons</th>
				  
				  <th align="CENTER">Tools</th>
                </thead>
                <tbody>';

	$sno=1;

 
 while($RSoakng=$StmtSoakng->fetch_array()){
	 
	 $IQFDate= getFormatedDate($RSoakng['DATE_TIME']);

 
	$MASTER_CARTONS= $RSoakng['MASTERS'];
	 $DUMMY_CARTONS=$RSoakng['DUMMY'];
	$TOTAL_CARTONS=$MASTER_CARTONS+$DUMMY_CARTONS;
	IF($OUTQTY==0){
		
	$YIELD=NA;	
	}ELSE{
	 
	 
	 $YIELD=round(($OUTQTY/$INQTY)*100,2);
	}

				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$IQFDate.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.$MASTER_CARTONS.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$DUMMY_CARTONS.'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.$TOTAL_CARTONS.'</font></td>
               
				 
                ';
	  $content.='<td align="CENTER" width="10%" >  

		<a target="_blank" href="/Production/AjaxIQFTable.php?from_date='.$row['PURCHASE_DATE'] .'&to_date='.$row['PURCHASE_DATE'].'"
                 class="btn btn-primary" >View</button></td>
		

	  $content.=' </td>
		</tr>';
$sno++;
 }
	 
	
	$content.='</tbody></table>'	;

echo $content;





	?>
