<?php
session_start();
include "../includes/config.php";

$FROM_DATE=$_POST['FROM_DATE'];
$TO_DATE=$_POST['TO_DATE'];
$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$Expanse=$_POST['Expanse'];
$DEPB=$_POST['DEPB'];
$UNIT=$_POST['UNIT'];

 $aval_hl_reports_sql = "select * from PPC_GRADING_HL  as HW left join inputmaterial_details AS 
	IM ON HW.LOT_ID=IM.id left join RM_BILL_DETAILS  AS PPC ON HW.LOT_ID=PPC.LOT_ID 
	WHERE  IM.SAMPLE_TYPE=''
	AND " ;
	
	if($UNIT!='0'){		
	 $aval_hl_reports_sql .="IM.UNIT='$UNIT' and IM.PURCHASE_DATE BETWEEN '$FROM_DATE' and '$TO_DATE'";
	}else{
		 $aval_hl_reports_sql .="IM.PURCHASE_DATE BETWEEN '$FROM_DATE' and '$TO_DATE'";
	}
//	echo $aval_hl_reports_sql;  
	
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
		
	
	$text=2;
	
	$exchange_rate=$EXCHANGE_RATE;
	
	
	$content .= '<table id="example1" class="table table-bordered">  
				
			  <thead> 
           		
                <th  align="CENTER" width="25%"><font size="'.$text.'"> <B>COUNT</B></font></th>
                <th  align="CENTER" width="25%"><font size="'.$text.'"> <B>HL QTY(Kgs)</B></font></th>
                <th  align="CENTER" width="25%"><font size="'.$text.'"> <B>USD</B></font></th>
                <th  align="CENTER" width="25%"><font size="'.$text.'"><B>RS</B></font></th>
               </thead>
				
			
               
                <tbody>';
	
	$PURCHASE_AMOUNT=0;
	$PURCHASE_WEIGHT=0;
	
	while($r_varity_list=$hl_stmt->fetch_array()){
			
			 $RATE=$r_varity_list['RATE'];
			$exchange_rate	=$r_varity_list['EXCHANGE_RATE'];
			$Expance_kg	=$Expanse;
			$DEPB=$DEPB;
		
			$PURCHASE_AMOUNT=$PURCHASE_AMOUNT +($RATE*$r_varity_list['WEIGHT']) ;
			$PURCHASE_WEIGHT=$PURCHASE_WEIGHT +$r_varity_list['WEIGHT'] ;
						
			$data = $r_varity_list['HOSO_W_DATA'];		
			$json_decode_data=json_decode($data,true);

			foreach($json_decode_data  as $value){				
				$ary_samples[]=$value;  // Colecting all weightments as single arrary					
				if(!in_array($value['Count'],$ary_counts)){
				$ary_counts[]= $value['Count'];
				}
			}
			
			sort($ary_counts);
	
	}		
	
	//print_r($ary_counts);

			
			
			foreach($ARY_COSTING_RATES as $ROW_COSTING){
				
				 $GRADE_SLAB;
					$weight_sum=0;	
					$range=explode("/",$ROW_COSTING['GRADE']);
					
					 $start=(int) $range[0];
					$end=(int) $range[1];
			
		foreach($ary_counts  as $count){		
						$count=(int) $count;
		if($count >= $start  && $count <=$end){			
			
			$weight_sum=0;
			
			  $ROW_COSTING['COUNT'];
			foreach($ary_samples  as $value){						
			if($value['Count'] == $count){
				 $weight_sum=$weight_sum + $value['NetWeight'];
			}						
			}
			
			$SALE_PRICE=$weight_sum*$ROW_COSTING['USD']*$exchange_rate;
			
			$ary_final_samples_per_grade_each= array();
			if($weight_sum!=0){
				
					$ary_final_samples_per_grade_each[]=$new_str = preg_replace("/\s+/", "",$ROW_COSTING['GRADE']);;
				$ary_final_samples_per_grade_each[]=$weight_sum;
				$ary_final_samples_per_grade_each[]=$ROW_COSTING['USD'];
				$ary_final_samples_per_grade_each[]=$SALE_PRICE;
				
				$ary_final_samples_per_grade[]=$ary_final_samples_per_grade_each;
				}
				
		}
		}
				
}
//print_r($ary_final_samples_per_grade);

		
				$ary_grades=array();
	
		foreach($ary_final_samples_per_grade as $grade){
			//print_r($ary_grades);
			$current_grade= $grade[0];
			//$current_grade= 0;
			if(!in_array($current_grade,$ary_grades)){
				$ary_grades[]= $current_grade;
			}else{
				// echo 'exists';
			}
			}
			
		//	print_r($ary_grades);
		
		foreach($ary_grades as $grade){
			
			$weight_sum=0;
			
			foreach($ary_final_samples_per_grade as $sample){
			if($sample[0] == $grade){				
				 $weight_sum=$weight_sum + $sample[1];
				 $usd=$sample[2];
			}			
		}
			
			$SALE_PRICE=$weight_sum*$usd*$EXCHANGE_RATE;
			$TOTAL_HL_WEIGHT=$TOTAL_HL_WEIGHT+$weight_sum;
			$TOTAL_SALE_PRICE=$TOTAL_SALE_PRICE+$SALE_PRICE;
			
			$content .= '<tr>  
           		
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.$grade.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$weight_sum.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$usd.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.formatToInr(round($SALE_PRICE)).'</B></font></th>
                </tr>';
			
			}
		
				

			
			

 $TOTAL_Expance=$Expance_kg * $TOTAL_HL_WEIGHT;
$TOTAL_DEPB=($DEPB * $TOTAL_SALE_PRICE)/100;

$PROFIT_LOSS=($TOTAL_SALE_PRICE+$TOTAL_DEPB)-($TOTAL_Expance+$PURCHASE_AMOUNT);

if($PROFIT_LOSS>0){
	
	$TRANSACTION='GAIN';
	$CLR='green';
}else{
	$TRANSACTION='LOSS';
		$CLR='red';
}


				
				$content .= '
				<tr>  
           		
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>TOTAL HEAD LESS</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$TOTAL_HL_WEIGHT.' Kgs</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.formatToInr($TOTAL_SALE_PRICE).'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>TOTAL HEAD ON </B></font></th>
                 <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$PURCHASE_WEIGHT.' Kgs</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B style="color:red"> -'.formatToInr($PURCHASE_AMOUNT).'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>Expanse</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$Expance_kg.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B style="color:red"> -'.formatToInr($TOTAL_Expance).'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>DEPB</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$DEPB.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B> +'.formatToInr($TOTAL_DEPB).'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.$TRANSACTION.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B style="color:'.$CLR.'"> '.formatToInr($PROFIT_LOSS).'</B></font></th>
                </tr>
				
				</table>'; 
				
				$TOTAL_REVENUE=round($TOTAL_SALE_PRICE+$TOTAL_DEPB,2); 
				$TOTAL_COST=round($PURCHASE_AMOUNT+$TOTAL_Expance,2);
				$PROFIT_LOSS=round($PROFIT_LOSS,2);
				
				$DIFF_PERC=round(($PROFIT_LOSS/$TOTAL_COST)*100,2);
				
				
				
				if($PROFIT_LOSS>0){
				$TRANS_SPAN='<span class="description-percentage text-green"><i class="fa fa-caret-up"></i>'.$DIFF_PERC.'%</span>';
				
				}else{
				$TRANS_SPAN='<span class="description-percentage text-red"><i class="fa fa-caret-down"></i>'.$DIFF_PERC.'%</span>';
				}
            
      echo    $data='<!-- ./box-body -->  <div class="box-footer">
              <div class="row">
			  
			  <div class="col-sm-2 col-xs-12">
                  <div class="description-block">
                    <span class="description-percentage text-red"><i class="fa fa-caret-downn"></i></span>
                    <h5 class="description-header">'.$BILLED_LOTS.'</h5>
                    <span class="description-text">Analyzed Lots</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                
                <!-- /.col -->
                <div class="col-sm-2 col-xs-12">
                  <div class="description-block border-right">
                    <span class="description-percentage text-yellow"><i class="fa fa-caret-leftt"></i></span>
                    <h5 class="description-header">
					<i style="font-size:16px" class="fa">&#xf156;</i> '.formatToInr($PURCHASE_AMOUNT).'</h5>
                    <span class="description-text">PURCHASE</span>
                  </div>
                  <!-- /.description-block -->
                </div>
				
				<!-- /.col -->
                <div class="col-sm-3 col-xs-12">
                  <div class="description-block border-right">
                    <span class="description-percentage text-yellow"><i class="fa fa-caret-leftt"></i></span>
                    <h5 class="description-header">
					<i style="font-size:16px" class="fa">&#xf156;</i> '.formatToInr($TOTAL_Expance).'</h5>
                    <span class="description-text">Expanse</span>
                  </div>
                  <!-- /.description-block -->
                </div>
				
				<div class="col-sm-3 col-xs-12">
                  <div class="description-block border-right">
                    <span class="description-percentage text-green"><i class="fa fa-caret-upp"></i></span>
                    <h5 class="description-header">
					<i style="font-size:16px" class="fa">&#xf156;</i> '.formatToInr($TOTAL_REVENUE).'</h5>
                    <span class="description-text">EXPECTED SALE </span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-2 col-xs-12">
                  <div class="description-block border-right">
                   '.$TRANS_SPAN.'
				  
                    <h5 class="description-header">  
					<i style="font-size:16px" class="fa ">&#xf156;</i> '.formatToInr($PROFIT_LOSS).'</h5>
					
                    <span class="description-text">TOTAL '.$TRANSACTION.'</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->';
				

	
	return $content;

	
	}
	
	 function formatToInr($number){
        $number=round($number);
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


