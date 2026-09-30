<?php
session_start();
include "../includes/config.php";
//include "../includes/Globals.php";


 $from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date'].' 23:59:59'; 
   
   if(isset($_GET['U'])){
	  $UNIT=$_GET['U'];
	  
	  $Query="and UNIT='$UNIT'";
  }else{
	   $Query="";
  }
 

//$M=$_GET['M'];
//$Y=$_GET['Y'];

//$FROM_DATE=$Y.'-'.$M.'-01';
//$TO_DATE=$Y.'-'.$M.'-30 23:59:59';

//$UNIT=$_GET['U'];
global $Query;
$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$Expanse=$_POST['Expanse'];
$DEPB=$_POST['DEPB'];

//IM.COUNT as EXP_COUNT,
	//PD.HON_ACTUAL_COUNT as ACT_COUNT,
	
	   $aval_hl_reports_sql = "
	select * from 
	
	PPC_GRADING_HL as HW 
	left join inputmaterial_details AS IM ON HW.LOT_ID=IM.id 
	left join RM_BILL_DETAILS AS RBD ON RBD.LOT_ID=id 
	left join RM_PURCHASE_DETAILS as PD on IM.id= PD.LOT_ID
	WHERE HW.DATA_TYPE='Headless Grading' 
	AND IM.SAMPLE_TYPE='' 
	AND IM.PURCHASE_DATE BETWEEN '$from_date' AND
	'$to_date' $Query
	 order by 
	IM.PURCHASE_DATE,IM.LOT_NO ASC";
	
	$stmt_hl=$iconn->query($aval_hl_reports_sql);
	
	
	
	$BILLED_LOTS=$stmt_hl->num_rows;	
	$table=	getCostingTable($iconn,$EXCHANGE_RATE,$stmt_hl,$BILLED_LOTS,$Expanse,$DEPB);
	
	
	//echo 'Ungraded Lots -'.$UN_GRADED_LOTS.' / Total Lots- '.$TOTAL_LOTS;
	echo $table;
	
	

	function getCostingTable($iconn,$EXCHANGE_RATE,$hl_stmt,$BILLED_LOTS,$Expanse,$DEPB){
		
		$ARY_COSTING_RATES=ARRAY();
		
		 $SQL_COSTING="SELECT * FROM ACC_COSTING_RATES WHERE TARCK_ID= (SELECT MAX(TARCK_ID) from ACC_COSTING_RATES)";
		
		$STMT_SQL_COSTING=$iconn->query($SQL_COSTING);
		
		while($ROW_COSTING = $STMT_SQL_COSTING->fetch_assoc()){
			$ARY_COSTING_RATES[]=$ROW_COSTING;
		}
		
		$ARY_DAY_GRADES=array();
		
		
		$PO_TMSP = strtotime($date);
$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);

	
 $from_dt=date('d-m-Y', strtotime($from_date)); 
 $to_dt=date('d-m-Y', strtotime($to_date)); 
		
			 
$PO_TMSP = strtotime($date);
$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);
	
 $from_dt=date('d-m-Y', strtotime($from_date)); 
 $to_dt=date('d-m-Y', strtotime($to_date)); 
	$text=4;
	$text1=2;
	
	$exchange_rate=$EXCHANGE_RATE;
	
		
	
	$content .= '<table border="1" cellspacing="0" cellpadding="1"> 
				
			  <tr> 
           		
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>S No</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>DATE</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>BILL NO</B></font></th>';
				
				//foreach($ARY_COSTING_RATES as $ROW_COSTING){
               //$content .= ' <th  align="CENTER" width="5%"><font size="'.$text.'"> 
			   //<B>'.$ROW_COSTING['GRADE'].'</B></font></th>';
                 
				//}
               
			   $content .= ' 
			   
			    <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>HON.QTY</B></font></th>
				 <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>HON WASTE</B></font></th>
			    <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>HON/PACKING</B></font></th>
			    <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>HON RATE</B></font></th>
				 
			  
			
			   
			   </tr>
				
			
               
                ';
	

	
	$SNO=1;
	$PREV_DATE='';
	
	
	while($r_varity_list=$hl_stmt->fetch_array()){
		
		
		$LOT_ID=$r_varity_list['LOT_ID'];
		$sql="SELECT * FROM `RM_BILL_DETAILS` where LOT_ID='$LOT_ID'";
		$stmt=$iconn->query($sql);
		$bill_details=$stmt->fetch_array();
	
$FWEIGHT=$aval_hl_reports_sql['WEIGHT_IN_KGS'];
$RATE=$bill_details['RATE'];
$WEIGHT=$bill_details['WEIGHT'];

$LOT_COUNT=$bill_details['COUNT'];
$SHED_YIELD=$bill_details['YIELD'];
		$T_HON+=$bill_details['WEIGHT'];
		
		
$DEFECT_WGT=$bill_details['DEFECT_WGT'];
$DEFECT_RATE=$bill_details['DEFECT_RATE'];
$TOTAL_HON=$WEIGHT;
$TOTAL_HON_PRICE=$TOTAL_HON*$RATE;
$DEFECT_HON_PRICE=$DEFECT_WGT*$DEFECT_RATE;
$PURCHASE_AMOUNT=$TOTAL_HON_PRICE+$DEFECT_HON_PRICE;

		
		$LOT_NO=$r_varity_list['LOT_NO'];
		$PURCHASE_DATE=$r_varity_list['PURCHASE_DATE'];
		
		
		if($PREV_DATE ==''){
		$PREV_DATE=$r_varity_list['PURCHASE_DATE'];
		
		}
		
		
	//umask
	$PURCHASE_DATEE=date("d-m-Y",strtotime($PURCHASE_DATE));
	
	/* */	
		
		$content .= '
				<tr>  
           		
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$SNO.'</B></font></th>
                <th  align="CENTER" width="5%"><font size="2"><B>'.$PURCHASE_DATEE.'</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$LOT_NO.'</B></font></th>';
			 $SNO++;
			
			$LOT_NO=$r_varity_list['LOT_NO'];
			$RATE=$r_varity_list['RATE'];
			$exchange_rate	=$r_varity_list['EXCHANGE_RATE'];
			$Expance_kg	=$Expanse;
			$DEPB=$DEPB;
		
			//$PURCHASE_AMOUNT=($RATE*$r_varity_list['HON_QTY']) ;
			$PURCHASE_WEIGHT=$PURCHASE_WEIGHT +$r_varity_list['HON_QTY'] ;
			$DAY_PUR_TOT+=$PURCHASE_AMOUNT;
						
			$data = $r_varity_list['HOSO_W_DATA'];		
			$json_decode_data=json_decode($data,true);
			$ary_counts=array();
			$ary_samples=array();

			foreach($json_decode_data  as $value){	
		
		
				$ary_samples[]=$value;  // Colecting all weightments as single arrary					
				if(!in_array($value['Count'],$ary_counts,true)){
				$ary_counts[]= $value['Count'];
				}
			}
			
			sort($ary_counts);
	
		//print_r($ary_counts);

			//=$row['COUNT'];
			$HL_LOT_WEIGHT=0;
			
			$ary_lot_grade=array();
			
foreach($ARY_COSTING_RATES as $ROW_COSTING){
				
				 $GRADE_SLAB;
				 $weight_sum=0;	
				 $range=explode("/",$ROW_COSTING['GRADE']);
				 $start=(int) $range[0];
				 $end=(int) $range[1];
				 
				 $aval=false;
				 $weight_sum=0;
			
		foreach($ary_counts  as $countt){
				$count=(int) $countt;
			if($count >= $start  && $count <=$end){
			$aval=true;
			
			foreach($ary_samples  as $value){						
			if($value['Count'] == $countt){
				 $weight_sum=$weight_sum + $value['NetWeight'];
			}						
			}
			}
			
				
		}
		
		//if($aval){
		 //$content.='<th  align="CENTER" width="5%"><font size="'.$text1.'">'. $weight_sum.'</font></th>';		
		//}else{
			//$content.='<th  align="CENTER" width="5%"><font size="'.$text.'"><B>--</B></font></th>';
		//}

		$sample['GRADE']=$ROW_COSTING['GRADE'];
		$sample['WEIGHT']=$weight_sum;
		$ary_lot_grade[]=$sample;
		$HL_LOT_WEIGHT+=$weight_sum;
		$HEADWAEST=$WEIGHT-$HL_LOT_WEIGHT;
		$T_WST+=$HEADWAEST;
	}

$ARY_DAY_GRADES[]=$ary_lot_grade;	


			$SqlDefectsDetails="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
			$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);
			
			$DefectsWeight=0;
			$DefectsPrice=0;
			 while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
				$DefectsWeight += $RowDefectsDetails['WEIGHT'];
				$DefectsPrice += ($RowDefectsDetails['WEIGHT'] * $RowDefectsDetails['RATE']) ;
			 }


	        $SqlQcRmDetails="SELECT * from ORGN_INSP_REPORT where LOT_ID='$LOT_ID'";
			$StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
			$RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();
			
			
			
			
			$LabDeductionKg= $RowQcRmDetails['DEDUCTIONS'];
			$Remarks= $RowQcRmDetails['AUTH_REMARKS'];
			$LabDeduction= $RowQcRmDetails['DEDUCTIONS']*$TOTAL_HON;
			$AccDeductions= $AccDeductionsKg*$TOTAL_HON;
			
			$PURCHASE_AMOUNT=$PURCHASE_AMOUNT+$DefectsPrice;
			$PURCHASE_AMOUNT=$PURCHASE_AMOUNT-$LabDeduction;
			$PURCHASE_AMOUNT=$PURCHASE_AMOUNT+$AccDeductions;
			
			$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
			$StmtHonGrading=$iconn->query($SqlHonGrading);
			$RowHonGrading=$StmtHonGrading->fetch_array();
			
			if($RowHonGrading!=0){
			$HonUsed=$RowHonGrading['HON_WEIGHT'];
			$HonUsedRate=$RowHonGrading['HON_PACKED_RATE'];
			$HonUsedCount=$RowHonGrading['HON_PACKED_COUNT'];
			
			$HonUsedAmount=$HonUsedRate*$HonUsed;
			
}else{
			$HonUsed=0;
			$HonUsedAmount=0;
			$HonUsedRate=0;
}
			
			
				
$content.='

<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$WEIGHT.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$HEADWAEST.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$HonUsed.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$HonUsedRate.'</font></th>



</tr>';
			
            
			

	
	}
	
	
	$content.='<tr>
	
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
//$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';


foreach($ARY_COSTING_RATES as $ROW_COSTING){
	$weight=0;
	
foreach($ARY_DAY_GRADES as $ROW_GRADES){
foreach($ROW_GRADES as $ROW_GRADE){
	//echo '=='.$ROW_GRADE[0]['GRADE'];
		//print_r($ROW_GRADE);

	if($ROW_GRADE['GRADE'] == $ROW_COSTING['GRADE']){
	//ECHO 'M'.$ROW_COSTING['COUNT'];
		$weight+=$ROW_GRADE['WEIGHT'];
		$T_HL+=$ROW_GRADE['WEIGHT'];
		
	}
	if($ROW_weight['WEIGHT'] == $ROW_COSTING['WEIGHT']){
	//ECHO 'M'.$ROW_COSTING['COUNT'];
		
		
	}
}
}
//$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$weight.'</B></font></td>';

}
$ARY_DAY_GRADES=array();
//$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$T_HON.'</B></font></td>';
//$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$T_WST.'</B></font></td>';

//$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$T_HON.'</B></font></td>';
//$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$DEFECT_WGT.'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';

//$content.='<td  align="RIGHT" width="5%"><font size="'.$text.'"><B>'.$DAY_PUR_TOT.'</B></font></td></tr>';
$DAY_PUR_TOT=0;
$T_HL=0;
				
		  $content.='  
				
				</table>'; 		

			header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=download.xls");
			
			
	return $content;

	
	}
	
 function formatToInr($number){
        $number=round($number,2);
        // windows is not supported money_format
    if(setlocale(LC_MONETARY, 'en_IN')){
         return money_format('%!'.$decimal.'n', $number);
         }
     else {
        if(floor($number) == $number) {
            $append='.00';
        }else{
            $append='';
        }
        $number = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $number);
        return $number.$append;
        }
		
    }
	
?>


