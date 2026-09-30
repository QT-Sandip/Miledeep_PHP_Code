<?php


function getBillData($iconn,$LOT_ID){
	
	
	
	
$sql="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();

if($post_details['RM_ANA_STATUS'] > 0){
	
	
	$sql_INSP="SELECT * FROM `ORGN_INSP_REPORT` WHERE LOT_ID=$LOT_ID";
    $stmt_INSP=$iconn->query($sql_INSP); 
	$row_INSP=$stmt_INSP->fetch_array();
	
	$INSPECTED_BY=$row_INSP['INSPECTED_BY'];	
	$VERIFIED_BY=$row_INSP['VERIFIED_BY'];	
	$AUTH_REMARKS=$row_INSP['AUTH_REMARKS'];	 
	$DEDUCTIONS=$row_INSP['DEDUCTIONS']; 
	
	 $sql_user="SELECT * FROM `login` WHERE USER_ID=$INSPECTED_BY";
    $stmt_user=$iconn->query($sql_user);
	$row_user=$stmt_user->fetch_array();
	
	$INSPECTED_BY=$row_user['USER_NAME'];
	
	$sql_user="SELECT * FROM `login` WHERE USER_ID=$VERIFIED_BY";
    $stmt_user=$iconn->query($sql_user);
	$row_user=$stmt_user->fetch_array();
	
	$VERIFIED_BY=$row_user['USER_NAME'];

	
	
}


$sql="SELECT * FROM `RM_PURCHASE_DETAILS` where LOT_ID='$LOT_ID'";
$stmt=$iconn->query($sql);
$bill_details=$stmt->fetch_array(); 

$SQL_GRADE_STATUS="SELECT * FROM `PURCHASE_FINAL_GRADES` where LOT_ID='$LOT_ID'";
$STMT_GRADE_STATUS=$iconn->query($SQL_GRADE_STATUS);

while($G_ROW=$STMT_GRADE_STATUS->fetch_array()){
	$HL_WEIGHT+=$G_ROW['WEIGHT'];
}

$RATE=$bill_details['RATE'];
$HON_USED=$bill_details['HON_USED'];
$HON_GRADED=$bill_details['HON_GRADED'];
$DEFECT_WGT=$bill_details['DEFECT_WGT'];
$DEFECT_RATE=$bill_details['DEFECT_RATE'];
$TOTAL_HON=$HON_USED+$HON_GRADED;
$TOTAL_HON_PRICE=$TOTAL_HON*$RATE;
$DEFECT_HON_PRICE=$DEFECT_WGT*$DEFECT_RATE;
$PURCHASE_AMOUNT=$TOTAL_HON_PRICE+$DEFECT_HON_PRICE;

$SHED_YIELD=round(($HL_WEIGHT/$HON_GRADED)*100,2);


$content='<table>
<thead>

                                <tr>
								
								<th><strong>Purchase Date</strong></th>
								<th><strong>LOT Number</strong></th>
								<th><strong>Bill Name</strong></th>
								<th><strong>Type</strong></th>
								<th><strong>HON Qty (Kgs)</strong></th>
								<th><strong>Count</strong></th>
								
								
								</tr>
								
									</thead>
									
										<tbody>
								<tr>
									<td>'. $post_details['PURCHASE_DATE'] .'</td>
									<td>'. $post_details['LOT_NO'].'</td>
								    <td>'.$bill_details['BILL_NAME'].'</td>
									<td>'.$post_details['PURCHASE_TYPE'].'</td>
									<td>'.$post_details['WEIGHT_IN_KGS'].'</strong></td>
								
									<td>'.$post_details['COUNT'].'</strong></td>
									
								
								</tr>
								
								
								
								'
								
								;
								
								
								
								
								$content.=	'</tbody>
							
								
								</table>';
								
								
								
								
$content.='<div class="row"> <h3 align="center">Rm  Details</h3> </div>

<table>
						<thead>

                                <tr>
								
								<th><strong>Inspected By</strong></th>
								<th><strong>Approved By</strong></th>
								<th><strong>Remarks</strong></th>
								<th><strong>Deduction /kg</strong></th>
								
								
								</tr>
								
									</thead>
									
										<tbody>
								
								
								
								
								<tr>
									<td>'. $INSPECTED_BY .'</td>
									<td>'. $VERIFIED_BY.'</td>
								    <td>'.$AUTH_REMARKS.'</td>
									<td>'.$DEDUCTIONS.'</td>
									
								
								</tr>'
								
								;
								
								
								
								
								$content.=	'</tbody>
							
								
								</table>';
								
								
return json_encode($content);
	

	
}

function getCosingData($iconn,$LOT_ID){
	
	
			$SQL_BILL_DETAILS="SELECT * FROM `PURCHASE_DETAILS` where LOT_ID='$LOT_ID'";
			$STMT_BILL_DETAILS=$iconn->query($SQL_BILL_DETAILS);
			$R_B_DETAILS=$STMT_BILL_DETAILS->fetch_array();
			
			$RATE=$R_B_DETAILS['RATE'];
			$HON_USED=$R_B_DETAILS['HON_USED'];
			$HON_GRADED=$R_B_DETAILS['HON_GRADED'];
			$DEFECT_WGT=$R_B_DETAILS['DEFECT_WGT'];
			$DEFECT_RATE=$R_B_DETAILS['DEFECT_RATE'];
			
			//$TOTAL_HON=$HON_USED+$HON_GRADED;
			$TOTAL_HON=$HON_GRADED;
			$TOTAL_HON_PRICE=$TOTAL_HON*$RATE;
			$DEFECT_HON_PRICE=$DEFECT_WGT*$DEFECT_RATE;
			
			//$PURCHASE_AMOUNT=$TOTAL_HON_PRICE+$DEFECT_HON_PRICE;
			$PURCHASE_AMOUNT=$TOTAL_HON_PRICE;
			
			$SQL_GRADE_STATUS="SELECT * FROM `PURCHASE_FINAL_GRADES` where LOT_ID='$LOT_ID'";
			$STMT_GRADE_STATUS=$iconn->query($SQL_GRADE_STATUS);
			
			
			
			$EXCHANGE_RATE=73;
			$TOTAL_HL_WEIGHT=0;
			$DEPB=8;
			
			$Expance_kg=80;
			
			
			$content .= '<table > 
				
			   <tr>  
           		
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>Exchange Rate</B></font></th>
                
                <th  align="CENTER" width="'.($width*3).'%"><font size="'.$text.'"><B>'.$exchange_rate.'</B></font></th>
              
                </tr>
				
				<tr>  
           		
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>COUNT</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>HL QTY</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>USD</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B>RS</B></font></th>
              
                </tr>';
				
				$SIZE=0;
			
			while($GRADES_ROW=$STMT_GRADE_STATUS->fetch_array()){
				$ID=$GRADES_ROW['ID'];
				$GRADE=$GRADES_ROW['GRADE'];
				$WEIGHT=$GRADES_ROW['WEIGHT'];
				$USD=$GRADES_ROW['USD'];
				$SALE_PRICE=$WEIGHT*$USD*$EXCHANGE_RATE;
				
				$TOTAL_HL_WEIGHT+=$WEIGHT;
				$TOTAL_SALE_PRICE+=$SALE_PRICE;
				
	
	
				$content .= '<tr>  
           		
               
				 <th align="CENTER" width="'.$width.'%"><font size="'.$text.'"> '.$GRADE.'</font></th>
				 <input  type="text" class="hidden"  name="GRADE_ID" id="GRADE_ID'.$SIZE.'" value="'.$ID.'">
				 
				  <!--<th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> '.$WEIGHT.'</font></th>-->
				
               <th><input  type="number" class="form-control"  name="WEIGHT" id="WEIGHT_ID'.$SIZE.'" value="'.$WEIGHT.'"></th>  
                <th><input  type="number"  step="any" class="form-control"  name="USD" id="USD'.$SIZE.'" value="'.$USD.'">
				</font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>'.$SALE_PRICE.'</B></font></th>
                </tr>';
				
				$SIZE++;
			}
			
			
			$TOTAL_Expance=$Expance_kg * $TOTAL_HL_WEIGHT;
			$TOTAL_DEPB=($DEPB * $TOTAL_SALE_PRICE)/100;
			$PROFIT_LOSS=($TOTAL_SALE_PRICE+$TOTAL_DEPB)-($TOTAL_Expance+$PURCHASE_AMOUNT);

if($PROFIT_LOSS>0){
	$TRANSACTION='PROFIT';
}else{
	$TRANSACTION='LOSS';
}


				
				$content .= '
				<tr>  
           		
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B>Total</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>'.$TOTAL_HL_WEIGHT.'</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>'.$TOTAL_SALE_PRICE.'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B>Expanse</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B>'.$Expance_kg.'</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B> -'.$TOTAL_Expance.'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B>DEPB</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B> '.$DEPB.'</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B> +'.$TOTAL_DEPB.'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B>PURCHASE</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B> -'.$PURCHASE_AMOUNT.'</B></font></th>
                </tr>
				
				<tr>            		
                <th align="CENTER" width="'.$width.'%"><font size="'.$text.'"><B>'.$TRANSACTION.'</B></font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="CENTER" width="'.$width.'%"><font size="'.$text.'"> <B style="color:red"> '.round($PROFIT_LOSS,2).'</B></font></th>
                <th><input  type="text" class="hidden"  name="SIZE" id="SIZE" value="'.$SIZE.'">
				</font></th>
				
				</tr>
				
				</table>'; 
				

	
	return json_encode($content);


				
}
				?>