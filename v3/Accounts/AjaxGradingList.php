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
//ROBO
if($UNIT!=0){
 $UNIT_SQL = "and IMD.UNIT='$UNIT'";
 }else{
	$UNIT_SQL = "";
 }
 

$showAddBill=false;
$showOptions=false;
$showAddCosting=false;
$showViewGrading=false;
$showEditGrading=false;
$BillWipeOff=false;



 if($_SESSION['D_ID']== 3){
	 $showOptions=true;
	$showAddBill=true;
 }

  if($_SESSION['D_ID']== 5){
	 $showViewGrading=true;
  }


IF($_SESSION['ACCESS_TYPE']==1){
	
	$BillWipeOff=true;
}

 if($_SESSION['D_ID']== 8){

	// echo "hii";
	 $showAddCosting=true;
	 if($_SESSION['ACCESS_TYPE']==1){
		 $showAddBill=true;
		 $showEditGrading=true;
	 }
 }

 //echo $showAddCosting;



 $accessType=$_SESSION['ACCESS_TYPE'];




 $sql="SELECT * from inputmaterial_details
 LEFT join PPC_GRADING_HL on inputmaterial_details.id=PPC_GRADING_HL.LOT_ID
	where PPC_GRADING_HL.DATA_TYPE='Headless Grading' and PURCHASE_DATE
	BETWEEN '$date_start' AND '$date_end'";

if($TYPE=='B'){
 	$sql="SELECT * from inputmaterial_details
	LEFT join PPC_GRADING_HL on inputmaterial_details.id=PPC_GRADING_HL.LOT_ID
	where PPC_GRADING_HL.DATA_TYPE='Headless Grading' and PURCHASE_DATE
	BETWEEN '$date_start' AND '$date_end' and NOT EXISTS
        (
        SELECT LOT_ID
        FROM    PURCHASE_DETAILS d
        WHERE   d.LOT_ID = PPC_GRADING_HL.LOT_ID
        )";
}else{


}

//where GD.DATA_TYPE='Headless Grading'
   $sql="SELECT
   IMD.id,
   IMD.LOT_NO,
   IMD.PURCHASE_DATE,
   IMD.SUPERVISOR,
   IMD.STATUS AS RETURN_S,
   IMD.PURCHASE_TYPE,
   IMD.WEIGHT_IN_KGS,
   IMD.ANTI_BIOTIC_STATUS,
   IMD.RM_ANA_STATUS,
   IMD.BILL_NAME,
   GD.SUBMISSION_DATE,
   GD.LOT_ID as GD_AVAL,
   GD.STATUS,
  RMBD.LOT_ID AS BILL_AVAL,
  RMCD.LOT_ID AS COSTING_AVAL,
  RMCD.SUBMISSION_DATE C_S_DATE
  from inputmaterial_details as IMD
  LEFT join PPC_GRADING_HL as GD on IMD.id=GD.LOT_ID
  LEFT join RM_BILL_DETAILS as RMBD on IMD.id=RMBD.LOT_ID
  LEFT JOIN RM_COSTING_DETAILS as RMCD on IMD.id=RMCD.LOT_ID
  where  IMD.PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' $UNIT_SQL";

//echo $sql;

$stmt=$iconn->query($sql);





$content .= '<table id="example1" class="table table-bordered" >


			<thead>

          <th align="CENTER">ID</th>
          <th align="CENTER">Lot</th>
				  <th align="CENTER">Purchase Date</th>
				  <th align="CENTER">Grading Date</th>
				  <th align="CENTER">Bill Name</th>
				  <th align="CENTER">Type</th>
				  <th align="CENTER">Weight</th>
          <th align="CENTER">AB Status</th>
				  <th align="CENTER">Status</th>
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

		if($row['GD_AVAL']== ''){
			$STATUS='Grading Not Aval';
			$STATUS_CLR='#f00';
		}else if($row['STATUS'] == 0){
			$STATUS='Grading Not Authorize';
			$STATUS_CLR='#f00';
		}else if($row['RM_ANA_STATUS'] == 0){
			$STATUS='Rm Report Not Aval';
			$STATUS_CLR='#f00';
		}else if($row['BILL_AVAL']== ''){
			$STATUS='Bill Not Aval';
			$STATUS_CLR='#f00';

		}else{
		 if($row['COSTING_AVAL'] == ''){
			$STATUS='Costing Not Aval';
			$STATUS_CLR='#f00';
		}else {
			$STATUS='All Clear';
			$STATUS_CLR='Green';
		 }
		}

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

				<td align="CENTER" width="7%">
			   <div class="bg-active color-palette" align="center" >
				<span class="label" align="center" style="background:'.$STATUS_CLR.'">'. $STATUS.'</span>
				</div>
				</td>

		<td style="width:15%;"> ';


	  if($row['RETURN_S']== 1){
		$content.='  <a  href="../Accounts/ReturnBill.php?LOT_ID='. $row['id'].'"class="btn btn-warning">PRINT BILL</a>';

	 }


	  if($row['GD_AVAL']== ''){  //GRADING REPORT NOT SUBMITTED
		//$content.='  <a  href="#"class="btn btn-danger">Grading Pending</a>';

	 }else if($row['STATUS']== '0'){  //GRADING REPORT NOT Authorize

	 if($showViewGrading){
	  $content.=' <a  target="_blank" href="../Accounts/AuthGradingReport.php?id='. $row['id'].'"class="btn btn-success">View Grading</a>';
	 }else{
		//$content.='  <a  href="#"class="btn btn-danger">Authorization Pending</a>';
	 }


	 }else if($row['BILL_AVAL']== '' && $showAddBill){ //Bill Pending

	 if($row['RM_ANA_STATUS'] != 0){
	  $content.=' <a  target="_blank" href="../Accounts/BillPreview.php?id='. $row['id'].'"class="btn btn-success">Add Bill</a>';
	 }
if($showEditGrading){
	//uma $content.=' <a  target="_blank" href="../Accounts/EditGradingReport.php?id='. $row['id'].'"class="btn btn-danger">Edit Grading </a>';
}
	}else if($row['BILL_AVAL']== '' && !$showAddBill){ //Bill Pending
		 $content.='  <a  href="#"class="btn btn-primary">BILL Pending</a>';
	 }else {


		//  $content.='  <a  target="_blank" href="EditRmBill.php?id='. $row['id'].'"class="btn btn-danger">Edit Bill</a>';

	  if($row['COSTING_AVAL'] == 0 ){  //COSTING Pending

	   if($showAddCosting){


	  	  $content.='  <a  target="_blank" href="CostingPreview.php?id='. $row['id'].'"class="btn btn-info">Preview</a>';
		  //uma$content.='  <a  target="_blank" href="EditRmBill.php?id='. $row['id'].'"class="btn btn-danger">Edit Bill</a>';
	  }else{
		  $content.='  <a  href="#"class="btn btn-warning">COSTING PENDING</a>';
	  }
	   }else {

		 $content.='<a  target="_blank" href="BillReports.php?id='. $row['id'].' "class="btn btn-primary">View</a> ';
		
IF($BillWipeOff){
		$content.='<a  target="_blank" href="BillWipeOFf.php?id='. $row['id'].' "class="btn btn-primary">Final Changes</a> ';
		 
}

if($showAddCosting){
      /*   $content.='<a  target="_blank" href="EditCostingReport.php?id='. $row['id'].' "class="btn btn-danger">Edit Costing</a>';
			   $content.='  <a  target="_blank" href="EditRmBill.php?id='. $row['id'].'&T=D"class="btn btn-danger">Edit Bill</a>'; */

         $content.='
     	  <div class="btn-group">
                       <button type="button" class="btn btn-info">Options</button>
                       <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                         <span class="caret"></span>
                         <span class="sr-only">Toggle Dropdown</span>
                       </button>
                       <ul class="dropdown-menu" role="menu">
                        <!--uma <li><a style="color:white" target="_blank" href="EditRmBill.php?id='. $row['id'].'&T=E" class="btn btn-info">Edit Bill</a></li>
                         <li><a style="color:white" target="_blank" href="EditCostingReport.php?id='. $row['id'].' " class="btn btn-primary">Edit Costing</a></li>-->
                         <li><a style="color:white" target="_blank" href="RmBillDelete.php?id='. $row['id'].'&T=D" class="btn btn-danger">Delete Bill</a></li>


                       </ul>
                     </div>';
				}

	   }

	  $content.=' </td>
		</tr>';

	 }
	}
	$content.='</tbody></table>'	;

echo $content;





	?>
