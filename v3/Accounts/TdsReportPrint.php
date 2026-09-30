<?php 
	include '../includes/config.php';
	include '../includes/Globals.php';

	if(isset($_GET['to_date'])){
		
			$FROM_DATE=$_GET['from_date'];
			$TO_DATE=$_GET['to_date'].' 23:59:59';
			
			
	 $sql="SELECT id,PURCHASE_DATE,LOT_NO,BILL_NAME,BillPanNumber FROM `inputmaterial_details` as IPD
			left join RM_BILL_DETAILS as RBD  on IPD.id=RBD.LOT_ID where IPD.PURCHASE_DATE
	BETWEEN '$FROM_DATE' AND '$TO_DATE' order by IPD.PURCHASE_DATE Asc,IPD.LOT_NO ASC  ";	
	$stmt_pid=$iconn->query($sql); 
	
	
	//echo $sql;	 exit;
	
	$header='<tr>  

           		<th  align="LEFT"  width="100%">
				<B>From Date:'.getFormatedDate($FROM_DATE).
				'<b style="color:white">hgbhnjnjhuy</b>
				To Date:'.getFormatedDate($TO_DATE).'</B></th>

               </tr>';
	$w1="8";

	 $data ='<table id="example1"  border="1" cellspacing="0" cellpadding="5" > 
		
		<thead>
		<tr>
		
		<th width="'.$w1.'%">Date</th>
		<th width="'.$w1.'%">Lot Number</th>
		<th width="'.$w1.'%">Bill Name</th>
		<th width="'.$w1.'%">PAN Number</th>
		<th width="'.$w1.'%">TDS %</th>
		<th width="'.$w1.'%">Bill Amount</th>
		<th width="'.$w1.'%">TDS Amount</th>
		<th width="'.$w1.'%">Sub Total</th>
		
		</tr></thead><tbody>
		
		
		';
		
		$C=0;
$WEIGHT_TOTAL=0;

	 while($row_csrd=$stmt_pid->fetch_array()){
	
	
	$BillDetails=getBillAmount($row_csrd['id']);
	
	$BillAmountTotal+=$BillDetails['BillAmount'];
	$TdsAmountTotal+=$BillDetails['TdsAmount'];
	$SubTotalTotal+=$BillDetails['SubTotal'];
	
	
	
	//print_r($BillDetails);
	$data .='
				<tr>
		
		
		
		<td >'.getFormatedDate($row_csrd['PURCHASE_DATE']).'</td>
		<td >'.$row_csrd['LOT_NO'].'</td>
		<td >'.$row_csrd['BILL_NAME'].'</td>
		<td >'.$row_csrd['BillPanNumber'].'</td>
		<td >'.$BillDetails['TdsPerc'].'</td>
		<td >'.$BillDetails['BillAmount'].'</td>
		<td >'.$BillDetails['TdsAmount'].'</td>
		<td >'.$BillDetails['SubTotal'].'</td>
		
	
			</tr>';
$C++;
					  
}

$data .= '


<tr>

				</tr>

				
		<tr> 
           		

                <th  align="center"width="" >Total </th>
                <th  align="center"width="13%" >'.$C.'Bills</th>
				<th  align="center" ></th>
				<th  align="center" ></th>
				<th  align="center" ></th>

				<th  align="center"width="13%" >'.$BillAmountTotal.' </th>
				<th  align="center"width="13%" >'.$TdsAmountTotal.'  </th>
				<th  align="center"width="13%" >'.round($SubTotalTotal,2).'  </th>


   
               </tr>';







		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
	function getBillAmount($LOT_ID){
global $iconn;		
		
			
 $HLGQuery = "SELECT 
im.PURCHASE_DATE,
 im.LOT_NO,
 im.UNIT_ID,
 im.AGENT_NAME,
 
 im.DRIVER_NAME,
 im.VEHICAL_NO,
 im.ADDRESS,
 im.WEIGHT_IN_KGS AS F_WEIGHT,
 im.COUNT AS F_COUNT,
 im.VEHICAL_NO ,
 im.DRIVER_NAME ,
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
RM_BILL_DETAILS RBD on im.id = RBD.LOT_ID WHERE im.id = '$LOT_ID'";

$HLGexec=$iconn->query($HLGQuery);
		
			$SqlDefectsDetails="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
			$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);


	        $SqlQcRmDetails="SELECT * from ORGN_INSP_REPORT where LOT_ID='$LOT_ID'";
			$StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
			$RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();
			
			$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
			$StmtHonGrading=$iconn->query($SqlHonGrading);
			$RowHonGrading=$StmtHonGrading->fetch_array();
	
	$HLGRep = $HLGexec->fetch_assoc();
	
	$AccDeductionsKg= $HLGRep['DEDUCTIONS'];
	$C_QTY = $HLGRep['WEIGHT'];
	$RATE =$HLGRep['RATE'];
	 $BillPanNumber =$HLGRep['BillPanNumber'];
	
	$PURCHASE_AMOUNT=($C_QTY * $RATE);
	
	
				   while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
						$value[]=$RowDefectsDetails;
				  
				   $DefectsAmount=$DefectsAmount+($value['WEIGHT'] * $value['RATE']);
	}
	
	
			if($BillPanNumber !='' && strlen($BillPanNumber) ==10){
					  $TDSperc='0.1%'; 
					 
				   }else{
					   $TDSperc='5%';
					   $BillPanNumber='NA';
					 
				   }
				   
				    
			$HonUsed=$RowHonGrading['HON_WEIGHT'];
			$HonUsedRate=$RowHonGrading['HON_PACKED_RATE'];
			$HonUsedCount=$RowHonGrading['HON_PACKED_COUNT'];
			
			$HonUsedAmount=$HonUsedRate*$HonUsed;
			
			$TotalWeight=$C_QTY+$HonUsed;
			
			$LabDeduction= $RowQcRmDetails['DEDUCTIONS']*$TotalWeight;
			$AccDeductions= $AccDeductionsKg*$TotalWeight;
	
	$BillTotal=$PURCHASE_AMOUNT + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
	$TdsAmount=($BillTotal*$TDSperc)/100;
	
	$BillData['TdsPerc']=$TDSperc;
	$BillData['BillAmount']=$BillTotal;
	$BillData['TdsAmount']=round($TdsAmount,2);
	$BillData['SubTotal']=round($BillTotal-$TdsAmount,2);
	
	return $BillData;
		
	}
	
?>