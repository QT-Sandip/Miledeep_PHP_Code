<?php
session_start();
include "../includes/config.php";
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];
$TYPE=$_POST['TYPE'];
$btn_id=$_POST['ID'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31 23:59:59';

$TYPE=$_POST['TO_DATE'];

$date_end=$_POST['TO_DATE'].' 23:59:59';
$date_start=$_POST['FROM_DATE'] ;
$COUNT=$_POST['COUNT'] ;

 $showQuantity=false;

 if($_SESSION['D_ID'] == 3  && $_SESSION['ACCESS_TYPE'] <= 3){
		 $showQuantity=true;
	 }


 if($_SESSION['D_ID']== 1){
	 $showOptions=true;
 }else{
	 $showOptions=false;
 }

 $accessType=$_SESSION['ACCESS_TYPE'];

   if($TYPE=='RETURN'){
	  $LOT_ID=$_POST['LOT_ID'];
	 $oldLotData = legacyAuditFetchRowById('inputmaterial_details', 'id', $LOT_ID, 'Production');
	 $sql="UPDATE inputmaterial_details SET STATUS='1' WHERE id='$LOT_ID' ";
	$stmt=$iconn->query($sql);
	if($stmt){
		legacyAuditLogUpdateById('inputmaterial_details', 'id', $LOT_ID, $oldLotData, $_POST, [
			'ReferenceNo' => $LOT_ID,
			'Remarks' => 'Lot returned (status updated)',
		], 'Production');
	}
   }
   
   if($COUNT !=''){
	   $COUNT=explode('&',$COUNT);
	   $LowerCount=$COUNT[0];
	   $UpperCount=$COUNT[1];
	   
	   $query="and COUNT > $LowerCount and COUNT < $UpperCount";
   }else{
	$query="";   
   }

  $sql="SELECT * from inputmaterial_details  where SAMPLE_TYPE ='' and
             PURCHASE_DATE BETWEEN '$date_start' AND '$date_end' $query";


$stmt=$iconn->query($sql);


$content .= '<table id="example1" class="table table-bordered" >


			<thead>

                  <th align="CENTER">Id</th>
                  <th align="CENTER">Lot</th>
				  <th align="CENTER">Purchase Date</th>
				   <th align="CENTER">Type</th>';
				  if($showQuantity){
				  $content .= '<th align="CENTER">Grader</th>
				  <th align="CENTER">Agent</th>
				  <th align="CENTER">Count</th>
				  <th align="CENTER">Weight</th>';
				  }
				  $content .= '
                  <th align="CENTER">AB Status</th>
				  <th align="CENTER">Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($row=$stmt->fetch_array()){


		if($row['ANTI_BIOTIC_STATUS']==0){
			//$A_B_STATUS='GRAY';
			$A_B_STATUS='TEST PENDING';
			$CLR='#666';
		}else if($row['ANTI_BIOTIC_STATUS']==1){
			//$A_B_STATUS='RED';
			$A_B_STATUS='REJECT';
			$CLR='#f00';
		}else if($row['ANTI_BIOTIC_STATUS']==2){
			//$A_B_STATUS='GREEN';
			$A_B_STATUS='ACCEPTED';
			$CLR='#00a65a';
		}else if($row['ANTI_BIOTIC_STATUS']==3){
			//$A_B_STATUS='BLUE';
			$A_B_STATUS='ON HOLD';
			$CLR='#04c';
		}


		if($row['STATUS']==1){
			//$A_B_STATUS='RED';
			$A_B_STATUS.='  (R)';
		}
		
		IF($row['PURCHASE_TYPE']=='Shed weighment'){
			$A_B_STATUS='NO TEST ';
			$CLR='#FFA500';
		}
		
		//uma
		$PURCHASE_DATE=date("d-m-Y",strtotime($row['PURCHASE_DATE']));
			/* */
				$content .= '<tr>

                <td  align="CENTER" width="5%"><font size="'.$text.'">'.$row['id'].'</font></td>

                <td  align="CENTER" width="10%"><font size="'.$text.'"><B>'.$row['LOT_NO'].'</B></font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$PURCHASE_DATE.'</font></td>
				
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['PURCHASE_TYPE'].'</font></td>';
				 if($showQuantity){
				  $content .= '
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['SUPERVISOR'].'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['AGENT_NAME'].'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['COUNT'].'</font></td>
                <td  align="CENTER" width="10%"><font size="'.$text.'">'.$row['WEIGHT_IN_KGS'].'</font></td>';
				 }



			    $content .= '<td align="CENTER" width="10%">

		<div class="bg-active color-palette" align="center" >
			<span class="label" align="center" style="background:'.$CLR.'">'. $A_B_STATUS.'</span>
			</div>
		</td>

		 <td style="width:15%;">

		';





	 if($_SESSION['D_ID']== 4){

	 $content.=  '
	 

	 <a target="_blank" class="btn btn-info" href="AddVAReprt.php?ID='.$row['id'].'"
					class="glyphicon
						glyphicon">Add VA</a>

	 </tr>';



	  	// $content.='  <a  target="_blank" href="LotDetails_Edit.php?id='. $row['id'].'"class="btn btn-info">Edit Lot</a>';
	   }elseif($_SESSION['D_ID']== 5){
		$content.='

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<a target="_blank" href="AddSockngRprt.php?ID='.$row['id'].'"
					>  Add Soaking Report </a>
					</li>
				  <!-- 	<li>
					<a  target="_blank" href="AdIQFRprt.php?ID='.$row['id'].'"
					>  Add Iqf Report </a>
					</li>-->
					<li> <button id="BtnAddIqf" style="width:160px;" value= "'.$row['id'].'" align="Center" class="btn btn-success btn-sm btn-flat">Add Iqf Report</button></li>



				 </ul>

                </div>

		';

	   }elseif($_SESSION['D_ID']== 3){


		$content.='

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
					<a target="_blank" href="AdMorCnts.php?ID='.$row['id'].'"
					>Add More Counts</a>
					</li>
				   	<li>
					<a target="_blank" href="AddRMDefects.php?ID='.$row['id'].'"
					>Add Defects</a>
					</li>
					<li>
					<a target="_blank" href="EdtLotRprt.php?ID='.$row['id'].'"
					>Edit Lot</a>
					</li>

				<!--		<li>
					<a target="_blank" href="#">Lot Details</a>
					</li> -->

				 </ul>

                </div>

		';


	   } else{

$content.='

		<div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>

				 <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                   	<li>
				<!--	<a target="_blank" href="AddSockngRprt.php?ID='.$row['id'].'"
					class="btn btn-warning btn-sm  btn-flat glyphicon
						glyphicon">  LOT DETAILS  </a> -->
					</li>

				 </ul>

                </div>

		';


	   }

	  $content.=' </td>
		</tr>';


	}
	$content.='</tbody></table>'	;

/*	<a   href="#"class="btn btn-info">Lot Details</a>
		<a   href="AddRMDefects.php?ID='.$row['id'].'"class="btn btn-info">RM DEFECTS</a>
	*/

echo $content;

	?>
	
