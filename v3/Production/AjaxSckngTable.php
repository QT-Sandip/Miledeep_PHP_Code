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


  $sql="SELECT SoakingDate,SUM(QTY) AS INQTY,
  SUM(APP_QT) AS OUTQTY FROM `soaking_report` WHERE  SoakingDate  
 BETWEEN '$date_start' AND '$date_end' GROUP BY SoakingDate ORDER BY SoakingDate DESC";
$StmtSoakng=$iconn->query($sql);

$content .= '<table id="example1" class="table table-bordered" >


			<thead>

                  <th align="CENTER">ID</th>
                  <th align="CENTER">Soaking Date</th>
				  <th align="CENTER">IN Quantity</th>
				  <th align="CENTER">OUT Quantity</th>
				  <th align="CENTER">PICK UP </th>
				  <th align="CENTER">Tools</th>
                </thead>
                <tbody>';

	$sno=1;

 
 while($RSoakng=$StmtSoakng->fetch_array()){
	 
	 $SoakingDate = getFormatedDate($RSoakng['SoakingDate']);

 
	$INQTY= $RSoakng['INQTY'];
	 $OUTQTY=$RSoakng['OUTQTY'];
	
	IF($OUTQTY==0){
		
	$YIELD=NA;	
	}ELSE{
	 
	 
	 $YIELD=round(($OUTQTY/$INQTY)*100,2);
	}

				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$sno.'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$SoakingDate.'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.round($INQTY,2).'</font></td>
				<td  align="CENTER" ><font size="'.$text.'">'.round($OUTQTY,2).'</font></td>
               
				<td  align="CENTER" ><font size="'.$text.'">'.$YIELD.'</font></td> 
                ';
	  $content.='<td align="CENTER" width="10%" >  
	  <a  target="_blank" class="btn btn-primary" href="SokingReportPrint.php?from_date='.$RSoakng['SoakingDate'] .'&to_date='.$RSoakng['SoakingDate'].'" 
	  >View</a>';
				

	  $content.=' </td>
		</tr>';
$sno++;
 }
	 
	
	$content.='</tbody></table>'	;

echo $content;





	?>
