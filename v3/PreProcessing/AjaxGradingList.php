<?php
session_start();

include "../includes/config.php";
include "../includes/Globals.php";
/*$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];*/
$FROM_DATE=$_POST['FROM_DATE']; 
$TO_DATE=$_POST['TO_DATE'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];
/*$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';*/

$showAddGrading=false;
$showAddBill=false;
$showOptions=false;
$showAddCosting=false;
$showViewGrading=false;
$showEditGrading=false;
$showQuantity=false;



 if($_SESSION['D_ID']== 19){
	 $showOptions=true;
//	$showAddBill=true;
  $showViewGrading=true;
 }

  if($_SESSION['D_ID']== 18){
	 $showViewGrading=true;
  }

 if($_SESSION['D_ID']== 8){
	 	 $showAddGrading=true;

	 	 $showAddCosting=true;

	  if($_SESSION['ACCESS_TYPE']==1){
		 $showAddBill=true;
		 $showEditGrading=true;
		 $showQuantity=true;
	 }

 }

 if($_SESSION['D_ID']== 4){
	 $showAddGrading=true;
	// echo "hii";
	/* $showAddCosting=true;
	 if($_SESSION['ACCESS_TYPE']==1){
		 $showAddBill=true;
		 $showEditGrading=true;
		 $showQuantity=true;
	 }*/
 }


 if($_SESSION['ACCESS_TYPE']==1){
		 $showQuantity=true;
	 }

 //echo $showAddCosting;



 $accessType=$_SESSION['ACCESS_TYPE'];




 $sql="SELECT * from inputmaterial_details
 LEFT join PPC_GRADING_HL on inputmaterial_details.id=PPC_GRADING_HL.LOT_ID
	where PPC_GRADING_HL.DATA_TYPE='Headless Grading' and PURCHASE_DATE
	BETWEEN '$FROM_DATE' AND '$TO_DATE'";

if($TYPE=='B'){
 	$sql="SELECT * from inputmaterial_details
	LEFT join PPC_GRADING_HL on inputmaterial_details.id=PPC_GRADING_HL.LOT_ID
	where PPC_GRADING_HL.DATA_TYPE='Headless Grading' and PURCHASE_DATE
	BETWEEN '$FROM_DATE' AND '$TO_DATE' and NOT EXISTS
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
  where IMD.UNIT='$UNIT' and IMD.PURCHASE_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE'";

//echo $sql; exit;

$stmt=$iconn->query($sql);





$content .= '<table id="example1" class="table table-bordered" >


			<thead>

                  <th align="CENTER">Id</th>
                  <th align="CENTER">Lot</th>
				  <th align="CENTER">Purchase Date</th>
				  <th align="CENTER">Grading Date</th>
			<!--	  <th align="CENTER">Costing Date</th> -->
				  <th align="CENTER">Type</th>';
				  if($showQuantity){
				 $content .= ' <th align="CENTER">Weight</th>';
				  }
                 $content .= ' <th align="CENTER">AB Status</th>
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
			$CLR='#f00';
		}

$PURCHASE_DATE = getFormatedDate($row['PURCHASE_DATE']);

if($row['SUBMISSION_DATE']!=''){
$SUBMISSION_DATE = getFormatedDateTime($row['SUBMISSION_DATE']);
}else{
	$SUBMISSION_DATE ='';
}

if($row['C_S_DATE']!=''){
$C_S_DATE = getFormatedDateTime($row['C_S_DATE']);
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
                <!--  <td  align="CENTER" width="10%"><font size="'.$text.'">'.$C_S_DATE.'</font></td>  -->
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['PURCHASE_TYPE'].'</font></td>';

if($showQuantity){
                $content .= '<td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['WEIGHT_IN_KGS'].'</font></td>';
}
			  $content .= ' <td align="CENTER" width="10%">
			   <div class="bg-active color-palette" align="center" >
				<span class="label" align="center" style="background:'.$CLR.'">'. $A_B_STATUS.'</span>
				</div>
				</td>

				<td align="CENTER" width="10%">
			   <div class="bg-active color-palette" align="center" >
				<span class="label" align="center" style="background:'.$STATUS_CLR.'">'. $STATUS.'</span>
				</div>
				</td>

		<td style="width:20%;"> ';


	  if($row['RETURN_S']== 1){
		$content.='  <a  href="../Accounts/ReturnBill.php?LOT_ID='. $row['id'].'"class="btn btn-warning">PRINT BILL</a>';
	 }


	  if($row['GD_AVAL']== '' && $showAddGrading){  //GRADING REPORT NOT SUBMITTED
		$content.='


		<div><input type="checkbox" id="chbk" value="'.$row['id'].'" ><span style="color: white";>= =</span>

		<a
		class="btn btn-info" id='.$row['id'].'
		href="../PreProcessing/AddHLGrdng.php?ID='.$row['id'].'">Add HL Grading </a>

		</div>';



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
	 $content.=' <a  target="_blank" href="../Accounts/EditGradingReport.php?id='. $row['id'].'"class="btn btn-danger">Edit Grading</a>';
	}

	}else if($row['BILL_AVAL']== '' && !$showAddBill){ //Bill Pending
		// $content.='  <a  href="#"class="btn btn-primary">BILL Pending</a>';
		//$content.=' <a  target="_blank" href="../Accounts/EditGradingReport.php?id='. $row['id'].'"class="btn btn-danger">Edit Grading</a>';
if($showQuantity){
 $content.=' <a  target="_blank" href="../Accounts/BillPreview.php?id='. $row['id'].'"class="btn btn-success">Preview</a>';
}
$content.=' <a  target="_blank" href="../PreProcessing/AddHLGrdng.php?ID='. $row['id'].'"class="btn btn-info">Edit Grading</a>';

	 }else {


		//  $content.='  <a  target="_blank" href="EditRmBill.php?id='. $row['id'].'"class="btn btn-danger">Edit Bill</a>';

	  if($row['COSTING_AVAL'] == 0 ){  //COSTING Pending

	   if($showAddCosting){
	   if($showQuantity){


	  			 $content.='  <a  target="_blank" href="CostingPreview.php?id='. $row['id'].'"class="btn btn-info">Preview</a>';
	   }
		$content.='  <a  target="_blank" href="EditRmBill.php?id='. $row['id'].'"class="btn btn-danger">Edit Bill</a>';
	  }else{
		  $content.='  <a  href="#"class="btn btn-warning">COSTING PENDING</a>';
	  }
	   }else {

       //$content.=' <a  target="_blank" href="../Accounts/AuthGradingReport.php?id='. $row['id'].'"class="btn btn-success">View Grading</a>';
      $content.=' <a
       class="btn btn-info" id='.$row['id'].'
       href="../PreProcessing/AddHLGrdng.php?ID='.$row['id'].'">Add HL Grading </a>';
		 $content.='<a  target="_blank" href="../Accounts/DocWeightSlipPrint.php?LOT_ID='. $row['id'].' "class="btn btn-warning">View</a> ';
		 if($showAddCosting){
			   $content.='  <a  target="_blank" href="EditRmBill.php?id='. $row['id'].'"class="btn btn-danger">Edit Bill</a>';
				$content.='<a  target="_blank" href="EditCostingReport.php?id='. $row['id'].' "class="btn btn-danger">Edit Costing</a>';
			}

	   }

	  $content.=' </td>
		</tr>';

	 }
	}
	$content.='</tbody></table>'	;

echo $content;





	?>
