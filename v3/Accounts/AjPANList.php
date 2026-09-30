<?php
session_start();

include "../includes/config.php";
include "../includes/Globals.php";
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];
$PAN=$_POST['PAN'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';
//ROBO
if($UNIT!=0) {
 $UNIT_SQL = "and im.UNIT='$UNIT' ";
 }else{
	$UNIT_SQL = "";
 }


 $accessType=$_SESSION['ACCESS_TYPE'];


 ECHO  $sql="SELECT 
im.PURCHASE_DATE,
 im.LOT_NO,
 im.UNIT_ID,
 im.AGENT_NAME,
 im.WEIGHT_IN_KGS AS F_WEIGHT,
 im.COUNT AS F_COUNT,
 im.BILL_NAME ,
 im.ANTI_BIOTIC_STATUS ,
 im.PURCHASE_TYPE,
 im.SUPERVISOR,
 im.BillPanNumber,
 RBD.COUNT AS C_COUNT,
 RBD.WEIGHT ,
 RBD.DEDUCTIONS ,
 RBD.REMARKS ,
 RBD.Tds ,
 RBD.RATE FROM 
`inputmaterial_details` im LEFT JOIN 
RM_BILL_DETAILS RBD on im.id = RBD.LOT_ID WHERE im.PURCHASE_DATE 
BETWEEN '$date_start' AND '$date_end' $UNIT_SQL";

//echo $sql;

$stmt=$iconn->query($sql);





$content .= '<table id="example1" class="table table-bordered" >


			<thead>

                  <th align="CENTER">ID</th>
                  <th align="CENTER">Lot</th>
				  <th align="CENTER">Purchase Date</th>				 
				  <th align="CENTER">Bill Name</th>
				  <th align="CENTER">Type</th>
				  <th align="CENTER">Weight</th>				 
				  <th align="CENTER">Sub total</th>				 
				  <th align="CENTER">TDS amunt</th>				 
				  <th align="CENTER">Grand total</th>				 
				  <th align="CENTER">Tools</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($row=$stmt->fetch_array()){


		if($row['ANTI_BIOTIC_STATUS']==0){

			$A_B_STATUS='TEST PENDING';
			$CLR='#666';
		}else if($row['ANTI_BIOTIC_STATUS']==1){

			$A_B_STATUS='REJECT';
			$CLR='#f00';
		}else if($row['ANTI_BIOTIC_STATUS']==2){

			$A_B_STATUS='ACCEPTED';
			$CLR='#00a65a';
		}else if($row['ANTI_BIOTIC_STATUS']==3){
			//$A_B_STATUS='BLUE';
			$A_B_STATUS='ON HOLD';
			$CLR='#04c';
		}

		if($row['RETURN_S']== 1){
			//$A_B_STATUS='RED';
			$A_B_STATUS='RETURN';
			$CLR='#FF7F50';
		}

$BILL_NAME = strlen($row['BILL_NAME']) > 15 ? substr($row['BILL_NAME'],0,15)."..." : $row['BILL_NAME'];
$PURCHASE_DATE = getFormatedDateDMMY($row['PURCHASE_DATE']);

if($row['SUBMISSION_DATE']!=''){
$SUBMISSION_DATE = getFormatedDateDMMY(substr($row['SUBMISSION_DATE'],0,10));
}else{
	$SUBMISSION_DATE ='';
}

if($row['C_S_DATE']!=''){
$C_S_DATE = getFormatedDateDMMY($row['C_S_DATE']);
}else{
$C_S_DATE ='';
}

$row['STATUS'] = 1;

				$content .= '<tr>

                <td  align="CENTER" width="2%"><font size="'.$text.'">'.$row['id'].'</font></td>
                <td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$row['LOT_NO'].'</B></font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$PURCHASE_DATE.'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$SUBMISSION_DATE.'</font></td>
                <!-- <td  align="CENTER" width="15%"><font size="'.$text.'">'.$C_S_DATE.'</font></td> -->
                <td  align="LEFT" width="12%"><font size="'.$text.'">'.$BILL_NAME.'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['PURCHASE_TYPE'].'</font></td>


                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['WEIGHT_IN_KGS'].'</font></td>

			   <td align="CENTER" width="7%">
			   <div class="bg-active color-palette" align="center" >
				<span class="label" align="center" style="background:'.$CLR.'">'. $A_B_STATUS.'</span>
				</div>
				</td>

				

		<td style="width:15%;"> ';


	  if($row['RETURN_S']== 1){
		$content.='  <a  href="../Accounts/ReturnBill.php?LOT_ID='. $row['id'].'"class="btn btn-warning">PRINT BILL</a>';

	 }

	}
	$content.='</tbody></table>'	;

echo $content;


	?>
